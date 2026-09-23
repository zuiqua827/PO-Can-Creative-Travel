<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Notifications\BookingExpiredNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentGatewayInterface $gateway
    ) {}

    /**
     * Handle incoming gateway payment webhook callbacks.
     */
    public function handle(Request $request): JsonResponse
    {
        Log::channel('payments')->info('Payment webhook received', [
            'ip' => $request->ip(),
            'payload' => $request->except(['client_key', 'server_key']),
        ]);

        // 1. Authenticate webhook signature
        if (! $this->gateway->verifyWebhookSignature($request)) {
            Log::channel('security')->warning('Payment webhook rejected: invalid or missing signature', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing signature.',
            ], 403);
        }

        $payload = $request->all();
        $result = $this->gateway->handleWebhook($payload, (string) $request->header('X-CAN-Signature'));

        $reference = $result->getReference();
        if (! $reference) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing payment reference in payload.',
            ], 422);
        }

        // 2. Locate Payment & Order records safely
        $payment = Payment::with('order.user')
            ->where('payment_reference', $reference)
            ->orWhere('provider_transaction_id', $reference)
            ->orWhereHas('order', function ($q) use ($reference) {
                $q->where('order_code', $reference);
            })
            ->first();

        if (! $payment) {
            Log::channel('payments')->warning("Payment webhook reference not found in database: {$reference}");

            return response()->json([
                'status' => 'error',
                'message' => 'Payment reference not found.',
            ], 404);
        }

        $order = $payment->order;

        // 3. Amount & Currency Validation
        $webhookAmount = $payload['gross_amount'] ?? $payload['amount'] ?? null;
        if ($webhookAmount !== null) {
            $expectedAmount = (float) $payment->amount;
            $receivedAmount = (float) $webhookAmount;

            if (abs($expectedAmount - $receivedAmount) > 0.01) {
                Log::channel('security')->warning("Payment webhook amount mismatch rejected for {$order->order_code}", [
                    'expected' => $expectedAmount,
                    'received' => $receivedAmount,
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment amount mismatch.',
                ], 422);
            }
        }

        if (isset($payload['currency'])) {
            $configuredCurrency = config('payment.currency', 'IDR');
            if (strtoupper($payload['currency']) !== strtoupper($configuredCurrency)) {
                Log::channel('security')->warning("Payment webhook currency mismatch rejected for {$order->order_code}", [
                    'expected' => $configuredCurrency,
                    'received' => $payload['currency'],
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment currency mismatch.',
                ], 422);
            }
        }

        // 4. Webhook Idempotency Check: Don't process twice if already settled or processed
        if ($payment->webhook_processed_at !== null || $payment->status === PaymentResult::STATUS_SUCCESS) {
            Log::channel('payments')->info("Payment webhook ignored: event already processed for order {$order->order_code}");

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook already processed previously.',
                'order_code' => $order->order_code,
            ], 200);
        }

        // 5. State Machine Validation: Prevent illegal transitions (e.g. cancelled -> confirmed)
        if ($result->isSuccess() && ! $order->canTransition('confirmed', 'paid')) {
            Log::channel('security')->warning("Payment webhook attempted illegal transition to paid for order {$order->order_code} with status {$order->status}/{$order->payment_status}");

            return response()->json([
                'status' => 'error',
                'message' => "Order cannot transition to confirmed and paid from {$order->status}/{$order->payment_status}.",
            ], 422);
        }

        // 6. Atomic status synchronization inside DB transaction
        DB::transaction(function () use ($payment, $order, $result, $payload) {
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->first();
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            $txId = $result->getTransactionId() ?: $lockedPayment->provider_transaction_id;

            if ($result->isSuccess()) {
                $lockedPayment->update([
                    'status' => 'success',
                    'provider' => $this->gateway->getProviderName(),
                    'provider_transaction_id' => $txId,
                    'paid_at' => now(),
                    'webhook_processed_at' => now(),
                    'metadata' => array_merge($lockedPayment->metadata ?? [], ['webhook' => $payload]),
                ]);

                $lockedOrder->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                    'expires_at' => null,
                ]);

                Log::channel('payments')->info("Payment webhook confirmed order {$lockedOrder->order_code}");

                try {
                    $lockedOrder->user?->notify(new PaymentReceivedNotification($lockedOrder));
                } catch (\Throwable $e) {
                    Log::channel('payments')->error("Failed to dispatch payment notification for {$lockedOrder->order_code}: ".$e->getMessage());
                }

            } elseif ($result->isExpired()) {
                $lockedPayment->update([
                    'status' => 'expired',
                    'expired_at' => now(),
                    'webhook_processed_at' => now(),
                    'metadata' => array_merge($lockedPayment->metadata ?? [], ['webhook' => $payload]),
                ]);

                $lockedOrder->update([
                    'status' => 'cancelled',
                    'payment_status' => 'expired',
                ]);

                Log::channel('payments')->info("Payment webhook expired order {$lockedOrder->order_code}");

                try {
                    $lockedOrder->user?->notify(new BookingExpiredNotification($lockedOrder));
                } catch (\Throwable $e) {
                    Log::channel('payments')->error("Failed to dispatch expiration notification for {$lockedOrder->order_code}: ".$e->getMessage());
                }

            } elseif ($result->isFailed()) {
                $lockedPayment->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'webhook_processed_at' => now(),
                    'metadata' => array_merge($lockedPayment->metadata ?? [], ['webhook' => $payload]),
                ]);

                Log::channel('payments')->warning("Payment webhook recorded failure for order {$lockedOrder->order_code}");
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook processed successfully.',
            'order_code' => $order->order_code,
            'result' => $result->getStatus(),
        ], 200);
    }
}
