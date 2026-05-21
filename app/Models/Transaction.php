<?php

namespace App\Models;

use App\Models\Scopes\UserTransactionScope;
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

    protected $guarded = ['user_id'];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserTransactionScope);
    }

    protected $casts = [
        'date' => 'datetime',
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
