<?php

namespace App\Models;

use App\Models\Scopes\UserCategoryScope;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'type'];

    protected $guarded = ['user_id'];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserCategoryScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
