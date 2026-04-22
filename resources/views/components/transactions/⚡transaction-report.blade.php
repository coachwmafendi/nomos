<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

new class extends Component {

    public int $selectedYear;
    public string $selectedMonth = '';

    public function mount(): void
    {
        $this->selectedYear = now()->year;
    }

    #[Computed]
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

    #[Computed]
    public function months(): array
    {
        return [
            1 => 'January', 2 => 'February', 3 => 'March',
            4 => 'April', 5 => 'May', 6 => 'June',
            7 => 'July', 8 => 'August', 9 => 'September',
            10 => 'October', 11 => 'November', 12 => 'December',
        ];
    }

    protected function baseQuery()
    {
        return Transaction::query()
            ->when($this->selectedYear, fn($q) =>
                $q->whereRaw("strftime('%Y', date) = ?", [(string) $this->selectedYear])
            )
            ->when($this->selectedMonth !== '', fn($q) =>
                $q->whereRaw("strftime('%m', date) = ?", [str_pad($this->selectedMonth, 2, '0', STR_PAD_LEFT)])
            );
    }

    #[Computed]
    public function summary(): array
    {
        $rows = $this->baseQuery()
            ->selectRaw("type, SUM(amount) as total")
            ->whereIn('type', ['income', 'expense'])
            ->groupBy('type')
            ->pluck('total', 'type');

        $income  = (float) ($rows['income'] ?? 0);
        $expense = (float) ($rows['expense'] ?? 0);

        return [
            'income'  => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }

    #[Computed]
    public function categoryBreakdown()
    {
        $rows = $this->baseQuery()
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.type', 'expense')
            ->selectRaw("
                COALESCE(categories.name, 'Uncategorized') as category_name,
                SUM(transactions.amount) as total
            ")
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $rows->sum('total') ?: 1;

        return $rows->map(function ($row, $index) use ($grandTotal) {
            $row->percentage = round(($row->total / $grandTotal) * 100, 1);
            $row->rank       = $index + 1;
            return $row;
        });
    }

    #[Computed]
    public function donutChartData(): array
    {
        $top = $this->categoryBreakdown->take(6);

        return [
            'labels' => $top->pluck('category_name')->toArray(),
            'values' => $top->pluck('total')->map(fn($v) => (float) $v)->toArray(),
        ];
    }

    #[Computed]
    public function monthlyTrend(): array
    {
        $rows = Transaction::query()
            ->selectRaw("
                type,
                CAST(strftime('%m', date) AS INTEGER) as month,
                SUM(amount) as total
            ")
            ->whereRaw("strftime('%Y', date) = ?", [(string) $this->selectedYear])
            ->whereIn('type', ['income', 'expense'])
            ->groupBy('type', DB::raw("strftime('%m', date)"))
            ->get();

        $income  = array_fill(1, 12, 0);
        $expense = array_fill(1, 12, 0);

        foreach ($rows as $row) {
            if ($row->type === 'income') {
                $income[(int) $row->month]  = (float) $row->total;
            } else {
                $expense[(int) $row->month] = (float) $row->total;
            }
        }

        return [
            'labels'  => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
            'income'  => array_values($income),
            'expense' => array_values($expense),
        ];
    }

    #[Computed]
    public function reportTitle(): string
    {
        if ($this->selectedMonth === '') {
            return "Report for {$this->selectedYear}";
        }
        $monthName = $this->months[(int) $this->selectedMonth] ?? '';
        return "Report for {$monthName} {$this->selectedYear}";
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'report-' . $this->selectedYear
            . ($this->selectedMonth !== '' ? '-' . str_pad($this->selectedMonth, 2, '0', STR_PAD_LEFT) : '')
            . '.csv';

        $rows = $this->baseQuery()
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->select(
                'transactions.date',
                'transactions.description',
                'transactions.type',
                'transactions.amount',
                DB::raw("COALESCE(categories.name, 'Uncategorized') as category")
            )
            ->orderBy('transactions.date', 'desc')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Description', 'Type', 'Amount', 'Category']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->date,
                    $row->description,
                    ucfirst($row->type),
                    number_format($row->amount, 2),
                    $row->category,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
};
?>

<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Reports</flux:heading>
            <flux:subheading>{{ $this->reportTitle }}</flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:select wire:model.live="selectedMonth" class="w-36">
                <flux:select.option value="">All Months</flux:select.option>
                @foreach($this->months as $num => $name)
                    <flux:select.option value="{{ $num }}">{{ $name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="selectedYear" class="w-24">
                @foreach($this->availableYears as $year)
                    <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:button wire:click="exportCsv" icon="arrow-down-tray" variant="outline" size="sm">
                Export CSV
            </flux:button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid gap-6 lg:grid-cols-3">

        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5">
            <flux:subheading>Total Income</flux:subheading>
            <p class="text-2xl font-bold text-green-500 mt-1">
                RM {{ number_format($this->summary['income'], 2) }}
            </p>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5">
            <flux:subheading>Total Expense</flux:subheading>
            <p class="text-2xl font-bold text-red-500 mt-1">
                RM {{ number_format($this->summary['expense'], 2) }}
            </p>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5">
            <flux:subheading>Balance</flux:subheading>
            <p class="text-2xl font-bold mt-1 {{ $this->summary['balance'] >= 0 ? 'text-blue-500' : 'text-red-500' }}">
                RM {{ number_format($this->summary['balance'], 2) }}
            </p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Donut Chart --}}
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5 space-y-4">
            <div>
                <flux:heading size="md">Expense Distribution</flux:heading>
                <flux:subheading>Top categories breakdown</flux:subheading>
            </div>

            @if($this->categoryBreakdown->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    <p class="text-sm">No expense data for this period</p>
                </div>
            @else
                <div
                    wire:key="donut-{{ $selectedYear }}-{{ $selectedMonth }}"
                    x-data="donutChart(@js($this->donutChartData))"
                    x-init="renderChart()"
                >
                    <div x-ref="chart"></div>
                </div>
            @endif
        </div>

        {{-- Monthly Trend --}}
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5 space-y-4">
            <div>
                <flux:heading size="md">Monthly Trend</flux:heading>
                <flux:subheading>Income vs expense — {{ $selectedYear }}</flux:subheading>
            </div>
            <div
                wire:key="trend-{{ $selectedYear }}"
                x-data="trendChart(@js($this->monthlyTrend))"
                x-init="renderChart()"
            >
                <div x-ref="chart"></div>
            </div>
        </div>
    </div>

    {{-- Category Breakdown Table --}}
    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5 space-y-4">
        <div>
            <flux:heading size="md">Expense by Category</flux:heading>
            <flux:subheading>Ranked by total spending for selected period</flux:subheading>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Category</flux:table.column>
                <flux:table.column align="end">Total (RM)</flux:table.column>
                <flux:table.column align="end">Share</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->categoryBreakdown as $row)
                    <flux:table.row :key="$row->category_name">
                        <flux:table.cell>
                            @if($row->rank <= 3)
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold
                                    {{ $row->rank === 1 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                       ($row->rank === 2 ? 'bg-gray-100 text-gray-600 dark:bg-zinc-700 dark:text-zinc-300' :
                                       'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400') }}">
                                    {{ $row->rank }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm pl-1">{{ $row->rank }}</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                {{ $row->category_name }}
                                @if($row->rank <= 5)
                                    <flux:badge size="sm" color="{{ $row->rank === 1 ? 'yellow' : 'zinc' }}">Top {{ $row->rank }}</flux:badge>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            {{ number_format($row->total, 2) }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-16 bg-gray-100 dark:bg-zinc-700 rounded-full h-1.5">
                                    <div class="bg-red-400 h-1.5 rounded-full" style="width: {{ $row->percentage }}%"></div>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-zinc-400 w-10 text-right">{{ $row->percentage }}%</span>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
                            <div class="py-10 text-center text-gray-400 dark:text-zinc-500">
                                No expense data for this period.
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>

@script
<script>
    Alpine.data('donutChart', (chartData) => ({
        chart: null,
        data: chartData,

        renderChart() {
            if (this.chart) this.chart.destroy()

            const isDark = document.documentElement.classList.contains('dark')
                || document.documentElement.getAttribute('data-theme') === 'dark'
            const textColor = isDark ? '#a1a1aa' : '#52525b'

            this.chart = new ApexCharts(this.$refs.chart, {
                chart: {
                    type: 'donut',
                    height: 300,
                    toolbar: { show: false },
                    background: 'transparent',
                    fontFamily: 'inherit',
                },
                series: this.data.values,
                labels: this.data.labels,
                colors: ['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#a855f7'],
                legend: {
                    position: 'bottom',
                    labels: { colors: textColor },
                    fontSize: '13px',
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    color: textColor,
                                    formatter: (w) => {
                                        const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                        return 'RM ' + total.toLocaleString('en-MY', { minimumFractionDigits: 2 })
                                    },
                                },
                            },
                        },
                    },
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val.toFixed(1) + '%',
                    style: { fontSize: '12px' },
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: (val) => 'RM ' + val.toLocaleString('en-MY', { minimumFractionDigits: 2 }),
                    },
                },
            })

            this.chart.render()
        },
    }))

    Alpine.data('trendChart', (chartData) => ({
        chart: null,
        data: chartData,

        renderChart() {
            if (this.chart) this.chart.destroy()

            const isDark = document.documentElement.classList.contains('dark')
                || document.documentElement.getAttribute('data-theme') === 'dark'
            const textColor = isDark ? '#a1a1aa' : '#52525b'
            const gridColor = isDark ? '#27272a' : '#f4f4f5'

            this.chart = new ApexCharts(this.$refs.chart, {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: { show: false },
                    background: 'transparent',
                    fontFamily: 'inherit',
                },
                series: [
                    { name: 'Income',  data: this.data.income,  color: '#22c55e' },
                    { name: 'Expense', data: this.data.expense, color: '#ef4444' },
                ],
                xaxis: {
                    categories: this.data.labels,
                    labels: { style: { colors: textColor, fontSize: '12px' } },
                    axisBorder: { color: gridColor },
                    axisTicks: { color: gridColor },
                },
                yaxis: {
                    labels: {
                        style: { colors: textColor, fontSize: '12px' },
                        formatter: (val) => 'RM ' + Number(val).toLocaleString('en-MY'),
                    },
                },
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                plotOptions: {
                    bar: { borderRadius: 4, columnWidth: '55%' },
                },
                dataLabels: { enabled: false },
                legend: { position: 'top', labels: { colors: textColor } },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: (val) => 'RM ' + Number(val).toLocaleString('en-MY', {
                            minimumFractionDigits: 2,
                        }),
                    },
                },
            })

            this.chart.render()
        },
    }))
</script>
@endscript