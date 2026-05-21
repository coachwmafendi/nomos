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
            ->selectRaw(\App\Support\DateExpression::year() . ' as year')
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
                $q->whereRaw(\App\Support\DateExpression::year() . ' = ?', [(string) $this->selectedYear])
            )
            ->when($this->selectedMonth !== '', fn ($q) =>
                $q->whereRaw(\App\Support\DateExpression::month() . ' = ?', [str_pad($this->selectedMonth, 2, '0', STR_PAD_LEFT)])
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
                " . \App\Support\DateExpression::month() . " as month,
                SUM(amount) as total
            ")
            ->whereRaw(\App\Support\DateExpression::year() . ' = ?', [(string) $this->selectedYear])
            ->whereIn('type', ['income', 'expense'])
            ->groupBy('type', DB::raw(\App\Support\DateExpression::month()))
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

    {{-- Page Header --}}
    <div class="flex items-start justify-between flex-wrap gap-4 mb-7 animate-slide-up" style="animation-delay: 0s">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-400 mb-1">Analytics</p>
            <h1 class="text-xl font-bold text-white">Reports</h1>
            <p class="text-xs text-zinc-500 mt-1">{{ $this->reportTitle }}</p>
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

    {{-- Summary Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        {{-- Income --}}
        <div class="group relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 overflow-hidden animate-slide-up hover:border-zinc-700 transition-colors duration-200" style="animation-delay: 0.04s">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-emerald-500/60 to-transparent"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                    </svg>
                </div>
                <span class="text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-400">Income</span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums leading-none">
                <span class="text-sm font-semibold text-zinc-400 mr-0.5">RM</span>{{ number_format($this->summary['income'], 2) }}
            </p>
            <p class="text-xs text-zinc-400 mt-2">Selected period</p>
        </div>

        {{-- Expense --}}
        <div class="group relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 overflow-hidden animate-slide-up hover:border-zinc-700 transition-colors duration-200" style="animation-delay: 0.08s">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-rose-500/60 to-transparent"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-rose-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.306-4.307a11.95 11.95 0 015.814 5.519l2.74 1.22m0 0l-5.94 2.28m5.94-2.28l-2.28-5.941" />
                    </svg>
                </div>
                <span class="text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-400">Expense</span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums leading-none">
                <span class="text-sm font-semibold text-zinc-400 mr-0.5">RM</span>{{ number_format($this->summary['expense'], 2) }}
            </p>
            <p class="text-xs text-zinc-400 mt-2">Selected period</p>
        </div>

        {{-- Balance --}}
        <div class="group relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 overflow-hidden animate-slide-up hover:border-zinc-700 transition-colors duration-200" style="animation-delay: 0.12s">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-violet-500/60 to-transparent"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-violet-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                    </svg>
                </div>
                <span class="text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-400">Balance</span>
            </div>
            <p class="text-2xl font-bold tabular-nums leading-none {{ $this->summary['balance'] >= 0 ? 'text-white' : 'text-rose-400' }}">
                <span class="text-sm font-semibold text-zinc-400 mr-0.5">RM</span>{{ number_format($this->summary['balance'], 2) }}
            </p>
            <p class="text-xs text-zinc-400 mt-2">Selected period</p>
        </div>
    </div>

    {{-- Report Card Summary --}}
    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Expense by Category Table --}}
        <div class="relative bg-zinc-900 border border-zinc-800/80 rounded-2xl overflow-hidden animate-slide-up" style="animation-delay: 0.16s">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-600/40 to-transparent"></div>
            <div class="px-5 pt-5 pb-4">
                <h3 class="text-sm font-semibold text-white">Expense by Category</h3>
                <p class="text-[11px] text-zinc-500 mt-0.5">Breakdown for selected period</p>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">Category</flux:table.column>
                    <flux:table.column align="end" class="text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">Total</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($this->categoryBreakdown as $row)
                        <flux:table.row :key="$row->category_name" class="hover:bg-zinc-800/30 transition-colors">
                            <flux:table.cell>
                                <span class="bg-zinc-800 text-zinc-400 text-xs px-2 py-0.5 rounded-full">{{ $row->category_name }}</span>
                            </flux:table.cell>
                            <flux:table.cell align="end" class="text-rose-400">RM {{ number_format($row->total, 2) }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="2">
                                <div class="flex flex-col items-center justify-center py-16 gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-zinc-800 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776" />
                                        </svg>
                                    </div>
                                    <p class="text-sm text-zinc-500">No data for this period</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        {{-- Monthly Trend Chart --}}
        <div class="relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 overflow-hidden animate-slide-up" style="animation-delay: 0.2s">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-600/40 to-transparent"></div>
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-sm font-semibold text-white">Monthly Trend</h3>
                    <p class="text-[11px] text-zinc-500 mt-0.5">Income vs expense for {{ $selectedYear }}</p>
                </div>
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
