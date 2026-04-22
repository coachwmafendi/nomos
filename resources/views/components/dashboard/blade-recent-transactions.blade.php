@props(['transactions'])

<div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm w-full">
    <h3 class="font-semibold text-base mb-4">Recent Transactions</h3>

    <div class="divide-y divide-gray-100 dark:divide-zinc-800">
        @forelse ($transactions as $transaction)
            <div class="flex justify-between items-center py-2">
                <div>
                    <p class="text-sm font-medium">{{ $transaction->description }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $transaction->category?->name ?? 'Uncategorized' }}
                        · {{ $transaction->date->format('d M Y') }}
                    </p>
                </div>
                <span class="text-sm font-semibold {{ $transaction->type === 'income' ? 'text-green-500' : 'text-red-500' }}">
                    {{ $transaction->type === 'income' ? '+' : '-' }}RM {{ number_format($transaction->amount, 2) }}
                </span>
            </div>
        @empty
            <p class="text-sm text-gray-400 py-4 text-center">No transactions yet.</p>
        @endforelse
    </div>

    <a href="{{ route('transactions') }}" wire:navigate
       class="block text-center text-xs text-blue-500 mt-4 hover:underline">
        View all →
    </a>
</div>