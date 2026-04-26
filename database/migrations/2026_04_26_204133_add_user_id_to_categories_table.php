<?php

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
                    'user_id' => $user->id,
                    'name' => $cat->name,
                    'type' => $cat->type,
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
