<?php

namespace App\Models;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'amount',
        'type',
        'category_id',
        'date',
    ];

    protected $casts = [
        'date'   => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)
            ->withDefault(['name' => '—']);
    }

    public function attachments()
    {
        return $this->hasMany(TransactionAttachment::class);
    }

    public function latestAttachment()
    {
        return $this->hasOne(TransactionAttachment::class)->latestOfMany();
    }

}
