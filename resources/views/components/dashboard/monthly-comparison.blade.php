@props(['comparison', 'sparkline'])

<div
    class="bg-white dark:bg-zinc-900 rounded-2xl p-4 shadow-sm w-full mt-6 border border-gray-100 dark:border-zinc-800"
    x-data="{
        chart: null,
        init() {
            this.renderChart();

            document.addEventListener('livewire:navigated', () => {
                this.renderChart();
            });
        },
        renderChart() {
            if (this.chart) {
                this.chart.destroy();
            }

            const data = @js($sparkline);
            const lastIndex = data.length - 1;
            const isDark = document.documentElement.classList.contains('dark');

            const lineColor = @js($comparison['is_up'])
                ? '#ef4444'
                : (@js($comparison['is_down']) ? '#22c55e' : '#94a3b8');

            this.chart = new ApexCharts(this.$refs.sparkline, {
                chart: {
                    type: 'area',
                    height: 34,
                    sparkline: {
                        enabled: true
                    },
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 350
                    }
                },
                series: [{
                    name: 'Expense',
                    data: data
                }],
                colors: [lineColor],
                stroke: {
                    curve: 'smooth',
                    width: 2.5,
                    lineCap: 'round'
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.14,
                        opacityTo: 0.01,
                        stops: [0, 100]
                    }
                },
                markers: {
                    size: 0,
                    hover: {
                        size: 0
                    },
                    discrete: lastIndex >= 0 ? [{
                        seriesIndex: 0,
                        dataPointIndex: lastIndex,
                        fillColor: lineColor,
                        strokeColor: isDark ? '#18181b' : '#ffffff',
                        strokeWidth: 2,
                        size: 5,
                        shape: 'circle'
                    }] : []
                },
                tooltip: {
                    enabled: false
                },
                grid: {
                    padding: {
                        left: 0,
                        right: 0,
                        top: 0,
                        bottom: 0
                    }
                }
            });

            this.chart.render();
        }
    }"
    x-init="init()"
>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $comparison['label'] }}
            </p>

            <h3 class="text-base font-semibold mt-1 text-gray-900 dark:text-white">
                This Month vs Last Month
            </h3>
        </div>
            <div @class([
            'inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap',
            'bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400' => $comparison['is_up'] && !is_null($comparison['percentage']),
            'bg-green-100 text-green-600 dark:bg-green-500/10 dark:text-green-400' => $comparison['is_down'] && !is_null($comparison['percentage']),
            'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400' => is_null($comparison['percentage']),
            'bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-gray-300' => ! $comparison['is_up'] && ! $comparison['is_down'] && !is_null($comparison['percentage']),
        ])>
            @if(is_null($comparison['percentage']))
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.5a.75.75 0 00-1.5 0v3.25c0 .199.079.39.22.53l2.25 2.25a.75.75 0 101.06-1.06L10.75 9.44V6.5z" clip-rule="evenodd" />
                </svg>
                <span>New</span>
            @elseif($comparison['percentage'] == 0)
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M4 10a.75.75 0 01.75-.75h10.5a.75.75 0 010 1.5H4.75A.75.75 0 014 10z" />
                </svg>
                <span>No change</span>
            @elseif($comparison['is_up'])
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 3.5a.75.75 0 01.53.22l4 4a.75.75 0 11-1.06 1.06l-2.72-2.72V15a.75.75 0 01-1.5 0V6.06L7.53 8.78a.75.75 0 11-1.06-1.06l4-4A.75.75 0 0110 3.5z" clip-rule="evenodd" />
                </svg>
                <span>+{{ number_format($comparison['percentage'], 1) }}%</span>
            @else
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 16.5a.75.75 0 01-.53-.22l-4-4a.75.75 0 111.06-1.06l2.72 2.72V5a.75.75 0 011.5 0v8.94l2.72-2.72a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-.53.22z" clip-rule="evenodd" />
                </svg>
                <span>{{ number_format($comparison['percentage'], 1) }}%</span>
            @endif
        </div>
    </div>

    <div class="mt-3">
        <div class="flex items-center justify-between mb-1.5">
            <p class="text-[11px] uppercase tracking-[0.12em]
             text-gray-400 dark:text-gray-500">
                Last 7 days trend
            </p>

            <p class="text-[11px] text-gray-400 dark:text-gray-500">
                Updated Today
            </p>
        </div>

        <div x-ref="sparkline"></div>
    </div>

    <div class="grid grid-cols-2 gap-3 mt-3">
        <div class="rounded-xl bg-gray-100 dark:bg-zinc-800/80 p-3">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">
                This month
            </p>
        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1 leading-none">                RM {{ number_format($comparison['this_month'], 2) }}
            </p>
        </div>

        <div class="rounded-xl bg-gray-100 dark:bg-zinc-800/80 p-3">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Last month
            </p>
        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1 leading-none">                RM {{ number_format($comparison['last_month'], 2) }}
            </p>
        </div>
    </div>

    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-zinc-800">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-3">
                    Difference
                </p>
                <p @class([
                    'text-base font-semibold mt-0.5',
                    'text-red-600 dark:text-red-400' => $comparison['is_up'],
                    'text-green-600 dark:text-green-400' => $comparison['is_down'],
                    'text-gray-700 dark:text-gray-200' => ! $comparison['is_up'] && ! $comparison['is_down'],
                ])>
                    {{ $comparison['difference'] > 0 ? '+' : '' }}RM {{ number_format($comparison['difference'], 2) }}
                </p>
            </div>

            <div class="text-right">
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    {{ $comparison['is_up'] ? 'Spending increased' : ($comparison['is_down'] ? 'Spending decreased' : 'Spending unchanged') }}
                </p>
            </div>
        </div>
    </div>
</div>