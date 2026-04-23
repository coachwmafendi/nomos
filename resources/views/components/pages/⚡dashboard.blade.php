<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

new class extends Component {

    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->endOfMonth()->format('Y-m-d');
    }

    #[Computed]
    public function totalIncome()
    {
        return Transaction::where('type', 'income')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->sum('amount');
    }

    #[Computed]
    public function totalExpense()
    {
        return Transaction::where('type', 'expense')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->sum('amount');
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
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
public function weeklyData()
{
    // Fixed: selalu 7 hari lepas — tidak ikut dateFrom/dateTo
    $results = Transaction::where('type', 'expense')
        ->where('date', '>=', Carbon::now()->subDays(6)->startOfDay())
        ->where('date', '<=', Carbon::now()->endOfDay())
        ->selectRaw('DATE(date) as day, SUM(amount) as total')
        ->groupBy('day')
        ->orderBy('day')
        ->get()
        ->keyBy('day');

    $days = [];
    $currentDate = Carbon::now()->subDays(6);

    while ($currentDate->lte(Carbon::now())) {
        $key    = $currentDate->toDateString();
        $days[] = [
            'day'   => $currentDate->format('d/m'),
            'total' => (float) ($results[$key]->total ?? 0),
        ];
        $currentDate->addDay();
    }

    return collect($days);
}

    #[Computed]
    public function pendingRecurring()
    {
            return \App\Models\RecurringTransaction::with('category')
                ->where('user_id', auth()->id())
                ->dueToday()
                ->get();
    }

    #[Computed]
    public function topCategories()
    {
        return Transaction::where('type', 'expense')
            ->whereNotNull('category_id')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->with('category')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();
    }
};
?>

<div>

    {{-- Header + Date Filter --}}
    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <h2 class="text-lg font-semibold">Dashboard</h2>
        <div class="flex items-center gap-3">
            <flux:input
                type="date"
                wire:model.live="dateFrom"
                label="From"
            />
            <span class="text-gray-400 mt-5">→</span>
            <flux:input
                type="date"
                wire:model.live="dateTo"
                label="To"
            />
        </div>
    </div>

    {{-- Recurring Pending Banner --}}
        @if($this->pendingRecurring->count() > 0)
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></div>
                    <div>
                        <p class="font-semibold text-sm text-amber-400">
                            {{ $this->pendingRecurring->count() }} Recurring Transaction Pending
                        </p>
                        <p class="text-xs text-amber-400/70 mt-0.5">
                            {{ $this->pendingRecurring->pluck('name')->join(', ') }}
                        </p>
                    </div>
                </div>
                <flux:button
                    href="{{ route('recurring.pending') }}"
                    size="sm"
                    variant="ghost"
                    class="text-amber-400 border-amber-500/30"
                >
                    Review Now →
                </flux:button>
            </div>
        </div>
        @endif

    {{-- Summary Cards --}}
    <x-dashboard.blade-summary-cards
        :totalIncome="$this->totalIncome"
        :totalExpense="$this->totalExpense"
        :balance="$this->balance"
    />

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        {{-- <x-dashboard.blade-weekly-spending-chart :weeklyData="$this->weeklyData" /> --}}
            
            <x-dashboard.blade-weekly-spending-chart
                :weeklyData="$this->weeklyData"
                wire:key="weekly-{{ $dateFrom }}-{{ $dateTo }}"
            />
        <x-dashboard.blade-top-categories
            :topCategories="$this->topCategories"
            :totalExpense="$this->totalExpense"
        />
    </div>

    {{-- Recent Transactions --}}
    <div class="mt-6">
        <x-dashboard.blade-recent-transactions :transactions="$this->recentTransactions" />
    </div>

</div>