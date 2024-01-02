<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiCurrency extends Model
{
    protected $table = 'api_currencies';
    protected $primaryKey = 'symbol';
    public $incrementing = false; // Indicates if the IDs are auto-incrementing.

    protected $fillable = ['symbol', 'rate'];
}
