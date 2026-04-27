<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('manage transactions page auto-opens modal when create query param is present', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->withQueryParams(['create' => '1'])
        ->test('transactions.manage-transactions')
        ->assertSet('showModal', true);
});

test('manage transactions page does not open modal without create query param', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('transactions.manage-transactions')
        ->assertSet('showModal', false);
});
