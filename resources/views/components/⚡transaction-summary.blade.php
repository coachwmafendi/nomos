<?php
// resources/views/livewire/transaction-summary.blade.php

use Livewire\Attributes\Computed;
use App\Models\Transaction;

new class extends Livewire\Component {

    #[Computed(persist: true)]
    public function totalIncome()
    {
        return Transaction::where('type', 'income')->sum('amount');
    }

    #[Computed(persist: true)]
    public function totalExpense()
    {
        return Transaction::where('type', 'expense')->sum('amount');
    }

    #[Computed(persist: true)]
    public function balance()
    {
        return $this->totalIncome - $this->totalExpense;
    }
};
?>

<div class="grid grid-cols-3 gap-4">
    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5">
        <flux:subheading>Total Income</flux:subheading>
        <p class="text-2xl font-bold text-green-500 mt-1">
            RM {{ number_format($this->totalIncome, 2) }}
        </p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5">
        <flux:subheading>Total Expense</flux:subheading>
        <p class="text-2xl font-bold text-red-500 mt-1">
            RM {{ number_format($this->totalExpense, 2) }}
        </p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5">
        <flux:subheading>Balance</flux:subheading>
        <p class="text-2xl font-bold mt-1 {{ $this->balance >= 0 ? 'text-blue-500' : 'text-red-500' }}">
            RM {{ number_format($this->balance, 2) }}
        </p>
    </div>
</div>