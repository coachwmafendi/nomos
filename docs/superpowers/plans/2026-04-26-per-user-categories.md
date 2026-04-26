# Per-User Categories Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make categories per-user — each user owns their own category list, with 21 defaults seeded on registration, and all category queries automatically scoped by auth user via a global scope.

**Architecture:** Add `user_id` to the `categories` table. A `UserCategoryScope` global scope (applied via `ScopedBy` attribute) automatically filters all `Category::` queries by `auth()->id()` when authenticated — no component changes needed for reads. A `UserObserver` seeds 21 default categories on new user creation. A `DefaultCategories` data class is the single source of truth for the default list, used by the observer, migration data step, and seeder.

**Tech Stack:** Laravel 13, Livewire 4, Pest 4, SQLite (Herd), PHP 8.4

---

> **Note:** Pre-existing test failures exist (`no such table: users` in Settings tests). These are unrelated to this feature. Run only the `PerUserCategoriesTest` filter during this work.

---

### Task 1: DefaultCategories data class

**Files:**
- Create: `app/Data/DefaultCategories.php`

- [ ] **Step 1: Create the file**

```php
<?php

namespace App\Data;

class DefaultCategories
{
    public const LIST = [
        ['name' => 'Food & Drinks',    'type' => 'expense'],
        ['name' => 'Transport',         'type' => 'expense'],
        ['name' => 'Shopping',          'type' => 'expense'],
        ['name' => 'Bills & Utilities', 'type' => 'expense'],
        ['name' => 'Health/Medical',    'type' => 'expense'],
        ['name' => 'Business',          'type' => 'expense'],
        ['name' => 'Entertainment',     'type' => 'expense'],
        ['name' => 'Education',         'type' => 'expense'],
        ['name' => 'Travel',            'type' => 'expense'],
        ['name' => 'Donation/Zakat',    'type' => 'expense'],
        ['name' => 'Household',         'type' => 'expense'],
        ['name' => 'Remuneration',      'type' => 'expense'],
        ['name' => 'Other Expense',     'type' => 'expense'],
        ['name' => 'Salary',            'type' => 'income'],
        ['name' => 'Freelance',         'type' => 'income'],
        ['name' => 'Investment',        'type' => 'income'],
        ['name' => 'Commission',        'type' => 'income'],
        ['name' => 'Gift',              'type' => 'income'],
        ['name' => 'Bonus',             'type' => 'income'],
        ['name' => 'Pension',           'type' => 'income'],
        ['name' => 'Other Income',      'type' => 'income'],
    ];
}
```

- [ ] **Step 2: Verify class loads**

```bash
php artisan tinker --execute 'echo count(App\Data\DefaultCategories::LIST);'
```

Expected output: `21`

- [ ] **Step 3: Commit**

```bash
git add app/Data/DefaultCategories.php
git commit -m "feat: add DefaultCategories data class"
```

---

### Task 2: UserCategoryScope global scope

**Files:**
- Create: `app/Models/Scopes/UserCategoryScope.php`

- [ ] **Step 1: Write the failing test**

In `tests/Feature/PerUserCategoriesTest.php`, replace the generated contents:

```php
<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('scopes categories to the authenticated user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $catA = Category::create(['name' => 'A Cat', 'type' => 'expense', 'user_id' => $userA->id]);
    $catB = Category::create(['name' => 'B Cat', 'type' => 'expense', 'user_id' => $userB->id]);

    $this->actingAs($userA);

    $categories = Category::all();

    expect($categories->pluck('id'))->toContain($catA->id)
        ->not->toContain($catB->id);
});

it('returns all categories when unauthenticated (for seeders)', function () {
    $userA = User::factory()->create();
    Category::create(['name' => 'A Cat', 'type' => 'expense', 'user_id' => $userA->id]);

    // No actingAs — unauthenticated context
    $categories = Category::all();

    expect($categories)->toHaveCount(1);
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=PerUserCategoriesTest
```

Expected: FAIL — `user_id` column doesn't exist yet, scope not applied.

- [ ] **Step 3: Create the scope**

```php
<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserCategoryScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $builder->where('categories.user_id', auth()->id());
        }
    }
}
```

Save to `app/Models/Scopes/UserCategoryScope.php`.

- [ ] **Step 4: Commit scope (tests still fail — migration + model needed next)**

```bash
git add app/Models/Scopes/UserCategoryScope.php
git commit -m "feat: add UserCategoryScope global scope"
```

---

### Task 3: Migration — add user_id + data remap

