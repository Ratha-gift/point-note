<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_date',
        'amount',
        'user_id',
    ];

    protected $casts = [
        'entry_date' => 'date:Y-m-d',
        'amount' => 'decimal:2',
    ];

    // Point ស្មើនឹង amount ÷ 5 (កែលេខ 5 នេះបានតាមចង់)
    public const DIVISOR = 5;

    public function getPointAttribute(): float
    {
        return round((float) $this->amount / self::DIVISOR, 1);
    }
}
