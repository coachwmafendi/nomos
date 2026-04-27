# Add Transaction Shortcut — Design Spec

**Date:** 2026-04-27
**Status:** Approved

## Problem

Dashboard is the first page users see. No quick way to add a transaction without navigating to Transactions page manually. High friction for the most frequent action.

## Solution

Two entry points, one destination: `/transactions?create=1`

### Entry Point 1 — Dashboard Button (Desktop)

- Location: Dashboard page header, right side, next to date filter
- Style: `flux:button` with `variant="primary"`, icon `plus`, indigo accent
- Label: "Add Transaction"
- Action: `wire:navigate` to `route('transactions') . '?create=1'`

### Entry Point 2 — Floating Action Button (Mobile only)

- Location: Fixed bottom-right, all pages, inside `sidebar.blade.php`
- Visibility: `lg:hidden` — hidden on desktop, shown on mobile only
- Style: Indigo circle button, `plus` icon, shadow-lg, `z-50`
- Size: 56×56px (standard FAB size)
- Action: `href="{{ route('transactions') }}?create=1"` with `wire:navigate`

### Destination — Auto-open Modal

- `manage-transactions` component `mount()` detects `request()->query('create') === '1'`
- Sets `$this->showModal = true` (or equivalent property that controls add modal)
- After save, modal closes, user sees transaction in list — clear feedback

## Architecture

- No new components. No duplicate form logic.
- Single source of truth: manage-transactions add modal.
- URL param approach: back-button safe, shareable.

## Files to Change

1. `resources/views/components/pages/⚡dashboard.blade.php` — add button to header
2. `resources/views/layouts/app/sidebar.blade.php` — add FAB (mobile only)
3. `resources/views/components/⚡manage-transactions.blade.php` — detect `?create=1` in `mount()`

## Out of Scope

- No FAB on desktop
- No inline add form on dashboard
- No new routes
