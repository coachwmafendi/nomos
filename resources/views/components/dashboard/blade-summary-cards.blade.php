@props(['totalIncome', 'totalExpense', 'balance'])

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">

    {{-- Income --}}
    <div class="group relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 overflow-hidden animate-slide-up hover:border-zinc-700 transition-colors duration-200"
         style="animation-delay: 0s">
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
            <span class="text-sm font-semibold text-zinc-400 mr-0.5">RM</span>{{ number_format($totalIncome, 2) }}
        </p>
        <p class="text-xs text-zinc-400 mt-2">Selected period</p>
    </div>

    {{-- Expense --}}
    <div class="group relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 overflow-hidden animate-slide-up hover:border-zinc-700 transition-colors duration-200"
         style="animation-delay: 0.08s">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-rose-500/60 to-transparent"></div>
        <div class="flex items-center justify-between mb-4">
            <div class="w-9 h-9 rounded-xl bg-rose-500/10 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.306-4.307a11.95 11.95 0 015.814 5.519l2.74 1.22m0 0l-5.94 2.28m5.94-2.28l-2.28-5.941" />
                </svg>
            </div>
            <span class="text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-400">Expenses</span>
        </div>
        <p class="text-2xl font-bold text-white tabular-nums leading-none">
            <span class="text-sm font-semibold text-zinc-400 mr-0.5">RM</span>{{ number_format($totalExpense, 2) }}
        </p>
        <p class="text-xs text-zinc-400 mt-2">Selected period</p>
    </div>

    {{-- Balance --}}
    @php $balancePositive = $balance >= 0; @endphp
    <div class="group relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 overflow-hidden animate-slide-up hover:border-zinc-700 transition-colors duration-200"
         style="animation-delay: 0.16s">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent {{ $balancePositive ? 'via-violet-500/60' : 'via-amber-500/60' }} to-transparent"></div>
        <div class="flex items-center justify-between mb-4">
            <div class="w-9 h-9 rounded-xl {{ $balancePositive ? 'bg-violet-500/10' : 'bg-amber-500/10' }} flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 {{ $balancePositive ? 'text-violet-400' : 'text-amber-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                </svg>
            </div>
            <span class="text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-400">Net Balance</span>
        </div>
        <p class="text-2xl font-bold {{ $balancePositive ? 'text-white' : 'text-amber-400' }} tabular-nums leading-none">
            <span class="text-sm font-semibold text-zinc-400 mr-0.5">RM</span>{{ number_format(abs($balance), 2) }}
        </p>
        <p class="text-xs text-zinc-400 mt-2">{{ $balancePositive ? 'Surplus' : 'Deficit' }} this period</p>
    </div>

</div>
