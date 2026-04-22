<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Transaction;

new class extends Component {

    #[Computed]
    public function totalIncome()
    {
        return Transaction::where('type', 'income')->sum('amount');
    }

    #[Computed]
    public function totalExpense()
    {
        return Transaction::where('type', 'expense')->sum('amount');
    }

    #[Computed]
    public function balance()
    {
        return $this->totalIncome - $this->totalExpense;
    }
};
?>

<div class="grid grid-cols-3 gap-4 w-full">
    <div class="bg-white rounded-xl p-4 shadow-sm">
        <p class="text-sm text-gray-500">Income</p>
        <p class="text-xl font-bold text-green-600">RM {{ number_format($this->totalIncome, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm">
        <p class="text-sm text-gray-500">Expense</p>
        <p class="text-xl font-bold text-red-500">RM {{ number_format($this->totalExpense, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm">
        <p class="text-sm text-gray-500">Balance</p>
        <p class="text-xl font-bold text-blue-600">RM {{ number_format($this->balance, 2) }}</p>
    </div>
</div>