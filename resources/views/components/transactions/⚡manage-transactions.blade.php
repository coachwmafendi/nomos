<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Transaction;
use App\Models\Category;
use Flux\Flux;
use App\Actions\CreateTransactionAction;
use App\Actions\UpdateTransactionAction;
use App\Actions\DeleteTransactionAction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

new #[Title('Manage Your Transactions')] class extends Component {
    use WithPagination;
    use WithFileUploads;

    public bool $showModal = false;
    public string $mode = 'create';
    public ?int $editId = null;

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;
    public string $deleteDescription = '';

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

    #[Validate('nullable|file|mimes:jpg,jpeg,png,pdf|max:5120')]
    public $attachment = null;

    public string $search = '';
    public string $filterType = '';
    public string $filterCategory = '';

    public string $sortBy = 'date';
    public string $sortDir = 'desc';

    public string $dateFrom = '';
    public string $dateTo = '';

    public bool $removeExistingAttachment = false;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    // ─── Computed Properties ──────────────────────────────────────────

    #[Computed]
    public function currentAttachment()
    {
        if (! $this->editId) {
            return null;
        }

        $transaction = Transaction::query()
            ->with('attachments')
            ->where('user_id', auth()->id())
            ->find($this->editId);

        return $transaction?->attachments->first();
    }

    #[Computed]
    public function transactions()
    {
        $query = Transaction::query()
            ->with(['category', 'attachments'])
            ->where('user_id', auth()->id())
            ->when($this->search, fn($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            // FIX: dateFrom & dateTo sekarang dipakai dalam query
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('date', '<=', $this->dateTo));

        if ($this->sortBy === 'date') {
            $query->orderBy('date', $this->sortDir)
                ->orderBy('updated_at', $this->sortDir)
                ->orderBy('id', $this->sortDir);
        } else {
            $query->orderBy($this->sortBy, $this->sortDir)
                ->orderBy('id', 'desc');
        }

        return $query->paginate(10);
    }

    #[Computed(cache: true, seconds: 300)]
    public function summary(): array
    {
        $rows = Transaction::query()
            ->where('user_id', auth()->id())
            ->selectRaw('type, SUM(amount) as total')
            ->whereIn('type', ['income', 'expense'])
            ->groupBy('type')
            ->pluck('total', 'type');

        return [
            'income'  => (float) ($rows['income'] ?? 0),
            'expense' => (float) ($rows['expense'] ?? 0),
            'balance' => (float) ($rows['income'] ?? 0) - (float) ($rows['expense'] ?? 0),
        ];
    }

    #[Computed]
    public function income()
    {
        return $this->summary['income'];
    }

    #[Computed]
    public function expenses()
    {
        return $this->summary['expense'];
    }

    #[Computed]
    public function isFiltering(): bool
    {
        return filled($this->search)
            || filled($this->filterType)
            || filled($this->filterCategory)
            || filled($this->dateFrom)
            || filled($this->dateTo);
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

    // ─── Updated Hooks ────────────────────────────────────────────────

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

    // FIX: dateFrom & dateTo reset pagination bila berubah
    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    // ─── Sorting ──────────────────────────────────────────────────────

    // FIX: Method sort() untuk dipakai dari transactions-table component
    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    // ─── Modal ────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetForm();
        $this->mode = 'create';
        $this->showModal = true;
    }

    private function formData(): array
    {
        return [
            'user_id'     => auth()->id(),
            'description' => $this->description,
            'amount'      => $this->amount,
            'type'        => $this->type,
            'category_id' => $this->category_id,
            'date'        => $this->date,
        ];
    }

    protected function clearChartCache(): void
    {
        $year = now()->year;

        Cache::forget("chart_data_{$year}_");
        for ($m = 1; $m <= 12; $m++) {
            Cache::forget("chart_data_{$year}_{$m}");
        }

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

    // ─── CRUD ─────────────────────────────────────────────────────────

    public function save(CreateTransactionAction $action): void
    {
        $this->validate();

        $transaction = $action->handle($this->formData());

        if ($this->attachment) {
            $path = $this->attachment->store('transactions/attachments', 'public');

            $transaction->attachments()->create([
                'original_name' => $this->attachment->getClientOriginalName(),
                'stored_name'   => basename($path),
                'path'          => $path,
                'disk'          => 'public',
                'mime_type'     => $this->attachment->getMimeType(),
                'size'          => $this->attachment->getSize(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        unset($this->summary, $this->transactions, $this->income, $this->expenses);

        $this->resetForm();
        $this->clearChartCache();
        $this->dispatch('transaction-updated');

        Flux::toast(text: 'Transaction added successfully!', variant: 'success');
    }

    public function openEdit(int $id): void
    {
        $transaction = Transaction::query()
            ->with('attachments')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $this->editId          = $transaction->id;
        $this->description     = $transaction->description;
        $this->amount          = (string) $transaction->amount;
        $this->type            = $transaction->type;
        $this->category_id     = $transaction->category_id;
        $this->date            = $transaction->date->format('Y-m-d');

        $this->attachment                = null;
        $this->removeExistingAttachment  = false;

        $this->resetValidation();
        $this->mode      = 'edit';
        $this->showModal = true;
    }

    public function update(UpdateTransactionAction $action): void
    {
        $this->validate();

        $transaction = $action->handle($this->editId, $this->formData());
        $transaction->load('attachments');

        if ($this->removeExistingAttachment) {
            foreach ($transaction->attachments as $oldAttachment) {
                Storage::disk($oldAttachment->disk)->delete($oldAttachment->path);
                $oldAttachment->delete();
            }
            $transaction->load('attachments');
        }

        if ($this->attachment) {
            foreach ($transaction->attachments as $oldAttachment) {
                Storage::disk($oldAttachment->disk)->delete($oldAttachment->path);
                $oldAttachment->delete();
            }

            $path = $this->attachment->store('transactions/attachments', 'public');

            $transaction->attachments()->create([
                'original_name' => $this->attachment->getClientOriginalName(),
                'stored_name'   => basename($path),
                'path'          => $path,
                'disk'          => 'public',
                'mime_type'     => $this->attachment->getMimeType(),
                'size'          => $this->attachment->getSize(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        unset($this->summary, $this->transactions, $this->income, $this->expenses);

        $this->resetForm();
        $this->clearChartCache();
        $this->dispatch('transaction-updated');

        Flux::toast(text: 'Transaction updated successfully!', variant: 'success');
    }

    public function confirmDelete(int $id, string $description): void
    {
        $this->deleteId          = $id;
        $this->deleteDescription = $description;
        $this->showDeleteModal   = true;
    }

    public function delete(DeleteTransactionAction $action): void
    {
        if (! $this->deleteId) {
            return;
        }

        $action->handle($this->deleteId);

        unset($this->summary, $this->transactions, $this->income, $this->expenses);

        $this->deleteId          = null;
        $this->deleteDescription = '';
        $this->showDeleteModal   = false;

        $this->clearChartCache();
        $this->dispatch('transaction-updated');

        Flux::toast(text: 'Transaction deleted!', variant: 'danger');
    }

    public function cancelDelete(): void
    {
        $this->deleteId          = null;
        $this->deleteDescription = '';
        $this->showDeleteModal   = false;
    }

    // ─── Attachment Helpers ───────────────────────────────────────────

    // FIX: Method ini wujud tapi tiada dalam code asal
    public function removeSelectedAttachment(): void
    {
        $this->attachment = null;
    }

    // FIX: Method ini wujud tapi tiada dalam code asal
    public function removeCurrentAttachment(): void
    {
        $this->removeExistingAttachment = true;
    }

    // ─── Export ───────────────────────────────────────────────────────

    // FIX: Method exportCsv() yang dipanggil dalam Blade
    public function exportCsv(): StreamedResponse
    {
        $transactions = Transaction::query()
            ->with('category')
            ->where('user_id', auth()->id())
            ->when($this->search, fn($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->orderBy('date', 'desc')
            ->get();

        $filename = 'transactions-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Description', 'Type', 'Category', 'Amount (RM)']);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->date->format('Y-m-d'),
                    $tx->description,
                    $tx->type,
                    $tx->category?->name ?? '—',
                    number_format($tx->amount, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // ─── Reset Form ───────────────────────────────────────────────────

    public function resetForm(): void
    {
        $this->reset([
            'description',
            'amount',
            'category_id',
            'editId',
            'attachment',
            'removeExistingAttachment',
        ]);

        $this->date      = now()->format('Y-m-d');
        $this->type      = 'expense';
        $this->mode      = 'create';
        $this->showModal = false;
        $this->resetValidation();
    }
};
?>

<div class="p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
            <h2 class="text-lg font-semibold">Transactions</h2>

            <div class="flex items-center gap-3">
                <flux:input type="date" wire:model.live="dateFrom" label="From" />
                <span class="text-gray-400 mt-5">→</span>
                <flux:input type="date" wire:model.live="dateTo" label="To" />

                <flux:button wire:click="exportCsv" icon="arrow-down-tray" variant="ghost">
                    Export CSV
                </flux:button>

                <flux:button wire:click="openCreate()" icon="plus" variant="primary">
                    Add Transaction
                </flux:button>
            </div>
        </div>
    </div>

    @island('summary-card', always:true)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
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
    @endisland

    <livewire:transaction-chart
        :income="$this->income"
        :expenses="$this->expenses"
        :key="'transaction-chart-'.$this->income.'-'.$this->expenses"
    />

    <div class="grid gap-4" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Search transactions..."
            icon="magnifying-glass"
            clearable
        />

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

    <x-transactions.transactions-table
        :transactions="$this->transactions"
        :sort-by="$sortBy"
        :sort-dir="$sortDir"
        :is-filtering="$this->isFiltering"
    />

    {{-- ─── Create / Edit Modal ─── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <flux:modal wire:model.self="showModal" class="w-full max-w-max">
            <div class="space-y-6">
                <flux:heading size="lg">
                    {{ $mode === 'create' ? 'Add Transaction' : 'Edit Transaction' }}
                </flux:heading>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <flux:input
                            wire:model="description"
                            label="Description"
                            placeholder="e.g. Zus Coffee"
                        />
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
                            step="0.01"
                        />
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
                            label="Date"
                        />
                        @error('date')
                            <flux:error>{{ $message }}</flux:error>
                        @enderror
                    </div>

                    <div class="col-span-2 space-y-3">
                        <flux:field>
                            <flux:label>
                                {{ $type === 'expense' ? 'Receipt' : 'Invoice / Proof' }}
                                <span class="text-zinc-400">(Optional)</span>
                            </flux:label>

                            <div
                                x-data="{ dragging: false }"
                                x-on:dragenter.prevent="dragging = true"
                                x-on:dragover.prevent="dragging = true"
                                x-on:dragleave.prevent="dragging = false"
                                x-on:drop.prevent="
                                    dragging = false;
                                    const files = $event.dataTransfer.files;
                                    if (files.length) {
                                        $refs.attachment.files = files;
                                        $refs.attachment.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                "
                                class="space-y-3"
                            >
                                <div
                                    x-on:click="$refs.attachment.click()"
                                    x-on:keydown.enter.prevent="$refs.attachment.click()"
                                    x-on:keydown.space.prevent="$refs.attachment.click()"
                                    tabindex="0"
                                    role="button"
                                    class="flex min-h-36 cursor-pointer items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-6 text-center transition hover:border-zinc-400 hover:bg-zinc-100/70 dark:border-zinc-700 dark:bg-zinc-900/40 dark:hover:border-zinc-600 dark:hover:bg-zinc-900"
                                    :class="dragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10' : ''"
                                >
                                    <div class="space-y-1">
                                        <div class="text-sm font-medium text-gray-500 dark:text-zinc-400">
                                            Drop file here or click to browse
                                        </div>
                                        <div class="text-sm font-medium text-gray-400 dark:text-zinc-400">
                                            JPG, PNG, or PDF up to 5MB
                                        </div>
                                    </div>
                                </div>

                                <input
                                    x-ref="attachment"
                                    type="file"
                                    wire:model="attachment"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="hidden"
                                />
                            </div>

                            @error('attachment')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        <div wire:loading wire:target="attachment" class="text-sm text-zinc-500">
                            Uploading file...
                        </div>

                        @if ($attachment)
                            <flux:callout variant="secondary" icon="paper-clip">
                                <flux:callout.heading>
                                    Selected: {{ $attachment->getClientOriginalName() }}
                                </flux:callout.heading>

                                <x-slot name="actions">
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        wire:click="removeSelectedAttachment"
                                    >
                                        Remove file
                                    </flux:button>
                                </x-slot>
                            </flux:callout>
                        @endif

                        @if ($mode === 'edit' && $this->currentAttachment && !$removeExistingAttachment && !$attachment)
                            <flux:callout variant="warning" icon="paper-clip">
                                <flux:callout.heading>
                                    Current attachment: {{ $this->currentAttachment->original_name }}
                                </flux:callout.heading>

                                <x-slot name="actions">
                                    <a
                                        href="{{ $this->currentAttachment->url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-sm text-blue-600 underline"
                                    >
                                        View
                                    </a>

                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        wire:click="removeCurrentAttachment"
                                    >
                                        Remove current file
                                    </flux:button>
                                </x-slot>
                            </flux:callout>
                        @endif

                        @if ($mode === 'edit' && $removeExistingAttachment && !$attachment)
                            <flux:callout variant="warning" icon="exclamation-triangle">
                                <flux:callout.heading>
                                    Current attachment akan dibuang apabila anda tekan Update.
                                </flux:callout.heading>
                            </flux:callout>
                        @endif
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
                            wire:target="save,attachment"
                        >
                            <span wire:loading.remove wire:target="save,attachment">Add Transaction</span>
                            <span wire:loading wire:target="save,attachment">Saving...</span>
                        </flux:button>
                    @else
                        <flux:button
                            wire:click="update"
                            variant="primary"
                            wire:loading.attr="disabled"
                            wire:target="update,attachment"
                        >
                            <span wire:loading.remove wire:target="update,attachment">Update</span>
                            <span wire:loading wire:target="update,attachment">Updating...</span>
                        </flux:button>
                    @endif
                </div>
            </div>
        </flux:modal>
    </div>

    {{-- ─── Delete Confirmation Modal ─── --}}
    <flux:modal wire:model.self="showDeleteModal" class="w-full max-w-sm">
        <div class="space-y-6">
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

            <div class="flex gap-3 justify-center">
                <flux:button wire:click="cancelDelete" variant="ghost" class="flex-1">
                    Cancel
                </flux:button>

                <flux:button
                    wire:click="delete"
                    variant="danger"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                    class="flex-1"
                >
                    <span wire:loading.remove wire:target="delete">Delete</span>
                    <span wire:loading wire:target="delete">Deleting...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
