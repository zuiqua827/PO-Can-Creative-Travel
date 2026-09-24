<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDummyTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:clean-dummy-transactions {--force : Bypass confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all dummy/test transactions and seat reservations while preserving official demo user accounts';

    /**
     * Official accounts that must NEVER be deleted.
     */
    protected array $preservedEmails = [
        'admin@pocan.com',
        'budi@gmail.com',
        'siti@gmail.com',
        'ahmad@gmail.com',
        'demo@pocan.com',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('============================================================');
        $this->info('CAN Travel — Operational Data & Seat Reservation Cleanup');
        $this->info('Rule: DUMMY USER != DUMMY SEAT OCCUPANCY');
        $this->info('============================================================');

        $orderCount = Order::count();
        $paymentCount = Payment::count();
        $orderItemCount = OrderItem::count();

        $this->line("Found {$orderCount} orders, {$paymentCount} payments, {$orderItemCount} order items in database.");

        if (! $this->option('force') && ! $this->confirm('Proceed with cleaning dummy/test transactions and freeing all seats?')) {
            $this->warn('Cleanup cancelled by user.');

            return 0;
        }

        DB::transaction(function () use (&$deletedOrders, &$deletedPayments, &$deletedItems, &$deletedUsers) {
            // 1. Delete all payments
            $deletedPayments = Payment::query()->delete();

            // 2. Delete all order items
            $deletedItems = OrderItem::query()->delete();

            // 3. Delete all orders
            $deletedOrders = Order::query()->delete();

            // 4. Delete stray test users from Faker/test suites, keeping official demo accounts
            $deletedUsers = User::whereNotIn('email', $this->preservedEmails)->delete();

            // 5. Clean order-related audit logs
            AuditLog::whereIn('action', ['ticket_verified', 'ticket_verification_failed', 'order_status_updated', 'order_cancelled_by_customer'])
                ->delete();
        });

        $this->info("✓ Cleaned {$deletedOrders} orders.");
        $this->info("✓ Cleaned {$deletedPayments} payments.");
        $this->info("✓ Cleaned {$deletedItems} order items / seat reservations.");
        $this->info("✓ Cleaned {$deletedUsers} temporary test user accounts.");

        $remainingUsers = User::pluck('email')->toArray();
        $this->info('Preserved Official Demo Accounts: '.implode(', ', $remainingUsers));
        $this->info('All scheduled trips now have 100% available seats!');

        return 0;
    }
}
