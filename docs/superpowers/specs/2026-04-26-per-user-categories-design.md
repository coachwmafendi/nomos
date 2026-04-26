# Per-User Categories Design

**Date:** 2026-04-26  
**Status:** Approved

## Problem

Categories are currently global — all users share one category list. Goal: make categories per-user so each user owns and manages their own category list.

## Decisions

- New users receive 21 default categories on registration
- Existing categories are copied per-user during migration; orphaned global rows deleted
- `transactions.category_id` remapped during migration to point to new per-user category copies
- Default categories seeded via User model observer (fires for all user creation paths)
- Default category list defined in a single `DefaultCategories` data class (used by observer + migration + seeder)

## Architecture

### 1. Database Migration

New migration: `add_user_id_to_categories_table`

Steps (in order):
1. Add nullable `user_id` column (foreign key → `users.id`, cascade delete) to `categories`
2. For each existing user: insert copies of all current categories with `user_id` set
3. For each existing user's transactions: remap `category_id` from old global category → new per-user copy (match by `name` + `type`)
4. For each existing user's budgets: remap `category_id` same way
5. For each existing user's recurring_transactions: remap `category_id` same way
6. Delete original global categories (rows with `user_id = null`)
7. Make `user_id` non-nullable

### 2. DefaultCategories Data Class

**File:** `app/Data/DefaultCategories.php`

Single source of truth for the 21 default categories. Simple class with a `LIST` constant (array of `['name' => string, 'type' => string]`).

Used by:
- `UserObserver` — seeds on new user creation
- Migration — copies per existing user
- `CategorySeeder` — seeds per existing user

### 3. UserCategoryScope

**File:** `app/Models/Scopes/UserCategoryScope.php`

Implements `Scope`. Applies `where('user_id', auth()->id())` only when `auth()->check()` is true. Skips silently when unauthenticated (seeders, migrations, CLI run clean without `withoutGlobalScopes()`).

Applied to `Category` model via `#[ScopedBy(UserCategoryScope::class)]` attribute.

### 4. Category Model Changes

**File:** `app/Models/Category.php`

- Add `user_id` to `$fillable`
- Add `user()` → `belongsTo(User::class)` relationship
- Add `#[ScopedBy(UserCategoryScope::class)]` attribute

### 5. User Observer

**File:** `app/Observers/UserObserver.php`

`created(User $user)` method: iterates `DefaultCategories::LIST`, creates each category with `user_id = $user->id`.

Registered in `AppServiceProvider::boot()`:
```php
User::observe(UserObserver::class);
```

### 6. manage-categories CRUD

**File:** `resources/views/components/⚡manage-categories.blade.php`

Only change: pass `user_id => auth()->id()` when creating/updating categories. All read queries auto-scoped by global scope — no other component needs changes.

### 7. CategorySeeder

**File:** `database/seeders/CategorySeeder.php`

Loop over all users, seed `DefaultCategories::LIST` per user using `firstOrCreate(['name', 'type', 'user_id'])` to avoid duplicates.

## Files Changed

| File | Change |
|---|---|
| `database/migrations/XXXX_add_user_id_to_categories_table.php` | New migration (add col + data remap) |
| `app/Data/DefaultCategories.php` | New — default category list |
| `app/Models/Scopes/UserCategoryScope.php` | New — global scope |
| `app/Models/Category.php` | Add fillable, relationship, ScopedBy |
| `app/Observers/UserObserver.php` | New — seed categories on user created |
| `app/Providers/AppServiceProvider.php` | Register UserObserver |
| `resources/views/components/⚡manage-categories.blade.php` | Add user_id on save |
| `database/seeders/CategorySeeder.php` | Seed per user |
| `tests/Feature/` | Add/update category + observer tests |

## Security

Global scope ensures no component can accidentally return another user's categories. A user can only `findOrFail` their own categories — delete/edit of another user's category returns 404 automatically.

## Out of Scope

- Category sharing between users
- Admin-managed global categories
- Per-user customization of default category list at registration
