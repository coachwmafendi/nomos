<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'description',
        'amount',
        'type',
        'category_id',
        'date',
    ];

    protected $casts = [
    'date'   => 'datetime',  // ← tukar dari 'date:Y-m-d'
    'amount' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)
                ->withDefault(['name' => '—']);
    }

}
