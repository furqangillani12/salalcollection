<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create branch_product_stock pivot table (if missing)
        if (!Schema::hasTable('branch_product_stock')) {
            Schema::create('branch_product_stock', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('stock_quantity', 10, 2)->default(0);
                $table->decimal('reorder_level', 10, 2)->default(10);
                $table->unique(['branch_id', 'product_id']);
                $table->timestamps();
            });
        }

        // 2-8. Add branch_id to various tables (if missing)
        $branchIdTables = ['orders', 'users', 'employees', 'attendances', 'payrolls', 'purchases', 'inventory_logs'];
        foreach ($branchIdTables as $tbl) {
            if (Schema::hasTable($tbl) && !Schema::hasColumn($tbl, 'branch_id')) {
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
                    if ($tbl === 'orders') {
                        $table->index('branch_id');
                    }
                });
            }
        }

        // 9. Ensure a default branch exists, then assign orphan records to it
        $existing = DB::table('branches')->first();
        if ($existing) {
            $defaultBranch = $existing->id;
        } else {
            $defaultBranch = DB::table('branches')->insertGetId([
                'name'       => 'Almufeed Saqafti Markaz',
                'code'       => 'ASM',
                'address'    => 'Main Branch',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Migrate product stock to branch_product_stock for the default branch (only missing rows)
        $existingProductIds = DB::table('branch_product_stock')
            ->where('branch_id', $defaultBranch)
            ->pluck('product_id')
            ->toArray();

        $products = DB::table('products')
            ->whereNotIn('id', $existingProductIds ?: [0])
            ->get(['id', 'stock_quantity', 'reorder_level']);

        foreach ($products as $product) {
            DB::table('branch_product_stock')->insert([
                'branch_id'      => $defaultBranch,
                'product_id'     => $product->id,
                'stock_quantity' => $product->stock_quantity ?? 0,
                'reorder_level'  => $product->reorder_level ?? 10,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // Assign existing records to the default branch (only where NULL).
        // EXCEPTION: admin/super_admin/manager users must keep branch_id = NULL
        // so they can switch between branches freely.
        foreach ($branchIdTables as $tbl) {
            if (!Schema::hasColumn($tbl, 'branch_id')) {
                continue;
            }

            if ($tbl === 'users' && Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
                $adminManagerUserIds = DB::table('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->whereIn('roles.name', ['admin', 'super_admin', 'manager'])
                    ->where('model_has_roles.model_type', 'App\\Models\\User')
                    ->pluck('model_has_roles.model_id')
                    ->toArray();

                DB::table('users')
                    ->whereNull('branch_id')
                    ->whereNotIn('id', $adminManagerUserIds ?: [0])
                    ->update(['branch_id' => $defaultBranch]);
            } else {
                DB::table($tbl)->whereNull('branch_id')->update(['branch_id' => $defaultBranch]);
            }
        }

        // 10. Add manage branches permission (idempotent)
        if (!DB::table('permissions')->where('name', 'manage branches')->exists()) {
            DB::table('permissions')->insert([
                ['name' => 'manage branches',   'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'view all branches', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ]);

            $adminRole = DB::table('roles')->where('name', 'admin')->first();
            if ($adminRole) {
                $perms = DB::table('permissions')->whereIn('name', ['manage branches', 'view all branches'])->pluck('id');
                foreach ($perms as $permId) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $permId,
                        'role_id'       => $adminRole->id,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::dropIfExists('branch_product_stock');

        DB::table('permissions')->whereIn('name', ['manage branches', 'view all branches'])->delete();
    }
};
