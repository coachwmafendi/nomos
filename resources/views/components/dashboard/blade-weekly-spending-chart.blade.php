@props(['weeklyData'])

@php
 $categories = collect($weeklyData)->pluck('label')->values();
 $total = $weeklyData->count();
 $hasData = $weeklyData->sum('total') > 0;
@endphp

<div
    class="relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 overflow-hidden animate-slide-up"
    style="animation-delay: 0.2s"
    x-data="{
        chart: null,
        init() {
            if (this.chart) return;
            this.$nextTick(() => this.renderChart());
        },
        renderChart() {
            if (this.chart) this.chart.destroy();

            const isDark = document.documentElement.classList.contains('dark');
            const textColor = '#52525b'; {{-- zinc-600 --}}
            const total = @js($total);

            this.chart = new ApexCharts(this.$refs.chart, {
                chart: {
                    type: 'bar',
                    height: 190,
                    toolbar: { show: false },
                    animations: {
                        enabled: true,
                        speed: 600,
                        animateGradually: { enabled: true, delay: 60 },
                        dynamicAnimation: { enabled: true, speed: 350 }
                    },
                    background: 'transparent',
                    fontFamily: 'inherit',
                    sparkline: { enabled: false },
                },
                series: [{
                    name: 'Spending',
                    data: @js($weeklyData->pluck('total'))
                }],
                xaxis: {
                    categories: @js($categories),
                    labels: {
                        style: { colors: textColor, fontSize: '11px', fontWeight: 500 },
                        rotate: 0,
                        hideOverlappingLabels: false,
                        trim: false,
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: { show: false },
                grid: { show: false, padding: { left: -10, right: -10 } },
                dataLabels: { enabled: false },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: total > 20 ? '85%' : '45%',
                        distributed: false,
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'vertical',
                        gradientToColors: ['#6366f1'],
                        stops: [0, 100],
                    }
                },
                colors: ['#818cf8'],
                tooltip: {
                    theme: 'dark',
                    y: { formatter: (val) => 'RM ' + val.toFixed(2) },
                    style: { fontSize: '12px' },
                },
                states: {
                    hover: { filter: { type: 'lighten', value: 0.1 } }
                },
                theme: { mode: 'dark' },
            });

            this.chart.render();
        }
    }"
    x-init="init()"
>
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-600/40 to-transparent"></div>

    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-sm font-semibold text-white">Daily Spending</h3>
            <p class="text-[11px] text-zinc-500 mt-0.5">Last 7 days</p>
        </div>
        @if($hasData)
            <div class="w-7 h-7 rounded-lg bg-violet-500/10 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            </div>
        @endif
    </div>

    @if($hasData)
        <div x-ref="chart" class="-mx-2"></div>
    @else
        <div class="flex flex-col items-center justify-center h-[190px] gap-2">
            <div class="w-10 h-10 rounded-xl bg-zinc-800 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            </div>
            <p class="text-sm text-zinc-400">No spending this week</p>
        </div>
    @endif
</div>
