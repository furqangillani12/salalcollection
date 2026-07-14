<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restore branch freedom for admin/super_admin/manager users.
 *
 * A prior multi-branch migration assigned a default branch_id to EVERY user,
 * including admins — which locked them to a single branch. Admins/managers
 * are supposed to keep branch_id = NULL so they can switch freely.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'branch_id')) {
            return;
        }

        if (!Schema::hasTable('model_has_roles') || !Schema::hasTable('roles')) {
            return;
        }

        $adminManagerUserIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('roles.name', ['admin', 'super_admin', 'manager'])
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('model_has_roles.model_id')
            ->unique()
            ->toArray();

        if (!empty($adminManagerUserIds)) {
            DB::table('users')
                ->whereIn('id', $adminManagerUserIds)
                ->whereNotNull('branch_id')
                ->update(['branch_id' => null]);
        }
    }

    public function down(): void
    {
        // One-way fix — leave admins unlocked on rollback too.
    }
};
