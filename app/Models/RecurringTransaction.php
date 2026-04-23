<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'amount',
        'type',
        'frequency',
        'start_date',
        'next_due_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'start_date'    => 'date',
        'next_due_date' => 'date',
        'end_date'      => 'date',
        'is_active'     => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function calculateNextDueDate()
    {
        return match ($this->frequency) {
            'daily'   => $this->next_due_date->addDay(),
            'weekly'  => $this->next_due_date->addWeek(),
            'monthly' => $this->next_due_date->addMonth(),
            'yearly'  => $this->next_due_date->addYear(),
        };
    }

    public function scopeDueToday($query)
    {
        return $query->where('is_active', true)
            ->where('next_due_date', '<=', now()->toDateString());
    }
}