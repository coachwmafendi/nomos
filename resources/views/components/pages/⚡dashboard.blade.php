<?php
use Livewire\Component;


use Livewire\Attributes\Computed;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

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

    #[Computed]
    public function recentTransactions()
    {
        return Transaction::with('category')
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function weeklyData()
    {
        return Transaction::where('type', 'expense')
            ->where('date', '>=', Carbon::now()->subDays(6))
            ->selectRaw('DATE(date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn($item) => [
                'day'   => Carbon::parse($item->day)->format('D'),
                'total' => (float) $item->total,
            ]);
    }

    #[Computed]
    public function topCategories()
    {
        return Transaction::where('type', 'expense')
            ->whereNotNull('category_id')
            ->with('category')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();
    }
};
?>

<div class="p-6 space-y-6">

    <livewire:financial-quote />

    <x-dashboard.blade-summary-cards
        :totalIncome="$this->totalIncome"
        :totalExpense="$this->totalExpense"
        :balance="$this->balance"
    />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-dashboard.blade-weekly-spending-chart :weeklyData="$this->weeklyData" />
        <x-dashboard.blade-top-categories :topCategories="$this->topCategories" :totalExpense="$this->totalExpense" />
    </div>

    <x-dashboard.blade-recent-transactions :transactions="$this->recentTransactions" />

</div>



