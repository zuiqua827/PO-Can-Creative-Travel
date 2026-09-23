<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'bus_seat_id',
        'ticket_token',
        'passenger_name',
        'passenger_phone',
        'passenger_id_number',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrderItem $item) {
            if (empty($item->ticket_token)) {
                $item->ticket_token = 'TKT-'.date('Y').'-'.strtoupper(Str::random(10));
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function busSeat(): BelongsTo
    {
        return $this->belongsTo(BusSeat::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }
}
