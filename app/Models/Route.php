<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'origin',
        'destination',
        'distance',
        'estimated_duration',
        'base_price',
        'status',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->base_price, 0, ',', '.');
    }
}
