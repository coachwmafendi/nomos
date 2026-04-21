<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

new class extends Livewire\Component {

    public int $selectedYear;
    public string $selectedMonth = '';
    public bool $loading = false;

    public function mount(): void
    {
        $this->selectedYear = now()->year;
    }

    // ─── Filters ─────────────────────────────────────────────────────────────

    #[Computed(cache: true, seconds: 3600)]
    public function availableYears(): array
    {
        $years = Transaction::query()
            ->selectRaw("CAST(strftime('%Y', date) AS INTEGER) as year")
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values()
            ->toArray();

        if (! in_array(now()->year, $years)) {
            array_unshift($years, now()->year);
        }

        return $years;
    }

    #[Computed(cache: true, seconds: 3600)]
    public function months(): array
    {
        return [
            1  => 'January',   2  => 'February',
            3  => 'March',     4  => 'April',
            5  => 'May',       6  => 'June',
            7  => 'July',      8  => 'August',
            9  => 'September', 10 => 'October',
            11 => 'November',  12 => 'December',
        ];
    }

    // ─── Chart Data (single query each) ──────────────────────────────────────

    #[Computed]
    public function chartData(): array
    {
        $cacheKey = "chart_data_{$this->selectedYear}_{$this->selectedMonth}";

        return Cache::remember($cacheKey, 300, function () {
            return $this->selectedMonth
                ? $this->dailyData()
                : $this->monthlyData();
        });
    }

    protected function monthlyData(): array
    {
        // Satu query sahaja — group by bulan
        $rows = Transaction::query()
            ->selectRaw("
                type,
                CAST(strftime('%m', date) AS INTEGER) as month,
                SUM(amount) as total
            ")
            ->whereRaw("strftime('%Y', date) = ?", [(string) $this->selectedYear])
            ->whereIn('type', ['income', 'expense'])
            ->groupByRaw("type, strftime('%m', date)")
            ->get()
            ->groupBy('type');

        $incomeMap  = $rows->get('income',  collect())->pluck('total', 'month');
        $expenseMap = $rows->get('expense', collect())->pluck('total', 'month');

        $labels  = [];
        $income  = [];
        $expense = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[]  = Carbon::create($this->selectedYear, $m)->format('M');
            $income[]  = round((float) ($incomeMap[$m]  ?? 0), 2);
            $expense[] = round((float) ($expenseMap[$m] ?? 0), 2);
        }

        return [
            'labels'  => $labels,
            'income'  => $income,
            'expense' => $expense,
            'title'   => "Income vs Expense — {$this->selectedYear}",
        ];
    }

    protected function dailyData(): array
    {
        $year  = $this->selectedYear;
        $month = (int) $this->selectedMonth;
        $pad   = str_pad($month, 2, '0', STR_PAD_LEFT);

        // Satu query sahaja — group by hari
        $rows = Transaction::query()
            ->selectRaw("
                type,
                CAST(strftime('%d', date) AS INTEGER) as day,
                SUM(amount) as total
            ")
            ->whereRaw("strftime('%Y', date) = ?", [(string) $year])
            ->whereRaw("strftime('%m', date) = ?", [$pad])
            ->whereIn('type', ['income', 'expense'])
            ->groupByRaw("type, strftime('%d', date)")
            ->get()
            ->groupBy('type');

        $incomeMap  = $rows->get('income',  collect())->pluck('total', 'day');
        $expenseMap = $rows->get('expense', collect())->pluck('total', 'day');

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $labels      = [];
        $income      = [];
        $expense     = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[]  = $d;
            $income[]  = round((float) ($incomeMap[$d]  ?? 0), 2);
            $expense[] = round((float) ($expenseMap[$d] ?? 0), 2);
        }

        $monthName = Carbon::create($year, $month)->format('F');

        return [
            'labels'  => $labels,
            'income'  => $income,
            'expense' => $expense,
            'title'   => "Income vs Expense — {$monthName} {$year}",
        ];
    }

    // ─── Clear cache bila data baru disimpan ──────────────────────────────────

    public static function clearChartCache(int $year, int $month = 0): void
    {
        Cache::forget("chart_data_{$year}_");
        for ($m = 1; $m <= 12; $m++) {
            Cache::forget("chart_data_{$year}_{$m}");
        }
    }
};
?>

<div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5 space-y-4">

    {{-- Header + Filter --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <flux:heading size="md">Income vs Expense</flux:heading>
            <flux:subheading class="text-xs mt-0.5">
                {{ $this->chartData['title'] }}
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:select wire:model.live="selectedMonth" class="w-36 text-sm">
                <flux:select.option value="">All Months</flux:select.option>
                @foreach($this->months as $num => $name)
                    <flux:select.option value="{{ $num }}">{{ $name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="selectedYear" class="w-24 text-sm">
                @foreach($this->availableYears as $year)
                    <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    {{-- Loading indicator --}}
    <div wire:loading class="flex items-center gap-2 text-xs text-zinc-400">
        <svg class="animate-spin h-4 w-4 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4z"></path>
        </svg>
        Loading...
    </div>

    {{-- Chart — wire:key berubah bila filter tukar, Alpine akan reinit --}}
    <div
        wire:key="chart-{{ $selectedYear }}-{{ $selectedMonth ?: 'all' }}"
        wire:loading.class="opacity-30"
        x-data="transactionChart(@js($this->chartData))"
        x-init="renderChart()"
        class="transition-opacity duration-200"
    >
        <div x-ref="chart"></div>
    </div>

</div>

@script
<script>
    Alpine.data('transactionChart', (chartData) => ({
        chart: null,
        data: chartData,

        renderChart() {
            if (this.chart) {
                this.chart.destroy()
            }

            const isDark =
                document.documentElement.classList.contains('dark') ||
                document.documentElement.getAttribute('data-theme') === 'dark'

            const textColor = isDark ? '#a1a1aa' : '#52525b'
            const gridColor = isDark ? '#27272a' : '#f4f4f5'

            this.chart = new ApexCharts(this.$refs.chart, {
                chart: {
                    type: 'bar',
                    height: 320,
                    background: 'transparent',
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    animations: {
                        enabled: true,
                        speed: 400,
                    },
                },
                series: [
                    { name: 'Income',  data: this.data.income,  color: '#22c55e' },
                    { name: 'Expense', data: this.data.expense, color: '#ef4444' },
                ],
                xaxis: {
                    categories: this.data.labels,
                    labels: {
                        style: { colors: textColor, fontSize: '12px' },
                    },
                    axisBorder: { color: gridColor },
                    axisTicks: { color: gridColor },
                },
                yaxis: {
                    labels: {
                        style: { colors: textColor, fontSize: '12px' },
                        formatter: (val) => 'RM ' + Number(val).toLocaleString('en-MY', {
                            minimumFractionDigits: 0,
                        }),
                    },
                },
                grid: {
                    borderColor: gridColor,
                    strokeDashArray: 4,
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '55%',
                    },
                },
                dataLabels: { enabled: false },
                legend: {
                    position: 'top',
                    labels: { colors: textColor },
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: (val) => 'RM ' + Number(val).toLocaleString('en-MY', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        }),
                    },
                },
            })

            this.chart.render()
        },
    }))
</script>
@endscript
