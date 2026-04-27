# Add Transaction Shortcut Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let users add a transaction from the dashboard (desktop button) or any page (mobile FAB), both routing to `/transactions?create=1` which auto-opens the add modal.

**Architecture:** Three isolated changes — (1) manage-transactions detects `?create=1` query param in `mount()` and sets `$showModal = true`, (2) dashboard header gains a primary "Add Transaction" button, (3) sidebar layout gains a mobile-only FAB. No new components, no new routes, no duplicate form logic.

**Tech Stack:** Livewire 4 SFC (Volt), Flux UI v2, Tailwind CSS v4, Pest v4

---

### Task 1: Auto-open modal on `?create=1`

**Files:**
- Modify: `resources/views/components/transactions/⚡manage-transactions.blade.php` (line 61–64, the `mount()` method)
- Test: `tests/Feature/ManageTransactionsTest.php` (create new)

- [ ] **Step 1: Write the failing test**

```php
<?php

use App\Models\User;
use Livewire\Livewire;

test('manage transactions page auto-opens modal when create query param is present', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->withQueryParams(['create' => '1'])
        ->test(\Livewire\Volt\Volt::class, ['name' => 'transactions.manage-transactions'])
        ->assertSet('showModal', true);
});

test('manage transactions page does not open modal without create query param', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\Livewire\Volt\Volt::class, ['name' => 'transactions.manage-transactions'])
        ->assertSet('showModal', false);
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=ManageTransactions
```

Expected: 2 FAILs — `showModal` is always `false` in `mount()`.

- [ ] **Step 3: Update `mount()` to detect `?create=1`**

In `resources/views/components/transactions/⚡manage-transactions.blade.php`, replace:

```php
public function mount(): void
{
    $this->date = now()->format('Y-m-d');
}
```

With:

```php
public function mount(): void
{
    $this->date = now()->format('Y-m-d');

    if (request()->query('create') === '1') {
        $this->showModal = true;
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test --compact --filter=ManageTransactions
```

Expected: 2 PASSes.

- [ ] **Step 5: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/transactions/⚡manage-transactions.blade.php tests/Feature/ManageTransactionsTest.php
git commit -m "feat: auto-open add modal when ?create=1 query param present"
```

---

### Task 2: Add Transaction button on Dashboard header

**Files:**
- Modify: `resources/views/components/pages/⚡dashboard.blade.php` (line 174–189, the header section)

- [ ] **Step 1: Add button to dashboard header**

In `resources/views/components/pages/⚡dashboard.blade.php`, replace:

```blade
<div class="flex items-center justify-between flex-wrap gap-4 mb-6">
    <h2 class="text-lg font-semibold">Dashboard</h2>
    <div class="flex items-center gap-3">
        <flux:input
            type="date"
            wire:model.live="dateFrom"
            label="From"
        />
        <span class="text-gray-400 mt-5">→</span>
        <flux:input
            type="date"
            wire:model.live="dateTo"
            label="To"
        />
    </div>
</div>
```

With:

```blade
<div class="flex items-center justify-between flex-wrap gap-4 mb-6">
    <h2 class="text-lg font-semibold">Dashboard</h2>
    <div class="flex items-center gap-3">
        <flux:input
            type="date"
            wire:model.live="dateFrom"
            label="From"
        />
        <span class="text-gray-400 mt-5">→</span>
        <flux:input
            type="date"
            wire:model.live="dateTo"
            label="To"
        />
        <div class="mt-5">
            <flux:button
                href="{{ route('transactions') }}?create=1"
                wire:navigate
                variant="primary"
                icon="plus"
            >
                Add Transaction
            </flux:button>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Write a smoke test**

Add to `tests/Feature/DashboardTest.php`:

```php
test('dashboard shows add transaction button for authenticated users', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee(route('transactions') . '?create=1');
});
```

- [ ] **Step 3: Run tests**

```bash
php artisan test --compact --filter=DashboardTest
```

Expected: all PASSes.

- [ ] **Step 4: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/pages/⚡dashboard.blade.php tests/Feature/DashboardTest.php
git commit -m "feat: add Add Transaction button to dashboard header"
```

---

### Task 3: Mobile FAB in sidebar layout

**Files:**
- Modify: `resources/views/layouts/app/sidebar.blade.php`

- [ ] **Step 1: Add FAB before closing `</flux:sidebar>` tag**

In `resources/views/layouts/app/sidebar.blade.php`, locate `<x-desktop-user-menu ...>` line and add the FAB above the closing body wrapper. The FAB goes outside the sidebar, fixed to the viewport. Add this block just before the `<!-- Mobile User Menu -->` comment:

```blade
{{-- Mobile FAB — fixed bottom-right, hidden on desktop --}}
<a
    href="{{ route('transactions') }}?create=1"
    wire:navigate
    class="lg:hidden fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 rounded-full bg-indigo-500 text-white shadow-lg hover:bg-indigo-600 active:bg-indigo-700 transition-colors"
    aria-label="Add Transaction"
>
    <flux:icon name="plus" class="size-6" />
</a>
```

- [ ] **Step 2: Verify FAB does not appear on desktop**

The `lg:hidden` class hides the FAB at `lg` breakpoint (1024px+). Confirm by checking Tailwind v4 responsive docs — `lg:hidden` = `display: none` at ≥1024px. No test needed; this is a CSS visibility rule.

- [ ] **Step 3: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 4: Run full test suite**

```bash
php artisan test --compact
```

Expected: all PASSes.

- [ ] **Step 5: Commit**

```bash
git add resources/views/layouts/app/sidebar.blade.php
git commit -m "feat: add mobile FAB for quick Add Transaction access"
```
