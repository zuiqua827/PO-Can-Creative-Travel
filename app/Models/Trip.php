<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'route_id',
        'trip_code',
        'departure_at',
        'arrival_at',
        'price',
        'boarding_point',
        'drop_off_point',
        'status',
    ];

    protected $casts = [
        'departure_at' => 'datetime',
        'arrival_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Order::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    /**
     * Get IDs of seats that are currently booked for this trip.
     * Includes orders that are confirmed/completed, or pending and not expired.
     */
    public function getBookedSeatIds(): array
    {
        return OrderItem::whereHas('order', function ($query) {
            $query->where('trip_id', $this->id)
                ->where('status', '!=', 'cancelled')
                ->where(function ($sub) {
                    $sub->where('payment_status', 'paid')
                        ->orWhere('expires_at', '>', now());
                });
        })->pluck('bus_seat_id')->toArray();
    }

    public function getAvailableSeatsCountAttribute(): int
    {
        $totalSeats = $this->bus ? $this->bus->seat_capacity : 0;
        $bookedSeats = count($this->getBookedSeatIds());

        return max(0, $totalSeats - $bookedSeats);
    }
}