**Files:**
- Modify: `database/migrations/2026_04_26_204133_add_user_id_to_categories_table.php`

- [ ] **Step 1: Write the migration**

Replace the generated migration content:

```php
<?php

use App\Data\DefaultCategories;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add nullable user_id first
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // 2. For each user, copy all existing global categories and remap foreign keys
        $users = DB::table('users')->get();
        $globalCategories = DB::table('categories')->whereNull('user_id')->get();

        foreach ($users as $user) {
            $idMap = []; // old category id => new per-user category id

            foreach ($globalCategories as $cat) {
                $newId = DB::table('categories')->insertGetId([
                    'user_id'    => $user->id,
                    'name'       => $cat->name,
                    'type'       => $cat->type,
                    'created_at' => $cat->created_at,
                    'updated_at' => $cat->updated_at,
                ]);
                $idMap[$cat->id] = $newId;
            }

            // Remap transactions
            foreach ($idMap as $oldId => $newId) {
                DB::table('transactions')
                    ->where('user_id', $user->id)
                    ->where('category_id', $oldId)
                    ->update(['category_id' => $newId]);
            }

            // Remap budgets
            foreach ($idMap as $oldId => $newId) {
                DB::table('budgets')
                    ->where('user_id', $user->id)
                    ->where('category_id', $oldId)
                    ->update(['category_id' => $newId]);
            }

            // Remap recurring_transactions
            foreach ($idMap as $oldId => $newId) {
                DB::table('recurring_transactions')
                    ->where('user_id', $user->id)
                    ->where('category_id', $oldId)
                    ->update(['category_id' => $newId]);
            }
        }

        // 3. Delete original global (unowned) categories
        // user_id remains nullable at DB level; app layer (observer + fillable) prevents null values
        DB::table('categories')->whereNull('user_id')->delete();
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
```

- [ ] **Step 2: Run the migration**

```bash
php artisan migrate
```

Expected: migrates successfully with no errors.

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_04_26_204133_add_user_id_to_categories_table.php
git commit -m "feat: migration to add user_id to categories and remap existing data"
```

---

### Task 4: Update Category model

**Files:**
- Modify: `app/Models/Category.php`

- [ ] **Step 1: Update the model**

Replace `app/Models/Category.php` entirely:

```php
<?php

namespace App\Models;

