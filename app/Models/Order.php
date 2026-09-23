<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trip_id',
        'order_code',
        'total_amount',
        'status',
        'payment_status',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'confirmed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'completed' => 'bg-blue-100 text-blue-800 border-blue-300',
            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'refunded' => 'bg-purple-100 text-purple-800 border-purple-300',
            'expired' => 'bg-gray-100 text-gray-800 border-gray-300',
            default => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }
}
