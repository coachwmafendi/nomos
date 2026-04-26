@props(['weeklyData'])

@php
 $categories = collect($weeklyData)->pluck('label')->values();
@endphp

<div
    class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm w-full"
    x-data="{
        chart: null,
        init() {
            if (this.chart) return;
            this.renderChart();
        },
        renderChart() {
            if (this.chart) this.chart.destroy();

            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#9ca3af' : '#6b7280';
            const total = @js($weeklyData->count());


            this.chart = new ApexCharts(this.$refs.chart, {
                chart: {
                    type: 'bar',
                    height: 200,
                    toolbar: { show: false },
                    animations: { enabled: true, speed: 400 },
                    background: 'transparent',
                    fontFamily: 'inherit',
                },
                series: [{
                    name: 'Perbelanjaan',
                    data: @js($weeklyData->pluck('total'))
                }],
                xaxis: {
                    categories: @js($categories),
                    labels: {
                        style: {
                            colors: textColor,
                            fontSize: '12px',
                            fontWeight: 500,
                        },
                        rotate: 0,
                        hideOverlappingLabels: false,
                        trim: false,
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: { show: false },
                grid: { show: false },
                dataLabels: { enabled: false },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: total > 20 ? '80%' : '50%',
                    }
                },
                colors: ['#f87171'],
                tooltip: {
                    y: {
                        formatter: (val) => 'RM ' + val.toFixed(2),
                    },
                },
                theme: { mode: isDark ? 'dark' : 'light' },
            });

            this.chart.render();
        }
    }"
    x-init="init()"
>
    <h3 class="font-semibold text-base mb-4">Weekly Spending</h3>

    @if($weeklyData->sum('total') > 0)
        <div x-ref="chart"></div>
    @else
        <div class="flex items-center justify-center h-32">
            <p class="text-sm text-gray-400">Tiada perbelanjaan dalam tempoh ini.</p>
        </div>
    @endif
</div>