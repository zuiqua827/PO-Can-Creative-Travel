<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGatewayInterface
{
    protected string $webhookSecret;

    public function __construct()
    {
        $this->webhookSecret = config('services.payment.webhook_secret', 'simulation_secret_token');
    }

    public function getProviderName(): string
    {
        return 'simulation';
    }

    /**
     * Charge an order in simulation mode.
     */
    public function charge(Order $order, array $payload = []): PaymentResult
    {
        // Enforce expiration check before charge
        if ($order->isExpired()) {
            Log::warning("Payment simulation rejected for expired order: {$order->order_code}");

            return PaymentResult::expired(
                $order->payment?->payment_reference ?? 'REF-EXPIRED',
                'Pesanan telah kedaluwarsa dan tidak dapat dibayar.',
                $payload
            );
        }

        $paymentRef = $order->payment?->payment_reference ?? 'SIM-'.strtoupper(Str::random(10));
        $transactionId = 'TX-SIM-'.date('YmdHis').'-'.rand(1000, 9999);

        // Check if simulate_failure flag is passed for testing
        if (! empty($payload['simulate_failure'])) {
            Log::info("Payment simulation triggered forced failure for: {$order->order_code}");

            return PaymentResult::failed($paymentRef, 'Simulasi pembayaran gagal.', [
                'provider' => $this->getProviderName(),
                'provider_transaction_id' => $transactionId,
            ]);
        }

        Log::info("Payment simulation successful for: {$order->order_code} [Tx: {$transactionId}]");

        return PaymentResult::success(
            $paymentRef,
            $transactionId,
            'Pembayaran simulasi berhasil diverifikasi secara instan.',
            [
                'provider' => $this->getProviderName(),
                'amount' => (float) $order->total_amount,
                'provider_transaction_id' => $transactionId,
                'channel' => $order->payment?->payment_method ?? 'SIMULATION_MANUAL',
            ]
        );
    }

    /**
     * Handle incoming simulated webhook event payload.
     */
    public function handleWebhook(array $payload, string $signature = ''): PaymentResult
    {
        $status = strtolower($payload['status'] ?? $payload['transaction_status'] ?? '');
        $reference = $payload['payment_reference'] ?? $payload['order_id'] ?? '';
        $transactionId = $payload['transaction_id'] ?? $payload['provider_transaction_id'] ?? 'TX-'.strtoupper(Str::random(12));

        return match ($status) {
            'settlement', 'success', 'paid' => PaymentResult::success(
                $reference,
                $transactionId,
                'Webhook payment confirmed successfully.',
                $payload
            ),
            'failed', 'failure', 'deny' => PaymentResult::failed(
                $reference,
                'Webhook payment failure received.',
                $payload
            ),
            'expired' => PaymentResult::expired(
                $reference,
                'Webhook payment expiration received.',
                $payload
            ),
            default => PaymentResult::pending(
                $reference,
                $transactionId,
                'Webhook payment pending.',
                $payload
            ),
        };
    }

    /**
     * Get payment status from the simulation provider.
     */
    public function getPaymentStatus(string $transactionIdOrReference): PaymentResult
    {
        return PaymentResult::success(
            $transactionIdOrReference,
            $transactionIdOrReference,
            'Simulated payment status retrieved.',
            ['provider' => $this->getProviderName()]
        );
    }

    /**
     * Cancel a payment in simulation mode.
     */
    public function cancelPayment(Order $order): PaymentResult
    {
        $ref = $order->payment?->payment_reference ?? $order->order_code;
        Log::info("Payment simulation cancelled for order: {$order->order_code}");

        return PaymentResult::failed($ref, 'Simulated payment cancelled.', [
            'provider' => $this->getProviderName(),
            'order_code' => $order->order_code,
        ]);
    }

    /**
     * Expire a payment in simulation mode.
     */
    public function expirePayment(Order $order): PaymentResult
    {
        $ref = $order->payment?->payment_reference ?? $order->order_code;
        Log::info("Payment simulation expired for order: {$order->order_code}");

        return PaymentResult::expired($ref, 'Simulated payment marked as expired.', [
            'provider' => $this->getProviderName(),
            'order_code' => $order->order_code,
        ]);
    }

    /**
     * Verify authenticity of a webhook request signature using HMAC SHA-256.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('X-CAN-Signature') ?? $request->header('X-Signature') ?? $request->input('signature');

        if (! $signature) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($expectedSignature, $signature) || hash_equals($this->webhookSecret, $signature);
    }

    /**
     * Helper to compute a signature for testing/simulating outgoing webhooks.
     */
    public function computeSignature(string|array $payload): string
    {
        $content = is_array($payload) ? json_encode($payload) : $payload;

        return hash_hmac('sha256', $content, $this->webhookSecret);
    }
}
