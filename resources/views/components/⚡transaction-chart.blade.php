<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    public int    $selectedYear;
    public string $selectedMonth = '';

    public function mount(): void
    {
        $this->selectedYear = now()->year;
    }

    #[On('transaction-updated')]
    public function refreshChart(): void
    {
        $this->forgetChartCache($this->selectedYear);
        unset($this->chartData);
        $this->dispatch('chart-data-updated', chartData: $this->chartData);
    }

    public function updatedSelectedYear(): void
    {
        $this->forgetChartCache($this->selectedYear);
        unset($this->chartData);
        $this->dispatch('chart-data-updated', chartData: $this->chartData);
    }

    public function updatedSelectedMonth(): void
    {
        unset($this->chartData);
        $this->dispatch('chart-data-updated', chartData: $this->chartData);
    }

    protected function forgetChartCache(int $year): void
    {
        Cache::forget("chart_data_{$year}_");
        for ($m = 1; $m <= 12; $m++) {
            Cache::forget("chart_data_{$year}_{$m}");
        }
    }

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
            1  => 'January',   2  => 'February', 3  => 'March',
            4  => 'April',     5  => 'May',       6  => 'June',
            7  => 'July',      8  => 'August',    9  => 'September',
            10 => 'October',   11 => 'November',  12 => 'December',
        ];
    }

    #[Computed]
    public function chartData(): array
    {
        $cacheKey = "chart_data_{$this->selectedYear}_{$this->selectedMonth}";

        return Cache::remember($cacheKey, 300, function () {
            return $this->selectedMonth ? $this->dailyData() : $this->monthlyData();
        });
    }

    protected function monthlyData(): array
    {
        

        $rows = Transaction::query()
            ->selectRaw("type, CAST(strftime('%m', date) AS INTEGER) as month, SUM(amount) as total")
            ->whereRaw("strftime('%Y', date) = ?", [(string) $this->selectedYear])
            ->whereIn('type', ['income', 'expense'])
            ->groupByRaw("type, strftime('%m', date)")
            ->get()
            ->groupBy('type');

        $incomeMap  = $rows->get('income', collect())->pluck('total', 'month');
        $expenseMap = $rows->get('expense', collect())->pluck('total', 'month');

        $labels = $income = $expense = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[]  = Carbon::create($this->selectedYear, $m)->format('M');
            $income[]  = round((float) ($incomeMap[$m] ?? 0), 2);
            $expense[] = round((float) ($expenseMap[$m] ?? 0), 2);
        }

        return ['labels' => $labels, 'income' => $income, 'expense' => $expense,
                'title' => "Income vs Expense — {$this->selectedYear}"];
    }

    protected function dailyData(): array
    {
        $year  = $this->selectedYear;
        $month = (int) $this->selectedMonth;
        $pad   = str_pad($month, 2, '0', STR_PAD_LEFT);

        $rows = Transaction::query()
            ->selectRaw("type, CAST(strftime('%d', date) AS INTEGER) as day, SUM(amount) as total")
            ->whereRaw("strftime('%Y', date) = ?", [(string) $year])
            ->whereRaw("strftime('%m', date) = ?", [$pad])
            ->whereIn('type', ['income', 'expense'])
            ->groupByRaw("type, strftime('%d', date)")
            ->get()
            ->groupBy('type');

        $incomeMap   = $rows->get('income', collect())->pluck('total', 'day');
        $expenseMap  = $rows->get('expense', collect())->pluck('total', 'day');
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $labels = $income = $expense = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[]  = $d;
            $income[]  = round((float) ($incomeMap[$d] ?? 0), 2);
            $expense[] = round((float) ($expenseMap[$d] ?? 0), 2);
        }

        return ['labels' => $labels, 'income' => $income, 'expense' => $expense,
                'title' => "Income vs Expense — " . Carbon::create($year, $month)->format('F') . " {$year}"];
    }
};
?>

{{--
    LANGKAH 1 — Pastikan ApexCharts di-load dalam layout utama anda:
    resources/views/components/layouts/app.blade.php

    Tambah baris ini sebelum </body>:
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>

    ATAU jika guna npm, tambah dalam resources/js/app.js:
    import ApexCharts from 'apexcharts'
    window.ApexCharts = ApexCharts
--}}

<div
    class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5 space-y-4"

    {{--
        LANGKAH 2 — Guna inline x-data (buang Alpine.data + @script sepenuhnya).
        Ini fix timing race condition antara Alpine init dan @script registration.
    --}}
    x-data="{
        chart: null,
        data: @js($this->chartData),

        renderChart() {
            if (typeof ApexCharts === 'undefined') {
                console.error('[Chart] ApexCharts tidak di-load! Tambah CDN dalam layout.')
                return
            }
            if (!this.$refs.chart) return
            if (this.chart) { this.chart.destroy(); this.chart = null }

            const dark = document.documentElement.getAttribute('data-theme') === 'dark'
                || (!document.documentElement.getAttribute('data-theme')
                    && window.matchMedia('(prefers-color-scheme: dark)').matches)

            this.chart = new ApexCharts(this.$refs.chart, {
                chart: {
                    type: 'bar',
                    height: 320,
                    width: '100%',
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    background: 'transparent',
                    foreColor: dark ? '#a1a1aa' : '#52525b',
                },
                theme: { mode: dark ? 'dark' : 'light' },
                series: [
                    { name: 'Income',  data: this.data.income  },
                    { name: 'Expense', data: this.data.expense },
                ],
                colors: ['#22c55e', '#ef4444'],
                xaxis: {
                    categories: this.data.labels,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: {
                        formatter: (v) => 'RM ' + Number(v).toLocaleString('en-MY', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
                    }
                },
                dataLabels: { enabled: false },
                plotOptions: {
                    bar: { borderRadius: 4, columnWidth: '55%', borderRadiusApplication: 'end' }
                },
                grid: {
                    borderColor: dark ? '#27272a' : '#f4f4f5',
                    strokeDashArray: 4,
                },
                legend: { position: 'top', horizontalAlign: 'right' },
                tooltip: { y: { formatter: (v) => 'RM ' + Number(v).toFixed(2) } },
            })

            this.chart.render()
        }
    }"

    {{-- LANGKAH 3 — Init chart lepas DOM ready --}}
    x-init="$nextTick(() => renderChart())"

    {{-- LANGKAH 4 — Update chart bila PHP dispatch event (tukar year/month atau add transaction) --}}
    x-on:chart-data-updated.window="data = $event.detail.chartData; $nextTick(() => renderChart())"
>
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

    <div wire:ignore>
        <div x-ref="chart" style="height: 320px; min-height: 320px;"></div>
    </div>

    {{-- @script DIBUANG sepenuhnya --}}
</div>
