@props([
    'transactions',
    'sortBy',
    'sortDir',
    'isFiltering' => false,
])

<div class="overflow-x-auto">
    <flux:table :paginate="$transactions">
        <flux:table.columns>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'date'"
                :direction="$sortDir"
                wire:click="sort('date')">
                Date
            </flux:table.column>

            <flux:table.column>Description</flux:table.column>

            <flux:table.column
                sortable
                :sorted="$sortBy === 'category'"
                :direction="$sortDir"
                wire:click="sort('category')">
                Category
            </flux:table.column>

            <flux:table.column>Type</flux:table.column>

            <flux:table.column
                sortable
                :sorted="$sortBy === 'amount'"
                :direction="$sortDir"
                wire:click="sort('amount')"
                align="end">
                Amount
            </flux:table.column>

            <flux:table.column>Attachment</flux:table.column>
            <flux:table.column>Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($transactions as $transaction)
                <flux:table.row :key="$transaction->id">
                    <flux:table.cell class="text-xs text-gray-400 whitespace-nowrap">
                        {{ $transaction->date->format('d M Y') }}
                    </flux:table.cell>

                    <flux:table.cell variant="strong">
                        {{ $transaction->description }}
                    </flux:table.cell>

                    <flux:table.cell>
                        {{ $transaction->category?->name ?? '—' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($transaction->type === 'income')
                            <flux:badge color="green" size="sm" inset="top bottom">Income</flux:badge>
                        @else
                            <flux:badge color="red" size="sm" inset="top bottom">Expense</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell
                        align="end"
                        class="font-medium {{ $transaction->type === 'income' ? 'text-green-500' : 'text-red-500' }}">
                        {{ $transaction->type === 'income' ? '+' : '-' }}
                        RM {{ number_format($transaction->amount, 2) }}
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($transaction->latestAttachment)
                            <a
                                href="{{ $transaction->latestAttachment->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm text-blue-500 hover:underline"
                            >
                                View
                            </a>
                        @else
                            <span class="text-sm text-gray-400">—</span>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button
                                wire:click="openEdit({{ $transaction->id }})"
                                variant="ghost"
                                size="sm"
                                icon="pencil"
                                inset="top bottom" />

                            <flux:button
                                wire:click="confirmDelete({{ $transaction->id }}, @js($transaction->description))"
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                inset="top bottom"
                                class="text-red-400 hover:text-red-600" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-zinc-700 flex items-center justify-center mb-4">
                                <flux:icon.banknotes class="w-8 h-8 text-gray-400 dark:text-zinc-500" />
                            </div>

                            <flux:heading size="sm" class="text-gray-500 dark:text-zinc-400 mb-1">
                                No transactions yet
                            </flux:heading>

                            <flux:subheading class="text-gray-400 dark:text-zinc-500 mb-6 max-w-xs">
                                Start tracking your finances by adding your first transaction.
                            </flux:subheading>

                            @if($isFiltering)
                                <flux:button
                                    wire:click="$set('search', ''); $set('filterType', ''); $set('filterCategory', '')"
                                    variant="ghost"
                                    icon="x-mark"
                                    size="sm">
                                    Clear Filters
                                </flux:button>
                            @endif

                            <flux:button wire:click="openCreate" variant="primary" icon="plus" size="sm">
                                Add First Transaction
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>