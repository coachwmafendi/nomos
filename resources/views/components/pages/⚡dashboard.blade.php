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

        //  $this->dateFrom = now()->startOfMonth()->toDateString();
        // $this->dateTo = now()->endOfMonth()->toDateString();
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
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        return Transaction::query()
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('DATE(date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(function ($item) {
                $date = \Carbon\Carbon::createFromFormat('Y-m-d', $item->day);

                return [
                    'day' => $date->format('Y-m-d'),
                    'total' => (float) $item->total,
                    'label' => [$date->format('d/m'), $date->format('D')],
                ];
        });
    }


    #[Computed]
    public function monthComparison()
    {
        $today = Carbon::today();

        $currentStart = $today->copy()->startOfMonth();
        $currentEnd = $today->copy()->endOfDay();

        $previousStart = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $previousEnd = $previousStart->copy()->day(
            min($today->day, $previousStart->copy()->endOfMonth()->day)
        )->endOfDay();

        $thisMonth = Transaction::where('type', 'expense')
            ->whereBetween('date', [$currentStart, $currentEnd])
            ->sum('amount');

        $lastMonth = Transaction::where('type', 'expense')
            ->whereBetween('date', [$previousStart, $previousEnd])
            ->sum('amount');

        $difference = $thisMonth - $lastMonth;

        $percentage = $lastMonth > 0
            ? ($difference / $lastMonth) * 100
            : null;

        return [
            'this_month' => (float) $thisMonth,
            'last_month' => (float) $lastMonth,
            'difference' => (float) $difference,
            'percentage' => $percentage,
            'is_up' => $difference > 0,
            'is_down' => $difference < 0,
            'label' => 'Month to date vs last month',
        ];
    }

    #[Computed]
    public function comparisonSparkline()
    {
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
            $key = $currentDate->toDateString();

            $days[] = (float) ($results[$key]->total ?? 0);

            $currentDate->addDay();
        }

        return $days;
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
                    href="{{ route('recurring') }}"
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

    {{-- <x-dashboard.monthly-comparison :comparison="$this->monthComparison" /> --}}
        <x-dashboard.monthly-comparison
            :comparison="$this->monthComparison"
            :sparkline="$this->comparisonSparkline"
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

    {{-- FAB: Add Transaction --}}
    <a
        href="{{ route('transactions') }}?create=1"
        wire:navigate
        class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 rounded-full bg-violet-600 hover:bg-violet-700 text-white shadow-lg transition-colors"
        title="Add Transaction"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
    </a>

</div>