<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('scopes categories to the authenticated user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $catA = Category::create(['name' => 'A Cat', 'type' => 'expense', 'user_id' => $userA->id]);
    $catB = Category::create(['name' => 'B Cat', 'type' => 'expense', 'user_id' => $userB->id]);

    $this->actingAs($userA);

    $categories = Category::all();

    expect($categories->pluck('id'))->toContain($catA->id)
        ->not->toContain($catB->id);
});

it('returns all categories when unauthenticated (for seeders)', function () {
    $userA = User::factory()->create();
    Category::create(['name' => 'A Cat', 'type' => 'expense', 'user_id' => $userA->id]);

    // No actingAs — unauthenticated context
    $categories = Category::all();

    expect($categories)->toHaveCount(1);
});
