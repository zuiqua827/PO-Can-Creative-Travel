<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'seat_capacity',
        'facilities',
        'description',
        'status',
    ];

    protected $casts = [
        'facilities' => 'array',
        'seat_capacity' => 'integer',
    ];

    public function busSeats(): HasMany
    {
        return $this->hasMany(BusSeat::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Auto generate seats for this bus in a 2-2 configuration (columns A, B, C, D)
     */
    public function generateSeats(?int $capacity = null): void
    {
        $totalSeats = $capacity ?? $this->seat_capacity;
        $cols = ['A', 'B', 'C', 'D'];
        $seatCount = 0;
        $row = 1;

        // Delete existing seats if any
        $this->busSeats()->delete();

        while ($seatCount < $totalSeats) {
            foreach ($cols as $col) {
                if ($seatCount >= $totalSeats) {
                    break;
                }
                $this->busSeats()->create([
                    'seat_number' => $row . $col,
                    'row' => $row,
                    'column' => $col,
                    'status' => 'available',
                ]);
                $seatCount++;
            }
            $row++;
        }
    }
}
