@props(['weeklyData'])

<div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm w-full">
    <h3 class="font-semibold text-base mb-4">Weekly Spending</h3>

    @php $max = collect($weeklyData)->max('total') ?: 1; @endphp

    <div class="flex items-end gap-2 h-32">
        @forelse ($weeklyData as $data)
            @php $height = max(5, ($data['total'] / $max) * 100); @endphp
            <div class="flex flex-col items-center flex-1">
                <span class="text-xs text-gray-100 mb-1">RM{{ number_format($data['total'], 0) }}</span>
                <div class="w-full bg-red-400 rounded-t" style="height: {{ $height }}%"></div>
                <span class="text-xs text-gray-200 mt-1">{{ $data['day'] }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center w-full">No data this week.</p>
        @endforelse
    </div>
</div>