<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\Category;
use Flux\Flux;
use App\Actions\CreateTransactionAction;
use App\Actions\UpdateTransactionAction;
use App\Actions\DeleteTransactionAction;
use Illuminate\Support\Facades\Cache;

new class extends Livewire\Component {

    use WithPagination;

    // --- Modal State ---
    public bool $showModal = false;
    public string $mode = 'create'; // 'create' | 'edit'
    public ?int $editId = null;

    //  properties untuk delete confirmation
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;
    public string $deleteDescription = '';

    // --- Form Fields ---
    #[Validate('required|min:2')]
    public string $description = '';

    #[Validate('required|numeric|min:0.01')]
    public string $amount = '';

    #[Validate('required|in:income,expense')]
    public string $type = 'expense';

    #[Validate('nullable|exists:categories,id')]
    public ?int $category_id = null;


    #[Validate('required|date')]
    public string $date = '';

    // --- Filters ---
    public string $search = '';
    public string $filterType = '';
    public string $filterCategory = '';

    // --- Sorting ---
    public string $sortBy = 'date';
    public string $sortDir = 'desc';

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    // ==================
    // SORTING
    // ==================
    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'desc';
        }
    }

    // ==================
    // COMPUTED
    // ==================
    // ✅ Selepas — eager load category
    #[Computed]
    public function transactions()
    {
        return Transaction::query()
            ->with('category')           // <-- tambah ini
            ->when($this->search, fn($q) =>
                $q->where('description', 'like', "%{$this->search}%")
            )
            ->when($this->filterType, fn($q) =>
                $q->where('type', $this->filterType)
            )
            ->when($this->filterCategory, fn($q) =>
                $q->where('category_id', $this->filterCategory)
            )
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(10);
    }

    #[Computed(persist: true)]
    public function totalIncome()
    {
        return Transaction::where('type', 'income')->sum('amount');
    }

    #[Computed(persist: true)]
    public function totalExpense()
    {
        return Transaction::where('type', 'expense')->sum('amount');
    }

    #[Computed(cache: true, seconds: 300)]
    public function summary(): array
    {
        $rows = Transaction::query()
            ->selectRaw("type, SUM(amount) as total")
            ->whereIn('type', ['income', 'expense'])
            ->groupBy('type')
            ->pluck('total', 'type');

        return [
            'income'  => (float) ($rows['income']  ?? 0),
            'expense' => (float) ($rows['expense'] ?? 0),
            'balance' => (float) ($rows['income']  ?? 0) - (float) ($rows['expense'] ?? 0),
        ];
    }

    // #[Computed(persist: true)]
    // public function balance()
    // {
    //     return $this->totalIncome - $this->totalExpense;
    // }

    #[Computed]
    public function isFiltering(): bool
    {
        return filled($this->search)
            || filled($this->filterType)
            || filled($this->filterCategory);
    }
    #[Computed]
    public function categories(): array
    {
        return Category::orderBy('name')
            ->get()
            ->groupBy('type')
            ->map(fn($group) => $group->pluck('name', 'id')->toArray())
            ->toArray();
    }

    // ==================
    // WATCHERS
    // ==================
        
    public function updatedType(): void
    {
        $this->category_id = null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->filterCategory = '';
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    // ==================
    // CREATE
    // ==================
    public function openCreate(): void
    {
        $this->resetForm();
        $this->mode      = 'create';
        $this->showModal = true;
    }

    public function save(CreateTransactionAction $action): void
    {
        $this->validate();

        $action->handle($this->formData());

        unset($this->summary, $this->transactions);

        $this->resetForm();
        
        $this->clearChartCache();


        Flux::toast(
            text: 'Transaction added successfully!',
            variant: 'success',
        );
    }

    private function formData(): array
    {
        return [
            'description' => $this->description,
            'amount'      => $this->amount,
            'type'        => $this->type,
            'category_id' => $this->category_id,  // ← betul
            'date'        => $this->date,
        ];
    }

    // ==================
    // EDIT
    // ==================
    public function openEdit(int $id): void
    {
        $transaction = Transaction::findOrFail($id);

        $this->editId      = $transaction->id;
        $this->description = $transaction->description;
        $this->amount      = (string) $transaction->amount;
        $this->type        = $transaction->type;
        $this->category_id = $transaction->category_id;
        $this->date        = $transaction->date->format('Y-m-d'); // ← Carbon object terus

        $this->resetValidation();
        $this->mode      = 'edit';
        $this->showModal = true;
    }

    public function update(UpdateTransactionAction $action): void
        {
            $this->validate();

            $action->handle($this->editId, $this->formData());

            // unset DULU sebelum resetForm()
            unset($this->summary, $this->transactions);

            $this->resetForm();

            Flux::toast(
                text: 'Transaction updated successfully!',
                variant: 'success',
            );
    }

    // ==================
    // DELETE
    // ==================
    

    // Ganti terus delete button → confirm dulu
    public function confirmDelete(int $id, string $description): void
    {
        $this->deleteId          = $id;
        $this->deleteDescription = $description;
        $this->showDeleteModal   = true;
    }

    public function delete(DeleteTransactionAction $action): void
    {
        if (!$this->deleteId) return;

        $action->handle($this->deleteId);

        // unset($this->totalIncome, $this->totalExpense, $this->transactions);
        unset( $this->summary,  $this->transactions );
    
        $this->deleteId          = null;
        $this->deleteDescription = '';
        $this->showDeleteModal   = false;

        $this->clearChartCache();

        Flux::toast(
            text: 'Transaction deleted!',
            variant: 'danger',
        );
    }

    public function cancelDelete(): void
    {
        $this->deleteId          = null;
        $this->deleteDescription = '';
        $this->showDeleteModal   = false;
    }

    // ==================
    // HELPERS
    // ==================
    public function resetForm(): void
    {
        $this->reset(['description', 'amount', 'category_id', 'editId']);
        $this->date      = now()->format('Y-m-d');
        $this->type      = 'expense';
        $this->mode      = 'create';
        $this->showModal = false;
        $this->resetValidation();
    }

    protected function clearChartCache(): void
    {
        $year = now()->year;

        // Clear semua bulan untuk tahun semasa
        Cache::forget("chart_data_{$year}_");
        for ($m = 1; $m <= 12; $m++) {
            Cache::forget("chart_data_{$year}_{$m}");
        }

        // Clear tahun lain kalau transaction date berbeza tahun
        if ($this->date) {
            $txYear = \Carbon\Carbon::parse($this->date)->year;
            if ($txYear !== $year) {
                Cache::forget("chart_data_{$txYear}_");
                for ($m = 1; $m <= 12; $m++) {
                    Cache::forget("chart_data_{$txYear}_{$m}");
                }
            }
        }
    }   
};
?>

