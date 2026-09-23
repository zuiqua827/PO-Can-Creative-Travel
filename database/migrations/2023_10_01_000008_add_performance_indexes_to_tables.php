<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->index(['departure_at', 'status'], 'idx_trips_departure_status');
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->index(['origin', 'destination', 'status'], 'idx_routes_origin_dest_status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['trip_id', 'status', 'payment_status'], 'idx_orders_trip_status_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropIndex('idx_trips_departure_status');
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->dropIndex('idx_routes_origin_dest_status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_trip_status_payment');
        });
    }
};