use App\Models\Scopes\UserCategoryScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(UserCategoryScope::class)]
class Category extends Model
{
    protected $fillable = ['user_id', 'name', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
```

- [ ] **Step 2: Run tests**

```bash
php artisan test --compact --filter=PerUserCategoriesTest
```

Expected: both tests pass. The scope test passes because `user_id` column now exists and scope is applied. The unauthenticated test passes because scope skips when `auth()->check()` is false.

- [ ] **Step 3: Run pint**

```bash
vendor/bin/pint app/Models/Category.php app/Models/Scopes/UserCategoryScope.php --format agent
```

- [ ] **Step 4: Commit**

```bash
git add app/Models/Category.php
git commit -m "feat: apply UserCategoryScope and user relationship to Category model"
```

---

### Task 5: UserObserver — seed defaults on registration

**Files:**
- Create: `app/Observers/UserObserver.php`
- Modify: `app/Providers/AppServiceProvider.php`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/PerUserCategoriesTest.php`:

```php
it('seeds 21 default categories when a new user is created', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    expect(Category::count())->toBe(21);

    expect(Category::where('type', 'expense')->count())->toBe(13);
    expect(Category::where('type', 'income')->count())->toBe(8);
});

it('does not seed categories for another user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $this->actingAs($userA);
    expect(Category::count())->toBe(21);

    $this->actingAs($userB);
    expect(Category::count())->toBe(21);
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=PerUserCategoriesTest
```

Expected: new tests FAIL — observer doesn't exist yet.

- [ ] **Step 3: Create the observer**

```php
<?php

namespace App\Observers;

use App\Data\DefaultCategories;
use App\Models\Category;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        foreach (DefaultCategories::LIST as $category) {
            Category::create([
                'user_id' => $user->id,
                'name'    => $category['name'],
                'type'    => $category['type'],
            ]);
        }
    }
}
```

Save to `app/Observers/UserObserver.php`.

- [ ] **Step 4: Register observer in AppServiceProvider**

In `app/Providers/AppServiceProvider.php`, add to the `boot()` method (after the Blaze line):

```php
use App\Models\User;
use App\Observers\UserObserver;
```

Add to `boot()`:

```php
User::observe(UserObserver::class);
```

Full updated `boot()`:

```php
public function boot(): void
{
    Blaze::optimize()->in(resource_path('views/components'));

    User::observe(UserObserver::class);

    $this->configureDefaults();
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=PerUserCategoriesTest
```

Expected: all 4 tests pass.

- [ ] **Step 6: Run pint**

```bash
vendor/bin/pint app/Observers/UserObserver.php app/Providers/AppServiceProvider.php --format agent
```

- [ ] **Step 7: Commit**

```bash
git add app/Observers/UserObserver.php app/Providers/AppServiceProvider.php
git commit -m "feat: add UserObserver to seed default categories on user registration"
```

---

### Task 6: manage-categories — attach user_id on save

**Files:**
- Modify: `resources/views/components/⚡manage-categories.blade.php:62-68`

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/PerUserCategoriesTest.php`:

```php
use Livewire\Livewire;

it('saves a new category with the authenticated user id', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('manage-categories')
        ->set('name', 'My Custom Category')
        ->set('type', 'expense')
        ->call('save');

    expect(Category::where('name', 'My Custom Category')->where('user_id', $user->id)->exists())->toBeTrue();
});

it('cannot see another users category in manage-categories', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $this->actingAs($userA);
    $catA = Category::create(['user_id' => $userA->id, 'name' => 'A Only', 'type' => 'expense']);

    $this->actingAs($userB);

    Livewire::test('manage-categories')
        ->assertDontSee('A Only');
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=PerUserCategoriesTest
```

Expected: new tests FAIL — `updateOrCreate` doesn't pass `user_id` yet.

- [ ] **Step 3: Fix the save method**

In `resources/views/components/⚡manage-categories.blade.php`, update the `updateOrCreate` call (around line 62):

```php
Category::updateOrCreate(
    ['id' => $this->editId],
    ['user_id' => auth()->id(), 'name' => $this->name, 'type' => $this->type]
);
```

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=PerUserCategoriesTest
```

Expected: all tests pass.

- [ ] **Step 5: Run pint**

```bash
vendor/bin/pint --format agent
```

- [ ] **Step 6: Commit**

```bash
git add "resources/views/components/⚡manage-categories.blade.php"
git commit -m "feat: attach user_id when saving category in manage-categories"
```

---

### Task 7: Update CategorySeeder

**Files:**
- Modify: `database/seeders/CategorySeeder.php`

- [ ] **Step 1: Update the seeder**

Replace `database/seeders/CategorySeeder.php` entirely:

```php
<?php

namespace Database\Seeders;

use App\Data\DefaultCategories;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        User::each(function (User $user) {
            foreach (DefaultCategories::LIST as $category) {
                Category::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $category['name'], 'type' => $category['type']]
                );
            }
        });
    }
}
```

- [ ] **Step 2: Verify seeder runs clean**

```bash
php artisan db:seed --class=CategorySeeder
```

Expected: runs without errors.

- [ ] **Step 3: Run pint**

```bash
vendor/bin/pint database/seeders/CategorySeeder.php --format agent
```

- [ ] **Step 4: Commit**

```bash
git add database/seeders/CategorySeeder.php
git commit -m "feat: update CategorySeeder to seed per-user categories"
```

---

### Task 8: Final verification

- [ ] **Step 1: Run full feature test**

```bash
php artisan test --compact --filter=PerUserCategoriesTest
```

Expected: all tests pass (6 tests minimum).

- [ ] **Step 2: Smoke test in browser**

Navigate to `/categories`. Verify:
- Categories list shows only current user's categories
- Adding a new category saves with the current user's `user_id`
- Deleting a category works

Navigate to `/transactions`, open create form. Verify category dropdown populates correctly.

Navigate to `/budget`. Verify expense categories load.

- [ ] **Step 3: Commit test file**

```bash
git add tests/Feature/PerUserCategoriesTest.php
git commit -m "test: add per-user categories feature tests"
```

---

## File Summary

| File | Action |
|---|---|
| `app/Data/DefaultCategories.php` | Create |
| `app/Models/Scopes/UserCategoryScope.php` | Create |
| `app/Observers/UserObserver.php` | Create |
| `database/migrations/2026_04_26_204133_add_user_id_to_categories_table.php` | Modify |
| `app/Models/Category.php` | Modify |
| `app/Providers/AppServiceProvider.php` | Modify |
| `resources/views/components/⚡manage-categories.blade.php` | Modify (line 62) |
| `database/seeders/CategorySeeder.php` | Modify |
| `tests/Feature/PerUserCategoriesTest.php` | Create |