<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    
    <flux:heading size="xl">💰 Nomos</flux:heading>
    
    <flux:button wire:click="openCreate" variant="primary" icon="plus">
        Add Transaction
    </flux:button> 
</div>

    
    {{-- SUMMARY CARDS --}}
    {{-- Tukar dari grid-cols-3 kepada responsive --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">

    {{-- <div class="grid gap-4" style="grid-template-columns: repeat(3, minmax(0, 1fr));"> --}}

    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5">
        <flux:subheading>Total Income</flux:subheading>
        <p class="text-2xl font-bold text-green-500 mt-1">
            RM {{ number_format($this->summary['income'], 2) }}
        </p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5">
        <flux:subheading>Total Expense</flux:subheading>
        <p class="text-2xl font-bold text-red-500 mt-1">
            RM {{ number_format($this->summary['expense'], 2) }}
        </p>
    </div>

    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-gray-100 dark:border-zinc-700 p-5">
        <flux:subheading>Balance</flux:subheading>
        <p class="text-2xl font-bold mt-1 {{ $this->summary['balance'] >= 0 ? 'text-blue-500' : 'text-red-500' }}">
            RM {{ number_format($this->summary['balance'], 2) }}
        </p>
    </div>
</div>

{{-- Chart component --}}
<livewire:transaction-chart />


    {{-- FILTERS --}}
    {{-- Tukar dari grid-cols-3 kepada responsive --}}
<div class="grid gap-4" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="Search transactions..."
        icon="magnifying-glass"
        clearable />

    <flux:select wire:model.live="filterType" placeholder="All Types">
        <flux:select.option value="">All Types</flux:select.option>
        <flux:select.option value="income">Income</flux:select.option>
        <flux:select.option value="expense">Expense</flux:select.option>
    </flux:select>
    <flux:select wire:model.live="filterCategory" placeholder="All Categories">
    <flux:select.option value="">All Categories</flux:select.option>

    @if(!empty($this->categories['income']))
        <optgroup label="── Income ──">
            @foreach($this->categories['income'] as $id => $name)
                <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
            @endforeach
        </optgroup>
    @endif

    @if(!empty($this->categories['expense']))
        <optgroup label="── Expense ──">
            @foreach($this->categories['expense'] as $id => $name)
                <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
            @endforeach
        </optgroup>
    @endif
</flux:select>
</div>

    

     
    {{-- TABLE --}}
    <div class="overflow-x-auto">
    <flux:table :paginate="$this->transactions">
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

            <flux:table.column>Action</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($this->transactions as $transaction)
                <flux:table.row :key="$transaction->id">

                    <flux:table.cell class="text-xs text-gray-400 whitespace-nowrap">
                        {{ $transaction->date->format('d M Y') }}  {{--Carbon object terus
                        {{-- {{ $transaction->date->diffForHumans() }} --}}
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

                    <flux:table.cell align="end"
                        class="font-medium {{ $transaction->type === 'income' ? 'text-green-500' : 'text-red-500' }}">
                        {{ $transaction->type === 'income' ? '+' : '-' }}
                        RM {{ number_format($transaction->amount, 2) }}
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
                            wire:click="confirmDelete({{ $transaction->id }}, '{{ $transaction->description }}')"
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
            <flux:table.cell colspan="6">
                <div class="flex flex-col items-center justify-center py-16 text-center">

                    {{-- Icon --}}
                    <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-zinc-700 flex items-center justify-center mb-4">
                        <flux:icon.banknotes class="w-8 h-8 text-gray-400 dark:text-zinc-500" />
                    </div>

                    {{-- Message --}}
                    <flux:heading size="sm" class="text-gray-500 dark:text-zinc-400 mb-1">
                        No transactions yet
                    </flux:heading>
                    <flux:subheading class="text-gray-400 dark:text-zinc-500 mb-6 max-w-xs">
                        Start tracking your finances by adding your first transaction.
                    </flux:subheading>

                     @if($this->isFiltering)
                    <flux:button
                        wire:click="$set('search', ''); $set('filterType', ''); $set('filterCategory', '')"
                        variant="ghost"
                        icon="x-mark"
                        size="sm">
                        Clear Filters
                    </flux:button>
                @else
                     
                @endif


                    {{-- CTA --}}
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
    

    {{-- MODAL (Create & Edit) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <flux:modal wire:model.self="showModal" class="w-full max-w-md">

            <div class="space-y-6">
                <flux:heading size="lg">
                    {{ $mode === 'create' ? 'Add Transaction' : 'Edit Transaction' }}
                </flux:heading>

                <div class="grid grid-cols-2 gap-4">

                    <div class="col-span-2">
                        <flux:input
                            wire:model="description"
                            label="Description"
                            placeholder="e.g. Zus Coffee" />
                        @error('description')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>

                    <div>
                        <flux:input
                            type="number"
                            wire:model="amount"
                            label="Amount (RM)"
                            placeholder="0.00"
                            step="0.01" />
                        @error('amount')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>

                    <div>
                        <flux:select wire:model.live="type" label="Type">
                            <flux:select.option value="expense">Expense</flux:select.option>
                            <flux:select.option value="income">Income</flux:select.option>
                        </flux:select>
                        @error('type')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>

                    <div>
                      {{-- ✅ Guna @foreach biasa dengan check type --}}
                        <flux:field>
                            <flux:label>Category</flux:label>
                            <flux:select wire:model="category_id">
                                <flux:select.option value="">— No Category —</flux:select.option>

                                @if($type === 'income' || $type === 'both')
                                    <optgroup label="Income">
                                        @foreach($this->categories['income'] ?? [] as $id => $name)
                                            <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                @if($type === 'expense' || $type === 'both')
                                    <optgroup label="Expense">
                                        @foreach($this->categories['expense'] ?? [] as $id => $name)
                                            <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </flux:select>
                            <flux:error name="category_id" />
                        </flux:field>
                    </div>

                    <div>
                        <flux:input
                            type="date"
                            wire:model="date"
                            value="{{ $date }}"
                            label="Date" />
                        @error('date')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>

                </div>

                <div class="flex gap-3 justify-end">
                    <flux:button wire:click="resetForm" variant="ghost">
                        Cancel
                    </flux:button>

                    @if($mode === 'create')
                        <flux:button
                            wire:click="save"
                            variant="primary"
                            wire:loading.attr="disabled"
                            wire:target="save">
                            <span wire:loading.remove wire:target="save">Add Transaction</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </flux:button>
                    @else
                        <flux:button
                            wire:click="update"
                            variant="primary"
                            wire:loading.attr="disabled"
                            wire:target="update">
                            <span wire:loading.remove wire:target="update">Update</span>
                            <span wire:loading wire:target="update">Updating...</span>
                        </flux:button>
                    @endif
                </div>
            </div>

        </flux:modal>
    </div>

    {{-- DELETE CONFIRM MODAL --}}
<flux:modal wire:model.self="showDeleteModal" class="w-full max-w-sm">
    <div class="space-y-6">

        {{-- Icon + Heading --}}
        <div class="flex flex-col items-center text-center gap-3">
            <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <flux:icon.trash class="w-7 h-7 text-red-500" />
            </div>

            <div>
                <flux:heading size="lg">Delete Transaction?</flux:heading>
                <flux:subheading class="mt-1">
                    Are you sure you want to delete
                    <span class="font-semibold text-gray-700 dark:text-zinc-300">
                        "{{ $deleteDescription }}"
                    </span>?
                    This action cannot be undone.
                </flux:subheading>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 justify-center">
            <flux:button
                wire:click="cancelDelete"
                variant="ghost"
                class="flex-1">
                Cancel
            </flux:button>

            <flux:button
                wire:click="delete"
                variant="danger"
                wire:loading.attr="disabled"
                wire:target="delete"
                class="flex-1">
                <span wire:loading.remove wire:target="delete">Delete</span>
                <span wire:loading wire:target="delete">Deleting...</span>
            </flux:button>
        </div>

    </div>
</flux:modal>

</div>
