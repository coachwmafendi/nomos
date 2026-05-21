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

<div class="max-w-5xl mx-auto pb-10">

    {{-- Page Header --}}
    @php
        $hour = now()->hour;
        $greeting = match(true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default    => 'Good evening',
        };
        $firstName = explode(' ', auth()->user()->name)[0];
    @endphp

    <div class="flex items-start justify-between flex-wrap gap-4 mb-7 animate-slide-up" style="animation-delay: 0s">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-400 mb-1">Overview</p>
            <h1 class="text-xl font-bold text-white">{{ $greeting }}, {{ $firstName }}</h1>
        </div>

        {{-- Date filter --}}
        <div class="flex items-center gap-2 bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <flux:input
                type="date"
                wire:model.live="dateFrom"
                class="border-0 bg-transparent p-0 text-xs text-zinc-300 focus:ring-0 w-28"
            />
            <span class="text-zinc-400 text-xs">–</span>
            <flux:input
                type="date"
                wire:model.live="dateTo"
                class="border-0 bg-transparent p-0 text-xs text-zinc-300 focus:ring-0 w-28"
            />
        </div>
    </div>

    {{-- Recurring Pending Banner --}}
    @if($this->pendingRecurring->count() > 0)
    <div class="relative flex items-center justify-between flex-wrap gap-3 bg-amber-500/8 border border-amber-500/25 rounded-2xl px-4 py-3.5 mb-6 overflow-hidden animate-slide-up"
         style="animation-delay: 0.04s">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-400/40 to-transparent"></div>
        <div class="flex items-center gap-3">
            <div class="relative w-2 h-2 shrink-0">
                <span class="absolute inset-0 rounded-full bg-amber-400 animate-ping opacity-75"></span>
                <span class="relative block w-2 h-2 rounded-full bg-amber-400"></span>
            </div>
            <div>
                <p class="text-sm font-semibold text-amber-400">
                    {{ $this->pendingRecurring->count() }} {{ Str::plural('transaction', $this->pendingRecurring->count()) }} due today
                </p>
                <p class="text-xs text-amber-400/60 mt-0.5">
                    {{ $this->pendingRecurring->pluck('name')->join(', ') }}
                </p>
            </div>
        </div>
        <flux:button href="{{ route('recurring') }}" size="sm" variant="ghost"
            class="text-amber-400 border-amber-500/30 hover:bg-amber-500/10 text-xs">
            Review →
        </flux:button>
    </div>
    @endif

    {{-- Summary Cards --}}
    <x-dashboard.blade-summary-cards
        :totalIncome="$this->totalIncome"
        :totalExpense="$this->totalExpense"
        :balance="$this->balance"
    />

    {{-- Month Comparison --}}
    <x-dashboard.monthly-comparison
        :comparison="$this->monthComparison"
        :sparkline="$this->comparisonSparkline"
    />

    {{-- Charts row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5">
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
    <div class="mt-5">
        <x-dashboard.blade-recent-transactions :transactions="$this->recentTransactions" />
    </div>

</div>