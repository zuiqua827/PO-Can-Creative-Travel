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
        Log::info('Payment webhook received', [
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        // 1. Authenticate webhook signature
        if (! $this->gateway->verifyWebhookSignature($request)) {
            Log::warning('Payment webhook rejected: invalid or missing signature', [
                'ip' => $request->ip(),
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
            Log::warning("Payment webhook reference not found in database: {$reference}");

            return response()->json([
                'status' => 'error',
                'message' => 'Payment reference not found.',
            ], 404);
        }

        $order = $payment->order;

        // 3. Webhook Idempotency Check: Don't process twice if already settled or processed
        if ($payment->webhook_processed_at !== null || $payment->status === PaymentResult::STATUS_SUCCESS) {
            Log::info("Payment webhook ignored: event already processed for order {$order->order_code}");

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook already processed previously.',
                'order_code' => $order->order_code,
            ], 200);
        }

        // 4. Atomic status synchronization inside DB transaction
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

                Log::info("Payment webhook confirmed order {$lockedOrder->order_code}");

                try {
                    $lockedOrder->user?->notify(new PaymentReceivedNotification($lockedOrder));
                } catch (\Throwable $e) {
                    Log::error("Failed to dispatch payment notification for {$lockedOrder->order_code}: ".$e->getMessage());
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

                Log::info("Payment webhook expired order {$lockedOrder->order_code}");

                try {
                    $lockedOrder->user?->notify(new BookingExpiredNotification($lockedOrder));
                } catch (\Throwable $e) {
                    Log::error("Failed to dispatch expiration notification for {$lockedOrder->order_code}: ".$e->getMessage());
                }

            } elseif ($result->isFailed()) {
                $lockedPayment->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'webhook_processed_at' => now(),
                    'metadata' => array_merge($lockedPayment->metadata ?? [], ['webhook' => $payload]),
                ]);

                Log::warning("Payment webhook recorded failure for order {$lockedOrder->order_code}");
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
