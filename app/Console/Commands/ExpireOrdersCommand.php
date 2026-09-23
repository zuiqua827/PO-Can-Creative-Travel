<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Notifications\BookingExpiredNotification;
use App\Services\Audit\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and expire overdue pending unpaid CAN Travel orders and release reserved seats';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting automated order expiration sweep...');

        $now = now();
        $expiredCount = 0;

        // Query overdue pending orders in chunks to avoid memory spikes
        Order::where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->with(['payment', 'user', 'orderItems'])
            ->chunkById(50, function ($orders) use (&$expiredCount) {
                foreach ($orders as $order) {
                    try {
                        DB::transaction(function () use ($order) {
                            $lockedOrder = Order::where('id', $order->id)
                                ->lockForUpdate()
                                ->first();

                            // Re-verify criteria under row lock to prevent race conditions
                            if (! $lockedOrder || $lockedOrder->status !== 'pending' || $lockedOrder->payment_status !== 'unpaid') {
                                return;
                            }

                            $lockedOrder->update([
                                'status' => 'cancelled',
                                'payment_status' => 'expired',
                            ]);

                            if ($lockedOrder->payment && $lockedOrder->payment->status !== 'expired') {
                                $lockedOrder->payment->update([
                                    'status' => 'expired',
                                    'expired_at' => now(),
                                ]);
                            }

                            AuditLogger::log('order_expired_by_scheduler', 'order', $lockedOrder->id, [
                                'order_code' => $lockedOrder->order_code,
                                'expired_at' => now()->toIso8601String(),
                            ]);

                            Log::info("Order expired automatically: {$lockedOrder->order_code} (Seats released)");
                        });

                        $expiredCount++;

                        // Dispatch customer notification
                        try {
                            $order->user?->notify(new BookingExpiredNotification($order));
                        } catch (\Throwable $e) {
                            Log::error("Failed to notify user for expired order {$order->order_code}: ".$e->getMessage());
                        }

                    } catch (\Throwable $e) {
                        Log::error("Failed to expire order {$order->order_code}: ".$e->getMessage());
                        $this->error("Error processing order {$order->order_code}: {$e->getMessage()}");
                    }
                }
            });

        $this->info("Order expiration sweep complete. Total expired and seats released: {$expiredCount}");

        return Command::SUCCESS;
    }
}
