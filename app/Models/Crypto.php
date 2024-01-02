<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crypto extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency',
        'btc_rate',
    ];

    protected $attributes = [
        'currency' => 0.00,
        'btc_rate' => 0.00,
    ];
}
