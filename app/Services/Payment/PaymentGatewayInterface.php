<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Charge an order with the gateway.
     */
    public function charge(Order $order, array $payload = []): PaymentResult;

    /**
     * Get payment status from the gateway provider.
     */
    public function getPaymentStatus(string $transactionIdOrReference): PaymentResult;

    /**
     * Handle incoming webhook event payload.
     */
    public function handleWebhook(array $payload, string $signature = ''): PaymentResult;

    /**
     * Cancel a payment or authorization on the gateway.
     */
    public function cancelPayment(Order $order): PaymentResult;

    /**
     * Expire a pending payment on the gateway.
     */
    public function expirePayment(Order $order): PaymentResult;

    /**
     * Verify authenticity of a webhook request signature.
     */
    public function verifyWebhookSignature(Request $request): bool;

    /**
     * Get the provider identifier (e.g. 'simulation', 'midtrans', 'xendit').
     */
    public function getProviderName(): string;
}
