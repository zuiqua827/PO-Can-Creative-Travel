<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider')->default('simulation')->after('payment_method');
            $table->string('provider_transaction_id')->nullable()->index()->after('payment_reference');
            $table->dateTime('failed_at')->nullable()->after('paid_at');
            $table->dateTime('expired_at')->nullable()->after('failed_at');
            $table->dateTime('webhook_processed_at')->nullable()->after('expired_at');
            $table->json('metadata')->nullable()->after('proof_file');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('ticket_token', 64)->nullable()->unique()->after('id');
        });

        // Backfill ticket_token for existing order_items
        $items = DB::table('order_items')->whereNull('ticket_token')->get();
        foreach ($items as $item) {
            DB::table('order_items')
                ->where('id', $item->id)
                ->update(['ticket_token' => 'TKT-'.date('Y').'-'.strtoupper(Str::random(10))]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'provider',
                'provider_transaction_id',
                'failed_at',
                'expired_at',
                'webhook_processed_at',
                'metadata',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('ticket_token');
        });
    }
};
