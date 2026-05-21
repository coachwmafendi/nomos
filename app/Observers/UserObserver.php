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
            $cat = new Category(['name' => $category['name'], 'type' => $category['type']]);
            $cat->user_id = $user->id;
            $cat->save();
        }
    }
}
