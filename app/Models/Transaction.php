<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'description',
        'amount',
        'type',
        'category',
        'date',
    ];

    protected $casts = [
    'date'   => 'datetime',  // ← tukar dari 'date:Y-m-d'
    'amount' => 'decimal:2',
];

}
