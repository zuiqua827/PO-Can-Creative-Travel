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
     * Handle incoming webhook event payload.
     */
    public function handleWebhook(array $payload, string $signature = ''): PaymentResult;

    /**
     * Verify authenticity of a webhook request signature.
     */
    public function verifyWebhookSignature(Request $request): bool;

    /**
     * Get the provider identifier (e.g. 'simulation', 'midtrans', 'xendit').
     */
    public function getProviderName(): string;
}
