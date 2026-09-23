<?php

namespace App\Console\Commands;

use App\Services\Payment\PaymentReconciliationService;
use Illuminate\Console\Command;

class ReconcilePaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:reconcile 
                            {--hours=48 : Lookback window in hours} 
                            {--dry-run : Simulate reconciliation without updating database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile local pending and unconfirmed payments with the payment gateway provider';

    /**
     * Execute the console command.
     */
    public function handle(PaymentReconciliationService $service): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Starting payment reconciliation sweep (Lookback: {$hours}h".($dryRun ? ' [DRY RUN]' : '').')...');

        $result = $service->reconcile($hours, $dryRun);

        $this->table(
            ['Metric', 'Count / Value'],
            [
                ['Processed Orders', $result['processed']],
                ['Synced / Reconciled', $result['synced']],
                ['Already Consistent', $result['already_consistent']],
                ['Failed / Errors', $result['failed']],
                ['Dry Run Mode', $result['dry_run'] ? 'Yes' : 'No'],
                ['Duration', $result['duration_seconds'].'s'],
            ]
        );

        $this->info(sprintf(
            'Reconciliation Summary: Processed: %d | Synced: %d | Already consistent: %d | Failed: %d',
            $result['processed'],
            $result['synced'],
            $result['already_consistent'],
            $result['failed']
        ));

        return Command::SUCCESS;
    }
}
