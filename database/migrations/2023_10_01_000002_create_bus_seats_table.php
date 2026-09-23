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
        Schema::create('bus_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('buses')->onDelete('cascade');
            $table->string('seat_number'); // e.g. 1A, 1B, 2A, 2B, etc.
            $table->integer('row');
            $table->string('column'); // A, B, C, D
            $table->enum('status', ['available', 'blocked', 'maintenance'])->default('available');
            $table->timestamps();

            $table->unique(['bus_id', 'seat_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_seats');
    }
};
