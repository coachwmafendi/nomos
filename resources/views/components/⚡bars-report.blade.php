<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            1  => 'January',
            2  => 'February',
            3  => 'March',
            4  => 'April',
            5  => 'May',
            6  => 'June',
            7  => 'July',
            8  => 'August',
            9  => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    protected function baseQuery()
    {
        return Transaction::query()
            ->when($this->selectedYear, fn ($q) =>
                $q->whereRaw("strftime('%Y', date) = ?", [(string) $this->selectedYear])
            )
            ->when($this->selectedMonth !== '', fn ($q) =>
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

        $income = (float) ($rows['income'] ?? 0);
        $expense = (float) ($rows['expense'] ?? 0);

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }

    #[Computed]
    public function categoryBreakdown()
    {
        return $this->baseQuery()
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.type', 'expense')
            ->selectRaw("
                COALESCE(categories.name, 'Uncategorized') as category_name,
                SUM(transactions.amount) as total
            ")
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->get();
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

        $income = array_fill(1, 12, 0);
        $expense = array_fill(1, 12, 0);

        foreach ($rows as $row) {
            if ($row->type === 'income') {
                $income[(int) $row->month] = (float) $row->total;
            } else {
                $expense[(int) $row->month] = (float) $row->total;
            }
        }

        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'income' => array_values($income),
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
};
?>

<div class="p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Reports</flux:heading>
            <flux:subheading>{{ $this->reportTitle }}</flux:subheading>
        </div>

        <div class="grid gap-3" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
            <flux:select wire:model.live="selectedMonth">
                <flux:select.option value="">All Months</flux:select.option>
                @foreach($this->months as $num => $name)
                    <flux:select.option value="{{ $num }}">{{ $name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="selectedYear">
                @foreach($this->availableYears as $year)
                    <flux:select.option value="{{ $year }}">{{ $year }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <div class="grid gap-4" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
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

    {{-- Report Card Summary --}}
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5 space-y-4">
            <div>
                <flux:heading size="md">Expense by Category</flux:heading>
                <flux:subheading>Breakdown for selected period</flux:subheading>
            </div>
            <div>


    

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Category</flux:table.column>
                    <flux:table.column align="end">Total</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($this->categoryBreakdown as $row)
                        <flux:table.row :key="$row->category_name">
                            <flux:table.cell>{{ $row->category_name }}</flux:table.cell>
                            <flux:table.cell align="end">RM {{ number_format($row->total, 2) }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="2">
                                <div class="py-8 text-center text-gray-500 dark:text-zinc-400">
                                    No expense data for this period.
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5 space-y-4">
            <div>
                <flux:heading size="md">Monthly Trend</flux:heading>
                <flux:subheading>Income vs expense for {{ $selectedYear }}</flux:subheading>
            </div>

            <div
                wire:key="reports-chart-{{ $selectedYear }}"
                x-data="reportsChart(@js($this->monthlyTrend))"
                x-init="renderChart()"
            >
                <div x-ref="chart"></div>
            </div>
        </div>
    </div>

    {{-- Chart component --}}
    <livewire:transaction-chart />

    </div>
</div>

@script
<script>
    Alpine.data('reportsChart', (chartData) => ({
        chart: null,
        data: chartData,

        renderChart() {
            if (this.chart) this.chart.destroy()

            const isDark =
                document.documentElement.classList.contains('dark') ||
                document.documentElement.getAttribute('data-theme') === 'dark'

            const textColor = isDark ? '#a1a1aa' : '#52525b'
            const gridColor = isDark ? '#27272a' : '#f4f4f5'

            this.chart = new ApexCharts(this.$refs.chart, {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false },
                    background: 'transparent',
                    fontFamily: 'inherit',
                },
                series: [
                    { name: 'Income', data: this.data.income, color: '#22c55e' },
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
                        formatter: (val) => 'RM ' + Number(val).toLocaleString('en-MY'),
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
