<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransPaymentGateway implements PaymentGatewayInterface
{
    protected string $serverKey;

    protected string $clientKey;

    protected bool $isProduction;

    protected string $snapUrl;

    protected string $apiBaseUrl;

    public function __construct()
    {
        $this->serverKey = (string) config('payment.drivers.midtrans.server_key', '');
        $this->clientKey = (string) config('payment.drivers.midtrans.client_key', '');
        $this->isProduction = (bool) config('payment.drivers.midtrans.is_production', false);
        $this->snapUrl = (string) config('payment.drivers.midtrans.snap_url', 'https://app.sandbox.midtrans.com/snap/v1/transactions');
        $this->apiBaseUrl = (string) config('payment.drivers.midtrans.api_base_url', 'https://api.sandbox.midtrans.com');
    }

    public function getProviderName(): string
    {
        return 'midtrans';
    }

    /**
     * Charge or create a Snap payment session for an order.
     */
    public function charge(Order $order, array $payload = []): PaymentResult
    {
        // 1. Enforce strict expiration check
        if ($order->isExpired()) {
            Log::channel('payments')->warning("Midtrans payment rejected: order {$order->order_code} is expired.");

            return PaymentResult::expired(
                $order->payment?->payment_reference ?? $order->order_code,
                'Batas waktu pembayaran pesanan telah habis (Kedaluwarsa).',
                $payload
            );
        }

        $paymentRef = $order->payment?->payment_reference ?? 'MID-'.strtoupper(Str::random(10));
        $grossAmount = (int) round((float) $order->total_amount);

        // 2. Missing credentials safety check
        if (empty($this->serverKey)) {
            if ($this->isProduction) {
                Log::channel('payments')->error("Midtrans server_key missing in production environment for order {$order->order_code}");

                return PaymentResult::failed($paymentRef, 'Midtrans belum dikonfigurasi untuk environment ini.', [
                    'provider' => $this->getProviderName(),
                    'error' => 'missing_server_key',
                ]);
            }

            Log::channel('payments')->info("Midtrans server_key not set, running in sandbox adapter mode for: {$order->order_code}");
            $simTransactionId = 'TX-MID-SANDBOX-'.date('YmdHis').'-'.rand(1000, 9999);

            if (! empty($payload['simulate_failure'])) {
                return PaymentResult::failed($paymentRef, 'Simulasi pembayaran Midtrans gagal.', [
                    'provider' => $this->getProviderName(),
                    'provider_transaction_id' => $simTransactionId,
                    'mode' => 'sandbox_simulation',
                ]);
            }

            return PaymentResult::success(
                $paymentRef,
                $simTransactionId,
                'Transaksi Midtrans sandbox berhasil diselesaikan.',
                [
                    'provider' => $this->getProviderName(),
                    'amount' => $grossAmount,
                    'order_id' => $order->order_code,
                    'provider_transaction_id' => $simTransactionId,
                    'mode' => 'sandbox_simulation',
                ]
            );
        }

        // 3. Dispatch Snap API Request to Midtrans
        try {
            $snapPayload = [
                'transaction_details' => [
                    'order_id' => $order->order_code,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $order->user?->name ?? 'Penumpang CAN Travel',
                    'email' => $order->user?->email ?? 'noreply@cantravel.co.id',
                    'phone' => $order->user?->phone ?? '08123456789',
                ],
                'callbacks' => [
                    'finish' => route('orders.show', $order),
                ],
                'expiry' => [
                    'unit' => 'minute',
                    'duration' => (int) config('payment.expiry_minutes', 120),
                ],
            ];

            $response = Http::withBasicAuth($this->serverKey, '')
                ->timeout(10)
                ->post($this->snapUrl, $snapPayload);

            if ($response->successful()) {
                $body = $response->json();
                $snapToken = $body['token'] ?? null;
                $redirectUrl = $body['redirect_url'] ?? null;

                Log::channel('payments')->info("Midtrans Snap transaction initialized for {$order->order_code}", [
                    'snap_token' => $snapToken,
                ]);

                return PaymentResult::pending(
                    $paymentRef,
                    $snapToken,
                    'Sesi pembayaran Midtrans berhasil dibuat.',
                    [
                        'provider' => $this->getProviderName(),
                        'snap_token' => $snapToken,
                        'redirect_url' => $redirectUrl,
                    ]
                );
            }

            Log::channel('payments')->error("Midtrans Snap request failed for {$order->order_code}: ".$response->body());

            return PaymentResult::failed($paymentRef, 'Gagal menghubungi server pembayaran Midtrans.', [
                'provider' => $this->getProviderName(),
                'status_code' => $response->status(),
                'error' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::channel('payments')->error("Midtrans communication error for {$order->order_code}: ".$e->getMessage());

            return PaymentResult::failed($paymentRef, 'Terjadi kendala jaringan ke payment gateway.', [
                'provider' => $this->getProviderName(),
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check real-time payment status directly from Midtrans Core API.
     */
    public function getPaymentStatus(string $transactionIdOrReference): PaymentResult
    {
        if (empty($this->serverKey)) {
            return PaymentResult::success(
                $transactionIdOrReference,
                $transactionIdOrReference,
                'Midtrans status retrieved (sandbox simulation).',
                ['provider' => $this->getProviderName()]
            );
        }

        try {
            $url = rtrim($this->apiBaseUrl, '/')."/v2/{$transactionIdOrReference}/status";
            $response = Http::withBasicAuth($this->serverKey, '')->timeout(10)->get($url);

            if ($response->successful()) {
                $payload = $response->json();

                return $this->handleWebhook($payload);
            }

            return PaymentResult::failed($transactionIdOrReference, 'Status tidak ditemukan di Midtrans.', [
                'provider' => $this->getProviderName(),
                'response' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::channel('payments')->error('Midtrans status check error: '.$e->getMessage());

            return PaymentResult::failed($transactionIdOrReference, $e->getMessage(), [
                'provider' => $this->getProviderName(),
            ]);
        }
    }

    /**
     * Cancel an active transaction on Midtrans.
     */
    public function cancelPayment(Order $order): PaymentResult
    {
        $ref = $order->payment?->payment_reference ?? $order->order_code;

        if (empty($this->serverKey)) {
            Log::channel('payments')->info("Midtrans payment cancelled (simulation) for {$order->order_code}");

            return PaymentResult::failed($ref, 'Transaksi Midtrans dibatalkan.', [
                'provider' => $this->getProviderName(),
            ]);
        }

        try {
            $url = rtrim($this->apiBaseUrl, '/')."/v2/{$order->order_code}/cancel";
            $response = Http::withBasicAuth($this->serverKey, '')->timeout(10)->post($url);

            return PaymentResult::failed($ref, 'Transaksi dibatalkan di Midtrans.', [
                'provider' => $this->getProviderName(),
                'response' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            return PaymentResult::failed($ref, $e->getMessage(), ['provider' => $this->getProviderName()]);
        }
    }

    /**
     * Expire an active transaction on Midtrans.
     */
    public function expirePayment(Order $order): PaymentResult
    {
        $ref = $order->payment?->payment_reference ?? $order->order_code;

        if (empty($this->serverKey)) {
            Log::channel('payments')->info("Midtrans payment expired (simulation) for {$order->order_code}");

            return PaymentResult::expired($ref, 'Transaksi Midtrans kedaluwarsa.', [
                'provider' => $this->getProviderName(),
            ]);
        }

        try {
            $url = rtrim($this->apiBaseUrl, '/')."/v2/{$order->order_code}/expire";
            $response = Http::withBasicAuth($this->serverKey, '')->timeout(10)->post($url);

            return PaymentResult::expired($ref, 'Transaksi kedaluwarsa di Midtrans.', [
                'provider' => $this->getProviderName(),
                'response' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            return PaymentResult::expired($ref, $e->getMessage(), ['provider' => $this->getProviderName()]);
        }
    }

    /**
     * Handle incoming Midtrans webhook notification payload.
     */
    public function handleWebhook(array $payload, string $signature = ''): PaymentResult
    {
        $txStatus = strtolower($payload['transaction_status'] ?? '');
        $fraudStatus = strtolower($payload['fraud_status'] ?? '');
        $orderId = $payload['order_id'] ?? $payload['payment_reference'] ?? '';
        $txId = $payload['transaction_id'] ?? 'TX-MID-'.strtoupper(Str::random(12));

        if ($txStatus === 'capture') {
            if ($fraudStatus === 'challenge') {
                return PaymentResult::pending($orderId, $txId, 'Payment is challenged by fraud detection system.', $payload);
            }

            return PaymentResult::success($orderId, $txId, 'Payment captured and confirmed.', $payload);
        }

        if ($txStatus === 'settlement') {
            return PaymentResult::success($orderId, $txId, 'Payment settled successfully.', $payload);
        }

        if (in_array($txStatus, ['deny', 'cancel'])) {
            return PaymentResult::failed($orderId, 'Payment was denied or cancelled.', $payload);
        }

        if ($txStatus === 'expire') {
            return PaymentResult::expired($orderId, 'Payment window has expired.', $payload);
        }

        if ($txStatus === 'refund') {
            return PaymentResult::refunded($orderId, $txId, 'Payment has been refunded.', $payload);
        }

        return PaymentResult::pending($orderId, $txId, 'Payment is pending.', $payload);
    }

    /**
     * Verify Midtrans webhook signature key using SHA-512 or HMAC-SHA256 fallback.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        // 1. Check Midtrans standard signature_key
        $signatureKey = $request->input('signature_key');
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');

        if ($signatureKey && $orderId && $statusCode && $grossAmount) {
            $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);
            if (hash_equals($expected, (string) $signatureKey)) {
                return true;
            }
        }

        // 2. Check X-CAN-Signature or X-Signature header HMAC (testing & gateway interoperability)
        $headerSig = $request->header('X-CAN-Signature') ?? $request->header('X-Signature');
        $secret = config('payment.webhook_secret', 'simulation_secret_token');
        if ($headerSig) {
            $content = $request->getContent();
            $expectedHmac = hash_hmac('sha256', $content, $secret);

            return hash_equals($expectedHmac, $headerSig) || hash_equals($secret, $headerSig);
        }

        return false;
    }
}
