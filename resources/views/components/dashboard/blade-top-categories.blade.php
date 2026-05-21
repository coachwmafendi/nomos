@props(['topCategories', 'totalExpense'])

@php
$palette = [
    ['dot' => 'bg-violet-400',  'bar' => 'from-violet-500 to-indigo-500',  'bg' => 'bg-violet-500/10',  'text' => 'text-violet-400'],
    ['dot' => 'bg-emerald-400', 'bar' => 'from-emerald-500 to-teal-500',   'bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400'],
    ['dot' => 'bg-amber-400',   'bar' => 'from-amber-500 to-orange-500',   'bg' => 'bg-amber-500/10',   'text' => 'text-amber-400'],
    ['dot' => 'bg-sky-400',     'bar' => 'from-sky-500 to-cyan-500',       'bg' => 'bg-sky-500/10',     'text' => 'text-sky-400'],
    ['dot' => 'bg-rose-400',    'bar' => 'from-rose-500 to-pink-500',      'bg' => 'bg-rose-500/10',    'text' => 'text-rose-400'],
];
@endphp

<div class="relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 overflow-hidden animate-slide-up"
     style="animation-delay: 0.28s">
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-600/40 to-transparent"></div>

    <div class="flex items-center justify-between mb-5">
        <h3 class="text-sm font-semibold text-white">Top Categories</h3>
        <span class="text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-400">By spend</span>
    </div>

    <div class="space-y-4">
        @forelse ($topCategories as $i => $item)
            @php
                $percent = round(($item->total / ($totalExpense ?: 1)) * 100);
                $color = $palette[$i % count($palette)];
                $delay = 0.3 + ($i * 0.06);
            @endphp
            <div class="animate-slide-up" style="animation-delay: {{ $delay }}s">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-2 h-2 rounded-full {{ $color['dot'] }} shrink-0"></span>
                        <span class="text-sm text-zinc-300 truncate">{{ $item->category?->name ?? 'Uncategorized' }}</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-3">
                        <span class="text-xs {{ $color['text'] }} font-semibold">{{ $percent }}%</span>
                        <span class="text-xs text-zinc-500 tabular-nums">RM {{ number_format($item->total, 2) }}</span>
                    </div>
                </div>
                <div class="w-full bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full bg-gradient-to-r {{ $color['bar'] }} animate-grow-x"
                         style="width: {{ $percent }}%; animation-delay: {{ $delay + 0.1 }}s"></div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-8 gap-2">
                <div class="w-10 h-10 rounded-xl bg-zinc-800 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
                    </svg>
                </div>
                <p class="text-sm text-zinc-400">No category data yet</p>
            </div>
        @endforelse
    </div>
</div>
