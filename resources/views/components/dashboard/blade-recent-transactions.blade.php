@props(['transactions'])

<div class="relative bg-zinc-900 border border-zinc-800/80 rounded-2xl overflow-hidden animate-slide-up"
     style="animation-delay: 0.36s">
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-600/40 to-transparent"></div>

    <div class="flex items-center justify-between px-5 pt-5 pb-4">
        <h3 class="text-sm font-semibold text-white">Recent Transactions</h3>
        <a href="{{ route('transactions') }}" wire:navigate
           class="text-[11px] font-semibold text-violet-400 hover:text-violet-300 transition-colors flex items-center gap-1">
            View all
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </a>
    </div>

    @forelse ($transactions as $i => $transaction)
        @php
            $isIncome = $transaction->type === 'income';
            $initial = strtoupper(mb_substr($transaction->description, 0, 1));
            $delay = 0.38 + ($i * 0.05);
            $catName = $transaction->category?->name ?? 'Uncategorized';
        @endphp
        <div class="flex items-center gap-4 px-5 py-3.5 border-t border-zinc-800/60 hover:bg-zinc-800/30 transition-colors duration-150 animate-slide-up"
             style="animation-delay: {{ $delay }}s">

            {{-- Avatar --}}
            <div class="w-9 h-9 rounded-xl {{ $isIncome ? 'bg-emerald-500/10' : 'bg-rose-500/10' }} flex items-center justify-center shrink-0">
                <span class="text-xs font-bold {{ $isIncome ? 'text-emerald-400' : 'text-rose-400' }}">{{ $initial }}</span>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-zinc-200 truncate">{{ $transaction->description }}</p>
                <p class="text-xs text-zinc-500 mt-0.5 flex items-center gap-1.5">
                    <span>{{ $catName }}</span>
                    <span class="w-1 h-1 rounded-full bg-zinc-700 inline-block"></span>
                    <span>{{ $transaction->date->format('d M Y') }}</span>
                </p>
            </div>

            {{-- Amount --}}
            <span class="text-sm font-semibold tabular-nums shrink-0 {{ $isIncome ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ $isIncome ? '+' : '−' }}RM {{ number_format($transaction->amount, 2) }}
            </span>
        </div>
    @empty
        <div class="flex flex-col items-center justify-center py-12 gap-2 border-t border-zinc-800/60">
            <div class="w-10 h-10 rounded-xl bg-zinc-800 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" />
                </svg>
            </div>
            <p class="text-sm text-zinc-400">No transactions yet</p>
        </div>
    @endforelse
</div>
