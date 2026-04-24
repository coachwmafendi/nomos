<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Transaction;
use App\Models\RecurringTransaction;
use Illuminate\Support\Carbon;

new class extends Component
{
    public string $month;
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
        $this->syncDatesFromMonth();
    }

    public function updatedMonth(): void
    {
        $this->syncDatesFromMonth();
        $this->dispatch('insights-month-changed');
    }

    public function previousMonth(): void
    {
        $this->month = Carbon::parse($this->month . '-01')->subMonth()->format('Y-m');
        $this->syncDatesFromMonth();
        $this->dispatch('insights-month-changed');
    }

    public function nextMonth(): void
    {
        $this->month = Carbon::parse($this->month . '-01')->addMonth()->format('Y-m');
        $this->syncDatesFromMonth();
        $this->dispatch('insights-month-changed');
    }

    protected function syncDatesFromMonth(): void
    {
        $start = Carbon::parse($this->month . '-01')->startOfMonth();
        $end = Carbon::parse($this->month . '-01')->endOfMonth();

        $this->dateFrom = $start->toDateString();
        $this->dateTo = $end->toDateString();
    }

    protected function expenseBaseQuery()
    {
        return Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereBetween('date', [$this->dateFrom, $this->dateTo]);
    }

    protected function categoryBaseQuery()
    {
        return Transaction::query()
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', auth()->id())
            ->where('transactions.type', 'expense');
    }

    #[Computed]
    public function totalSpending(): float
    {
        return (float) $this->expenseBaseQuery()->sum('amount');
    }

    #[Computed]
    public function averageDailySpending(): float
    {
        $days = max(1, Carbon::parse($this->dateFrom)->diffInDays(Carbon::parse($this->dateTo)) + 1);

        return round($this->totalSpending / $days, 2);
    }

    #[Computed]
    public function previousMonthTotal(): float
    {
        $start = Carbon::parse($this->month . '-01')->subMonth()->startOfMonth()->toDateString();
        $end = Carbon::parse($this->month . '-01')->subMonth()->endOfMonth()->toDateString();

        return (float) Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereBetween('date', [$start, $end])
            ->sum('amount');
    }

    #[Computed]
    public function topCategory(): array
    {
        $row = $this->categoryBaseQuery()
            ->whereBetween('transactions.date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('COALESCE(categories.name, "Uncategorized") as name, SUM(transactions.amount) as total')
            ->groupBy('name')
            ->orderByDesc('total')
            ->first();

        return [
            'name' => $row?->name ?? 'No data',
            'total' => (float) ($row?->total ?? 0),
        ];
    }

    #[Computed]
    public function trendData(): array
    {
        $results = $this->expenseBaseQuery()
            ->selectRaw('DATE(date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $series = [];
        $cursor = Carbon::parse($this->dateFrom);
        $end = Carbon::parse($this->dateTo);

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('j M');
            $series[] = (float) ($results[$key]->total ?? 0);
            $cursor->addDay();
        }

        return compact('labels', 'series');
    }

    #[Computed]
    public function categoryBreakdown(): \Illuminate\Support\Collection
    {
        return $this->categoryBaseQuery()
            ->whereBetween('transactions.date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('COALESCE(categories.name, "Uncategorized") as name, SUM(transactions.amount) as total')
            ->groupBy('name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();
    }

    #[Computed]
    public function categoryChanges(): \Illuminate\Support\Collection
    {
        $currentStart = Carbon::parse($this->month . '-01')->startOfMonth()->toDateString();
        $currentEnd = Carbon::parse($this->month . '-01')->endOfMonth()->toDateString();
        $previousStart = Carbon::parse($this->month . '-01')->subMonth()->startOfMonth()->toDateString();
        $previousEnd = Carbon::parse($this->month . '-01')->subMonth()->endOfMonth()->toDateString();

        $current = $this->categoryBaseQuery()
            ->whereBetween('transactions.date', [$currentStart, $currentEnd])
            ->selectRaw('COALESCE(categories.name, "Uncategorized") as name, SUM(transactions.amount) as total')
            ->groupBy('name')
            ->pluck('total', 'name');

        $previous = $this->categoryBaseQuery()
            ->whereBetween('transactions.date', [$previousStart, $previousEnd])
            ->selectRaw('COALESCE(categories.name, "Uncategorized") as name, SUM(transactions.amount) as total')
            ->groupBy('name')
            ->pluck('total', 'name');

        return collect($current->keys())
            ->merge($previous->keys())
            ->unique()
            ->map(function ($name) use ($current, $previous) {
                $currentTotal = (float) ($current[$name] ?? 0);
                $previousTotal = (float) ($previous[$name] ?? 0);

                return [
                    'name' => $name,
                    'current' => $currentTotal,
                    'previous' => $previousTotal,
                    'difference' => $currentTotal - $previousTotal,
                ];
            })
            ->sortByDesc(fn ($item) => abs($item['difference']))
            ->values();
    }

    #[Computed]
    public function biggestIncrease(): array
    {
        $item = $this->categoryChanges->sortByDesc('difference')->first();

        return $item ?: ['name' => 'No data', 'difference' => 0];
    }

    #[Computed]
    public function biggestDecrease(): array
    {
        $item = $this->categoryChanges->sortBy('difference')->first();

        return $item ?: ['name' => 'No data', 'difference' => 0];
    }

    #[Computed]
    public function highestSpendingDay(): array
    {
        $row = $this->expenseBaseQuery()
            ->selectRaw("CAST(strftime('%w', date) AS INTEGER) as weekday, SUM(amount) as total")
            ->groupBy('weekday')
            ->orderByDesc('total')
            ->first();

        $map = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return [
            'name' => $row ? $map[(int) $row->weekday] : 'No data',
            'total' => (float) ($row->total ?? 0),
        ];
    }

    #[Computed]
    public function weekendVsWeekday(): array
    {
        $rows = $this->expenseBaseQuery()
            ->selectRaw("CASE WHEN CAST(strftime('%w', date) AS INTEGER) IN (0, 6) THEN 'weekend' ELSE 'weekday' END as bucket, SUM(amount) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $weekend = (float) ($rows['weekend'] ?? 0);
        $weekday = (float) ($rows['weekday'] ?? 0);

        $message = match (true) {
            $weekend > $weekday => 'Weekend spending is higher than weekday spending.',
            $weekday > $weekend => 'Weekday spending is higher than weekend spending.',
            default => 'Weekend and weekday spending are about the same.',
        };

        return [
            'weekend' => $weekend,
            'weekday' => $weekday,
            'message' => $message,
        ];
    }

    #[Computed]
    public function monthlySpike(): array
    {
        $row = $this->expenseBaseQuery()
            ->selectRaw('DATE(date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderByDesc('total')
            ->first();

        if (! $row) {
            return [
                'day' => 'No data',
                'total' => 0,
                'message' => 'No unusual spikes detected this period.',
            ];
        }

        return [
            'day' => Carbon::parse($row->day)->format('j M'),
            'total' => (float) $row->total,
            'message' => 'The highest spending day this month was ' . Carbon::parse($row->day)->format('j M') . '.',
        ];
    }

    #[Computed]
    public function recurringSummary(): array
    {
        if (! class_exists(RecurringTransaction::class)) {
            return [
                'count' => 0,
                'total' => 0,
                'next_due' => 'Recurring feature not available',
            ];
        }

        $query = RecurringTransaction::query()->where('user_id', auth()->id());
        $count = (clone $query)->count();
        $total = (float) (clone $query)->sum('amount');
        $nextDue = (clone $query)->whereNotNull('next_due_date')->orderBy('next_due_date')->value('next_due_date');

        return [
            'count' => $count,
            'total' => $total,
            'next_due' => $nextDue ? Carbon::parse($nextDue)->format('j M Y') : 'No upcoming due date',
        ];
    }

    #[Computed]
    public function summaryText(): string
    {
        if ($this->totalSpending <= 0) {
            return 'Add a few transactions to unlock insights for this month.';
        }

        $diff = $this->totalSpending - $this->previousMonthTotal;
        $direction = $diff > 0 ? 'higher' : ($diff < 0 ? 'lower' : 'the same');

        return 'Your spending this month is RM ' . number_format(abs($diff), 2) . ' ' . $direction . ' than last month.';
    }

    #[Computed]
    public function recommendations(): array
    {
        $items = [];

        if ($this->topCategory['name'] !== 'No data') {
            $items[] = 'Review your ' . $this->topCategory['name'] . ' spending, as it is your largest category this month.';
        }

        if ($this->biggestIncrease['difference'] > 0 && $this->biggestIncrease['name'] !== 'No data') {
            $items[] = $this->biggestIncrease['name'] . ' increased the most compared to last month.';
        }

        if ($this->weekendVsWeekday['weekend'] > $this->weekendVsWeekday['weekday']) {
            $items[] = 'Weekend spending is higher than weekdays, so that may be the best place to cut back.';
        }

        if ($this->recurringSummary['count'] > 0) {
            $items[] = 'Review recurring expenses regularly to avoid paying for things you no longer use.';
        }

        return collect($items)->filter()->take(3)->values()->all();
    }
};
?>

<div class="p-6 space-y-6">
    <style>
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .insight-animate { animation: fadeUp 0.45s ease-out both; }
        .insight-hover { transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease; }
        .insight-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06); }
        .insight-delay-1 { animation-delay: 0.03s; }
        .insight-delay-2 { animation-delay: 0.06s; }
        .insight-delay-3 { animation-delay: 0.09s; }
        .insight-delay-4 { animation-delay: 0.12s; }
        .insight-delay-5 { animation-delay: 0.15s; }
    </style>

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between insight-animate">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Insights</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Understand where your money goes and what changed this month.</p>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-3">{{ $this->summaryText }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="previousMonth" class="px-3 py-2 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 text-sm">←</button>
            <input type="month" wire:model.live="month" class="px-3 py-2 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 text-sm" />
            <button wire:click="nextMonth" class="px-3 py-2 rounded-lg bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 text-sm">→</button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white dark:bg-zinc-900 p-4 shadow-sm border border-gray-100 dark:border-zinc-800 insight-animate insight-delay-1 insight-hover">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total spending</p>
            <p class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">RM {{ number_format($this->totalSpending, 2) }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">You spent this amount in the selected month.</p>
        </div>

        <div class="rounded-xl bg-white dark:bg-zinc-900 p-4 shadow-sm border border-gray-100 dark:border-zinc-800 insight-animate insight-delay-2 insight-hover">
            <p class="text-sm text-gray-500 dark:text-gray-400">Average per day</p>
            <p class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">RM {{ number_format($this->averageDailySpending, 2) }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Average daily spending for this period.</p>
        </div>

        <div class="rounded-xl bg-white dark:bg-zinc-900 p-4 shadow-sm border border-gray-100 dark:border-zinc-800 insight-animate insight-delay-3 insight-hover">
            <p class="text-sm text-gray-500 dark:text-gray-400">Top category</p>
            <p class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ $this->topCategory['name'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">RM {{ number_format($this->topCategory['total'], 2) }} spent in this category.</p>
        </div>

        <div class="rounded-xl bg-white dark:bg-zinc-900 p-4 shadow-sm border border-gray-100 dark:border-zinc-800 insight-animate insight-delay-4 insight-hover">
            <p class="text-sm text-gray-500 dark:text-gray-400">Biggest increase</p>
            <p class="text-2xl font-bold mt-2 text-gray-900 dark:text-white">{{ $this->biggestIncrease['name'] }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">+RM {{ number_format(max(0, $this->biggestIncrease['difference']), 2) }} vs last month.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-xl bg-white dark:bg-zinc-900 p-4 shadow-sm border border-gray-100 dark:border-zinc-800 insight-animate insight-delay-1 insight-hover">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Category breakdown</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">See which categories are driving your spending this month.</p>
            <div class="mt-4 space-y-4">
                @forelse($this->categoryBreakdown as $item)
                    @php $percent = $this->totalSpending > 0 ? ($item->total / $this->totalSpending) * 100 : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-700 dark:text-gray-200">{{ $item->name }}</span>
                            <span class="text-gray-500 dark:text-gray-400">RM {{ number_format($item->total, 2) }} · {{ number_format($percent, 1) }}%</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-zinc-800 overflow-hidden">
                            <div class="h-2 rounded-full bg-red-500 transition-all duration-700" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No category data for this month.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-zinc-900 p-4 shadow-sm border border-gray-100 dark:border-zinc-800 insight-animate insight-delay-2 insight-hover">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Category movement</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">The biggest increase and decrease compared to last month.</p>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="rounded-lg bg-red-50 dark:bg-red-500/10 p-4 border border-red-100 dark:border-red-500/20">
                    <p class="text-sm font-medium text-red-700 dark:text-red-300">Biggest increase</p>
                    <p class="text-lg font-bold mt-1 text-red-700 dark:text-red-300">{{ $this->biggestIncrease['name'] }}</p>
                    <p class="text-sm mt-1 text-red-600 dark:text-red-400">+RM {{ number_format(max(0, $this->biggestIncrease['difference']), 2) }}</p>
                </div>
                <div class="rounded-lg bg-green-50 dark:bg-green-500/10 p-4 border border-green-100 dark:border-green-500/20">
                    <p class="text-sm font-medium text-green-900 dark:text-green-500">Biggest decrease</p>
                    <p class="text-lg font-bold mt-1 text-red-700 dark:text-red-300">{{ $this->biggestDecrease['name'] }}</p>
                    <p class="text-sm mt-1 text-green-600 dark:text-green-400">RM {{ number_format(min(0, $this->biggestDecrease['difference']), 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div
        class="rounded-xl bg-white dark:bg-zinc-900 p-4 shadow-sm border border-gray-100 dark:border-zinc-800 insight-animate insight-delay-3 insight-hover"
        wire:key="insights-trend-{{ $month }}"
        x-data="{
            chart: null,
            init() {
                this.renderChart()
                this.$watch(() => @js($month), () => {
                    this.renderChart()
                })
                window.addEventListener('insights-month-changed', () => this.renderChart())
            },
            renderChart() {
                if (this.chart) {
                    this.chart.destroy()
                    this.chart = null
                }

                const isDark = document.documentElement.classList.contains('dark')

                this.chart = new ApexCharts(this.$refs.chart, {
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: { show: false },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 550,
                            animateGradually: { enabled: true, delay: 50 },
                            dynamicAnimation: { enabled: true, speed: 320 }
                        }
                    },
                    series: [{ name: 'Spending', data: @js($this->trendData['series']) }],
                    xaxis: {
                        categories: @js($this->trendData['labels']),
                        labels: { style: { colors: isDark ? '#a1a1aa' : '#6b7280' } }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: isDark ? '#a1a1aa' : '#6b7280' },
                            formatter: (val) => 'RM ' + val.toFixed(0)
                        }
                    },
                    colors: ['#ef4444'],
                    stroke: { curve: 'smooth', width: 3 },
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.22, opacityTo: 0.03, stops: [0, 100] }
                    },
                    dataLabels: { enabled: false },
                    grid: { borderColor: isDark ? '#27272a' : '#e5e7eb' },
                    tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: (val) => 'RM ' + Number(val).toFixed(2) } },
                    theme: { mode: isDark ? 'dark' : 'light' }
                })

                this.chart.render()
            }
        }"
        x-init="init()"
    >
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Spending trend</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $this->monthlySpike['message'] }}</p>
            </div>
        </div>
        <div x-ref="chart"></div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="rounded-xl bg-white dark:bg-zinc-900 p-4 shadow-sm border border-gray-100 dark:border-zinc-800 insight-animate insight-delay-4 insight-hover">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Spending patterns</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">These are the habits that appear most often in your transactions.</p>
            <div class="mt-4 space-y-3">
                <div class="rounded-lg bg-gray-50 dark:bg-zinc-800 p-3">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Highest spending day</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $this->highestSpendingDay['name'] }} — RM {{ number_format($this->highestSpendingDay['total'], 2) }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 dark:bg-zinc-800 p-3">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Weekend vs weekday</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $this->weekendVsWeekday['message'] }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 dark:bg-zinc-800 p-3">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Peak spending day</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $this->monthlySpike['message'] }} Total: RM {{ number_format($this->monthlySpike['total'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-zinc-900 p-4 shadow-sm border border-gray-100 dark:border-zinc-800 insight-animate insight-delay-5 insight-hover">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Recurring expenses</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">These are the bills and subscriptions that repeat regularly.</p>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="rounded-lg bg-gray-50 dark:bg-zinc-800 p-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Recurring items</p>
                    <p class="text-lg font-bold mt-1 text-gray-900 dark:text-white">{{ $this->recurringSummary['count'] }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 dark:bg-zinc-800 p-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Monthly recurring</p>
                    <p class="text-lg font-bold mt-1 text-gray-900 dark:text-white">RM {{ number_format($this->recurringSummary['total'], 2) }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 dark:bg-zinc-800 p-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Next due</p>
                    <p class="text-sm font-medium mt-1 text-gray-900 dark:text-white">{{ $this->recurringSummary['next_due'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-zinc-900 p-4 shadow-sm border border-gray-100 dark:border-zinc-800 insight-animate">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Recommended actions</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Small changes here can help reduce monthly spending.</p>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            @forelse($this->recommendations as $item)
                <div class="rounded-lg bg-gray-50 dark:bg-zinc-800 p-3">
                    <p class="text-sm text-gray-700 dark:text-gray-200">{{ $item }}</p>
                </div>
            @empty
                <div class="rounded-lg bg-gray-50 dark:bg-zinc-800 p-3 md:col-span-3">
                    <p class="text-sm text-gray-700 dark:text-gray-200">No recommendations yet. Add more transactions to unlock smarter insights.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>