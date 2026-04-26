<?php

namespace App\Models;

use App\Models\Scopes\UserCategoryScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(UserCategoryScope::class)]
class Category extends Model
{
    protected $fillable = ['user_id', 'name', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
