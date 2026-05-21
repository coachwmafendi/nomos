@props(['comparison', 'sparkline'])

<div
    class="relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 w-full mt-5 overflow-hidden animate-slide-up"
    style="animation-delay: 0.12s"
    x-data="{
        chart: null,
        init() {
            this.$nextTick(() => this.renderChart());
            document.addEventListener('livewire:navigated', () => this.renderChart());
        },
        renderChart() {
            if (this.chart) this.chart.destroy();

            const data = @js($sparkline);
            const lastIndex = data.length - 1;

            const lineColor = @js($comparison['is_up'])
                ? '#f87171'
                : (@js($comparison['is_down']) ? '#34d399' : '#818cf8');

            this.chart = new ApexCharts(this.$refs.sparkline, {
                chart: {
                    type: 'area',
                    height: 40,
                    sparkline: { enabled: true },
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 500 }
                },
                series: [{ name: 'Expense', data: data }],
                colors: [lineColor],
                stroke: { curve: 'smooth', width: 2, lineCap: 'round' },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.18, opacityTo: 0.01, stops: [0, 100] }
                },
                markers: {
                    size: 0,
                    discrete: lastIndex >= 0 ? [{
                        seriesIndex: 0,
                        dataPointIndex: lastIndex,
                        fillColor: lineColor,
                        strokeColor: '#171717',
                        strokeWidth: 2,
                        size: 4,
                        shape: 'circle'
                    }] : []
                },
                tooltip: { enabled: false },
                grid: { padding: { left: 0, right: 0, top: 2, bottom: 0 } }
            });

            this.chart.render();
        }
    }"
    x-init="init()"
>
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-600/40 to-transparent"></div>

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 mb-4">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-400 mb-1">Month over Month</p>
            <h3 class="text-sm font-semibold text-white">Expense Comparison</h3>
        </div>

        {{-- Badge --}}
        <div @class([
            'inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1.5 rounded-full whitespace-nowrap shrink-0',
            'bg-rose-500/10 text-rose-400'     => $comparison['is_up'] && !is_null($comparison['percentage']),
            'bg-emerald-500/10 text-emerald-400' => $comparison['is_down'] && !is_null($comparison['percentage']),
            'bg-violet-500/10 text-violet-400'  => is_null($comparison['percentage']),
            'bg-zinc-800 text-zinc-400'         => !$comparison['is_up'] && !$comparison['is_down'] && !is_null($comparison['percentage']),
        ])>
            @if(is_null($comparison['percentage']))
                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.5a.75.75 0 00-1.5 0v3.25c0 .199.079.39.22.53l2.25 2.25a.75.75 0 101.06-1.06L10.75 9.44V6.5z" clip-rule="evenodd" /></svg>
                First month
            @elseif($comparison['percentage'] == 0)
                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path d="M4 10a.75.75 0 01.75-.75h10.5a.75.75 0 010 1.5H4.75A.75.75 0 014 10z" /></svg>
                No change
            @elseif($comparison['is_up'])
                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3.5a.75.75 0 01.53.22l4 4a.75.75 0 11-1.06 1.06l-2.72-2.72V15a.75.75 0 01-1.5 0V6.06L7.53 8.78a.75.75 0 11-1.06-1.06l4-4A.75.75 0 0110 3.5z" clip-rule="evenodd" /></svg>
                +{{ number_format($comparison['percentage'], 1) }}%
            @else
                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 16.5a.75.75 0 01-.53-.22l-4-4a.75.75 0 111.06-1.06l2.72 2.72V5a.75.75 0 011.5 0v8.94l2.72-2.72a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-.53.22z" clip-rule="evenodd" /></svg>
                {{ number_format($comparison['percentage'], 1) }}%
            @endif
        </div>
    </div>

    {{-- Sparkline --}}
    <div class="mb-4">
        <p class="text-[10px] uppercase tracking-[0.12em] text-zinc-400 mb-1.5">Last 7 days trend</p>
        <div x-ref="sparkline"></div>
    </div>

    {{-- This / Last comparison --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-800 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">This month</p>
            <p class="text-lg font-bold text-white mt-1.5 tabular-nums leading-none">
                <span class="text-xs text-zinc-500 font-semibold">RM</span> {{ number_format($comparison['this_month'], 2) }}
            </p>
        </div>
        <div class="rounded-xl bg-zinc-800/60 border border-zinc-800 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">Last month</p>
            <p class="text-lg font-bold text-white mt-1.5 tabular-nums leading-none">
                <span class="text-xs text-zinc-500 font-semibold">RM</span> {{ number_format($comparison['last_month'], 2) }}
            </p>
        </div>
    </div>

    {{-- Difference --}}
    <div class="flex items-center justify-between mt-4 pt-4 border-t border-zinc-800/60">
        <div>
            <p class="text-[10px] uppercase tracking-[0.12em] text-zinc-400">Difference</p>
            <p @class([
                'text-base font-bold mt-1 tabular-nums',
                'text-rose-400'    => $comparison['is_up'],
                'text-emerald-400' => $comparison['is_down'],
                'text-zinc-300'    => !$comparison['is_up'] && !$comparison['is_down'],
            ])>
                {{ $comparison['difference'] > 0 ? '+' : '' }}RM {{ number_format($comparison['difference'], 2) }}
            </p>
        </div>
        <p class="text-xs text-zinc-400 text-right">
            {{ $comparison['is_up'] ? 'Spending up this month' : ($comparison['is_down'] ? 'Spending down this month' : 'Unchanged') }}
        </p>
    </div>
</div>
