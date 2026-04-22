<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

new class extends Component {

    #[Computed]
    public function weeklyData()
    {
        return Transaction::where('type', 'expense')
            ->where('date', '>=', Carbon::now()->subDays(6))
            ->selectRaw('DATE(date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn($item) => [
                'day'   => Carbon::parse($item->day)->format('D'),
                'total' => (float) $item->total,
            ]);
    }
};
?>

<div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm w-full">
    <h3 class="font-semibold text-base mb-4">Weekly Spending</h3>

    <div class="flex items-end gap-2 h-32">
        @foreach ($this->weeklyData as $data)
            @php $height = $data['total'] > 0 ? max(10, ($data['total'] / ($this->weeklyData->max('total') ?: 1)) * 100) : 5; @endphp
            <div class="flex flex-col items-center flex-1">
                <span class="text-xs text-gray-400 mb-1">RM{{ number_format($data['total'], 0) }}</span>
                <div class="w-full bg-red-400 rounded-t" style="height: {{ $height }}%"></div>
                <span class="text-xs text-gray-500 mt-1">{{ $data['day'] }}</span>
            </div>
        @endforeach

        @if($this->weeklyData->isEmpty())
            <p class="text-sm text-gray-400 text-center w-full">No data this week.</p>
        @endif
    </div>
</div>