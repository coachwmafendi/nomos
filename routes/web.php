<?php

use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;


Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/dashboard', 'pages.dashboard')->name('dashboard');
    Route::livewire('/transactions', 'transactions.manage-transactions')->name('transactions');
    Route::livewire('/manage-categories', 'manage-categories')->name('categories');
    Route::livewire('/bars-report', 'bars-report')->name('bars-report');
    Route::livewire('/transaction-report', 'transactions.transaction-report')->name('report');
    Route::livewire('/budget', 'budget')->name('budget');
    Route::livewire('/quote', 'financial-quote')->name('quote');
    Route::livewire('/recurring', 'recurring-transactions')->name('recurring');

    Route::livewire('/insights', 'pages.insights')->name('insights');

    
    //route for export
    Route::get('/transactions/export', [ExportController::class, 'csv'])->name('transactions.export');

});

require __DIR__.'/settings.php';