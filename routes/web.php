<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
// Route::livewire('/transactions', 'transactions.manage-transactions')
//     ->name('transactions')
//     ->middleware(['auth']);



Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('/transactions', 'transactions.manage-transactions')->name('transactions');

});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/manage-categories', 'manage-categories' )->name('categories');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/transaction-report', 'transactions.transaction-report')->name('report');
});

require __DIR__.'/settings.php';
