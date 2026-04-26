<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #region Form fields
    public string $name = '';
    public string $type = 'expense';
    #endregion
    
    //Edit state
    public ?int $editId = null;
    //

    // Delete state
    public ?int $deleteId = null;
    public string $deleteName = '';
    public bool $showDeleteModal = false;

    // Modal
    public bool $showModal = false;



    #[Computed]
    public function categories()
    {
        return Category::withCount('transactions')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(15);
    }

    public function openCreate(): void
    {
        $this->reset('name', 'type', 'editId');
        $this->type = 'expense';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editId = $id;
        $this->name   = $category->name;
        $this->type   = $category->type;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|min:2|max:50',
            'type' => 'required|in:income,expense',
        ]);

        Category::updateOrCreate(
            ['id' => $this->editId],
            ['name' => $this->name, 'type' => $this->type, 'user_id' => auth()->id()]
        );

        unset($this->categories);
        $this->showModal = false;
        $this->reset('name', 'type', 'editId');

        Flux::toast(
            text: $this->editId ? 'Category updated!' : 'Category added!',
            variant: 'success'
        );
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteId   = $id;
        $this->deleteName = $name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (!$this->deleteId) return;

        Category::findOrFail($this->deleteId)->delete();

        unset($this->categories);
        $this->showDeleteModal = false;
        $this->reset('deleteId', 'deleteName');

        Flux::toast(text: 'Category deleted!', variant: 'danger');
    }
};
?>

<div class="space-y-6 w-full max-w-[34rem]">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Categories</flux:heading>
            <flux:subheading>Manage your transaction categories</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Add Category
        </flux:button>
</div>

    {{-- Table --}}

<flux:table :paginate="$this->categories" sticky pagination:scroll-to>
    <flux:table.columns>
        <flux:table.column>Name</flux:table.column>
        <flux:table.column>Type</flux:table.column>
        <flux:table.column>Transactions</flux:table.column>
        <flux:table.column class="text-right" align="end">Action</flux:table.column>

    </flux:table.columns>

    <flux:table.rows>
        @foreach($this->categories as $category)
            <flux:table.row :key="$category->id">
                <flux:table.cell>{{ $category->name }}</flux:table.cell>
                <flux:table.cell>
                    <flux:badge
                        variant="pill"
                        color="{{ $category->type === 'income' ? 'green' : ($category->type === 'expense' ? 'red' : 'zinc') }}"
                    >
                        {{ ucfirst($category->type) }}
                    </flux:badge>
                </flux:table.cell>
                <flux:table.cell>
                    {{ $category->transactions_count }}
                </flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-2 justify-end">
                        <flux:button
                            wire:click="openEdit({{ $category->id }})"
                            size="sm" variant="ghost" icon="pencil"
                        />
                        <flux:button
                            wire:click="confirmDelete({{ $category->id }}, '{{ addslashes($category->name) }}')"
                            size="sm" variant="ghost" icon="trash"
                            class="text-red-500 hover:text-red-600"
                        />
                    </div>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>

    {{-- <div>{{ $this->categories->links() }}</div> --}}

    {{-- Add/Edit Modal --}}
    <flux:modal wire:model="showModal" class="w-full max-w-md">
        <flux:modal.close />
        <flux:heading size="lg">
            {{ $editId ? 'Edit Category' : 'Add Category' }}
        </flux:heading>

        <div class="mt-4 space-y-4">
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input
                    wire:model="name"
                    placeholder="e.g. Food & Drinks"
                    autofocus
                />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Type</flux:label>
                <flux:select wire:model="type">
                    <flux:select.option value="expense">Expense</flux:select.option>
                    <flux:select.option value="income">Income</flux:select.option>
                </flux:select>
                <flux:error name="type" />
            </flux:field>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <flux:button wire:click="$set('showModal', false)" variant="ghost">
                Cancel
            </flux:button>
            <flux:button wire:click="save" variant="primary">
                {{ $editId ? 'Update' : 'Add' }}
            </flux:button>
        </div>
    </flux:modal>

    {{-- Delete Confirm Modal --}}
    <flux:modal wire:model="showDeleteModal" class="w-full max-w-sm">
        <flux:heading size="lg">Delete Category</flux:heading>
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            Delete <strong>{{ $deleteName }}</strong>? Transactions using this category will become uncategorized.
        </p>
        <div class="mt-6 flex justify-end gap-2">
            <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">
                Cancel
            </flux:button>
            <flux:button wire:click="delete" variant="danger">
                Delete
            </flux:button>
        </div>
    </flux:modal>

</div>