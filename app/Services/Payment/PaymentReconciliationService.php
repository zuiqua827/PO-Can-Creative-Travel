<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Notifications\PaymentReceivedNotification;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentReconciliationService
{
    public function __construct(
        protected PaymentGatewayInterface $gateway
    ) {}

    /**
     * Reconcile local pending and unconfirmed payments against the provider.
     *
     * @param  int  $hours  Lookback window in hours
     * @param  bool  $dryRun  Simulate without database writes
     * @return array Summary of processed, synced, consistent, and failed counts
     */
    public function reconcile(int $hours = 48, bool $dryRun = false): array
    {
        $startTime = microtime(true);
        $since = now()->subHours($hours);

        $processed = 0;
        $synced = 0;
        $alreadyConsistent = 0;
        $failed = 0;

        Log::channel('payments')->info("Starting payment reconciliation sweep (Lookback: {$hours}h, Dry Run: ".($dryRun ? 'yes' : 'no').')');

        // Look up orders in non-final state or pending payments
        Order::where(function ($q) {
            $q->where('status', 'pending')
                ->orWhere('payment_status', 'unpaid');
        })
            ->where('created_at', '>=', $since)
            ->with(['payment', 'user', 'orderItems'])
            ->chunkById(50, function ($orders) use (&$processed, &$synced, &$alreadyConsistent, &$failed, $dryRun) {
                foreach ($orders as $order) {
                    $processed++;

                    try {
                        $payment = $order->payment;
                        $ref = $payment?->provider_transaction_id ?: $payment?->payment_reference ?: $order->order_code;

                        if (! $ref) {
                            $alreadyConsistent++;

                            continue;
                        }

                        $providerResult = $this->gateway->getPaymentStatus($ref);

                        // If provider confirms payment is settled/success
                        if ($providerResult->isSuccess()) {
                            if ($order->payment_status === 'paid' && $order->status === 'confirmed') {
                                $alreadyConsistent++;

                                continue;
                            }

                            if ($dryRun) {
                                $synced++;
                                Log::channel('payments')->info("[DRY-RUN] Payment would be reconciled to PAID for {$order->order_code}");

                                continue;
                            }

                            DB::transaction(function () use ($order, $payment, $providerResult) {
                                $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
                                $lockedPayment = $payment ? Payment::where('id', $payment->id)->lockForUpdate()->first() : null;

                                if (! $lockedOrder || $lockedOrder->payment_status === 'paid') {
                                    return;
                                }

                                if ($lockedPayment) {
                                    $lockedPayment->update([
                                        'status' => 'success',
                                        'provider' => $this->gateway->getProviderName(),
                                        'provider_transaction_id' => $providerResult->getTransactionId() ?: $lockedPayment->provider_transaction_id,
                                        'paid_at' => now(),
                                        'webhook_processed_at' => now(),
                                        'metadata' => array_merge($lockedPayment->metadata ?? [], [
                                            'reconciled_at' => now()->toISOString(),
                                            'reconcile_result' => $providerResult->getPayload(),
                                        ]),
                                    ]);
                                }

                                $lockedOrder->update([
                                    'status' => 'confirmed',
                                    'payment_status' => 'paid',
                                    'expires_at' => null,
                                ]);

                                AuditLogger::log(
                                    'payment_reconciliation_synced',
                                    Order::class,
                                    $lockedOrder->id,
                                    [
                                        'order_code' => $lockedOrder->order_code,
                                        'provider' => $this->gateway->getProviderName(),
                                        'status' => 'confirmed',
                                        'payment_status' => 'paid',
                                    ]
                                );

                                Log::channel('payments')->info("Payment reconciled to PAID for order {$lockedOrder->order_code}");

                                try {
                                    $lockedOrder->user?->notify(new PaymentReceivedNotification($lockedOrder));
                                } catch (\Throwable $e) {
                                    Log::channel('payments')->error("Failed to notify user on reconciliation for {$lockedOrder->order_code}: ".$e->getMessage());
                                }
                            });

                            $synced++;

                        } elseif ($providerResult->isExpired() || $order->isExpired()) {
                            // If expired on provider or locally past deadline
                            if ($order->status === 'cancelled' && $order->payment_status === 'expired') {
                                $alreadyConsistent++;

                                continue;
                            }

                            if ($dryRun) {
                                $synced++;

                                continue;
                            }

                            DB::transaction(function () use ($order, $payment) {
                                $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
                                $lockedPayment = $payment ? Payment::where('id', $payment->id)->lockForUpdate()->first() : null;

                                if (! $lockedOrder || $lockedOrder->status === 'cancelled') {
                                    return;
                                }

                                if ($lockedPayment && $lockedPayment->status !== 'expired') {
                                    $lockedPayment->update([
                                        'status' => 'expired',
                                        'expired_at' => now(),
                                    ]);
                                }

                                $lockedOrder->update([
                                    'status' => 'cancelled',
                                    'payment_status' => 'expired',
                                ]);

                                AuditLogger::log(
                                    'payment_reconciliation_expired',
                                    Order::class,
                                    $lockedOrder->id,
                                    ['order_code' => $lockedOrder->order_code]
                                );
                            });

                            $synced++;

                        } elseif ($providerResult->isFailed()) {
                            if ($payment && $payment->status === 'failed') {
                                $alreadyConsistent++;

                                continue;
                            }

                            if (! $dryRun && $payment) {
                                $payment->update([
                                    'status' => 'failed',
                                    'failed_at' => now(),
                                ]);
                            }

                            $synced++;
                        } else {
                            $alreadyConsistent++;
                        }

                    } catch (\Throwable $e) {
                        $failed++;
                        Log::channel('payments')->error("Reconciliation error for order {$order->order_code}: ".$e->getMessage());
                    }
                }
            });

        $report = [
            'processed' => $processed,
            'synced' => $synced,
            'already_consistent' => $alreadyConsistent,
            'failed' => $failed,
            'dry_run' => $dryRun,
            'duration_seconds' => round(microtime(true) - $startTime, 2),
        ];

        Log::channel('payments')->info('Payment reconciliation completed', $report);

        return $report;
    }

    /**
     * Reconcile a specific single Order or Payment record.
     */
    public function reconcileOrder(Order|Payment $target, bool $dryRun = false): array
    {
        $order = $target instanceof Payment ? $target->order : $target;
        if (! $order) {
            return ['status' => 'failed', 'message' => 'Order not found'];
        }

        $payment = $order->payment;
        $ref = $payment?->provider_transaction_id ?: $payment?->payment_reference ?: $order->order_code;

        if (! $ref) {
            return ['status' => 'consistent', 'message' => 'No reference available'];
        }

        try {
            $providerResult = $this->gateway->getPaymentStatus($ref);

            if ($providerResult->isSuccess()) {
                if ($order->payment_status === 'paid' && $order->status === 'confirmed') {
                    return ['status' => 'consistent', 'message' => 'Already consistent'];
                }

                if ($dryRun) {
                    return ['status' => 'synced', 'message' => 'Dry run synced to paid'];
                }

                DB::transaction(function () use ($order, $payment, $providerResult) {
                    $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
                    $lockedPayment = $payment ? Payment::where('id', $payment->id)->lockForUpdate()->first() : null;

                    if (! $lockedOrder || $lockedOrder->payment_status === 'paid') {
                        return;
                    }

                    if ($lockedPayment) {
                        $lockedPayment->update([
                            'status' => 'success',
                            'provider' => $this->gateway->getProviderName(),
                            'provider_transaction_id' => $providerResult->getTransactionId() ?: $lockedPayment->provider_transaction_id,
                            'paid_at' => now(),
                            'webhook_processed_at' => now(),
                            'metadata' => array_merge($lockedPayment->metadata ?? [], [
                                'reconciled_at' => now()->toISOString(),
                                'reconcile_result' => $providerResult->getPayload(),
                            ]),
                        ]);
                    }

                    $lockedOrder->update([
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                        'expires_at' => null,
                    ]);

                    AuditLogger::log(
                        'payment_reconciliation_synced',
                        'order',
                        $lockedOrder->id,
                        [
                            'order_code' => $lockedOrder->order_code,
                            'provider' => $this->gateway->getProviderName(),
                            'status' => 'confirmed',
                            'payment_status' => 'paid',
                        ]
                    );
                });

                return ['status' => 'synced', 'message' => 'Synchronized to paid'];
            }

            return ['status' => 'consistent', 'message' => 'Consistent with provider'];
        } catch (\Throwable $e) {
            Log::channel('payments')->error("Single reconciliation error for {$order->order_code}: ".$e->getMessage());

            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }
}
