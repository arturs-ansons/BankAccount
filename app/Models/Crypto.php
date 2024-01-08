<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crypto extends Model
{
    use HasFactory;

    protected $fillable = [
        'crypto_name',
        'usd_rate',
    ];

    protected $attributes = [
        'usd_rate' => 0.0,
    ];
}
