@props(['topCategories', 'totalExpense'])

<div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm w-full">
    <h3 class="font-semibold text-base mb-4">Top Categories</h3>

    <div class="space-y-3">
        @forelse ($topCategories as $item)
            @php $percent = round(($item->total / ($totalExpense ?: 1)) * 100); @endphp
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span>{{ $item->category?->name ?? 'Uncategorized' }}</span>
                    <span class="font-medium">RM {{ number_format($item->total, 2) }} ({{ $percent }}%)</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-4">No data yet.</p>
        @endforelse
    </div>
</div>