<?php

namespace App\Observers;

use App\Data\DefaultCategories;
use App\Models\Category;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        foreach (DefaultCategories::LIST as $category) {
            Category::create([
                'name' => $category['name'],
                'type' => $category['type'],
                'user_id' => $user->id,
            ]);
        }
    }
}
