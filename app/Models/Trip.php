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
     * Scope to eager-load active booked seats count in one query without N+1.
     */
    public function scopeWithBookedSeatsCount($query)
    {
        return $query->withCount(['orderItems as active_booked_seats_count' => function ($q) {
            $q->whereHas('order', function ($sub) {
                $sub->where('status', '!=', 'cancelled')
                    ->where('payment_status', '!=', 'expired')
                    ->where(function ($sub2) {
                        $sub2->where('payment_status', 'paid')
                            ->orWhere(function ($pending) {
                                $pending->where('payment_status', 'unpaid')
                                    ->where('expires_at', '>', now());
                            });
                    });
            });
        }]);
    }

    /**
     * Get IDs of seats that are currently booked for this trip.
     * Paid/confirmed orders hold seats permanently (BOOKED).
     * Unpaid orders hold seats only until expires_at (HELD).
     * Cancelled and expired orders never hold seats (RELEASED / AVAILABLE).
     */
    public function getBookedSeatIds(): array
    {
        return OrderItem::whereHas('order', function ($query) {
            $query->where('trip_id', $this->id)
                ->where('status', '!=', 'cancelled')
                ->where('payment_status', '!=', 'expired')
                ->where(function ($sub) {
                    $sub->where('payment_status', 'paid')
                        ->orWhere(function ($pending) {
                            $pending->where('payment_status', 'unpaid')
                                ->where('expires_at', '>', now());
                        });
                });
        })->pluck('bus_seat_id')->toArray();
    }

    public function getAvailableSeatsCountAttribute(): int
    {
        $totalSeats = $this->bus ? $this->bus->seat_capacity : 0;
        $bookedSeats = isset($this->attributes['active_booked_seats_count'])
            ? (int) $this->attributes['active_booked_seats_count']
            : count($this->getBookedSeatIds());

        return max(0, $totalSeats - $bookedSeats);
    }
}
