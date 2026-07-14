<?php

/**
 * ╔═══════════════════════════════════════════════════════════════════╗
 * ║  SAFE DEPLOYMENT MIGRATION                                       ║
 * ║  Consolidates ALL new schema changes with hasTable/hasColumn     ║
 * ║  checks so it can run safely on a live database that may or      ║
 * ║  may not already have some of these changes.                     ║
 * ║                                                                   ║
 * ║  Run on server:                                                   ║
 * ║  php artisan migrate --path=database/migrations/2026_03_12_100000_safe_deploy_all_changes.php ║
 * ╚═══════════════════════════════════════════════════════════════════╝
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════
        // 1. CREATE NEW TABLES (only if they don't exist)
        // ═══════════════════════════════════════════════════════════

        // ── Branches ──
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable()->unique();
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ── Payrolls ──
        if (!Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->integer('month');
                $table->integer('year');
                $table->integer('present_days')->default(0);
                $table->integer('absent_days')->default(0);
                $table->integer('late_days')->default(0);
                $table->decimal('gross_salary', 10, 2)->default(0);
                $table->decimal('deductions', 10, 2)->default(0);
                $table->decimal('net_salary', 10, 2)->default(0);
                $table->decimal('total_hours', 8, 2)->default(0);
                $table->decimal('hourly_rate', 10, 2)->default(0);
                $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
                $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });
        } else {
            Schema::table('payrolls', function (Blueprint $table) {
                if (!Schema::hasColumn('payrolls', 'total_hours')) {
                    $table->decimal('total_hours', 8, 2)->default(0)->after('net_salary');
                }
                if (!Schema::hasColumn('payrolls', 'hourly_rate')) {
                    $table->decimal('hourly_rate', 10, 2)->default(0)->after('total_hours');
                }
                if (!Schema::hasColumn('payrolls', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
                }
            });
        }

        // ── Attendance Sessions ──
        if (!Schema::hasTable('attendance_sessions')) {
            Schema::create('attendance_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
                $table->time('check_in');
                $table->time('check_out')->nullable();
                $table->timestamps();
            });
        }

        // ── Units ──
        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('abbreviation')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ── Credit Ledgers ──
        if (!Schema::hasTable('credit_ledgers')) {
            Schema::create('credit_ledgers', function (Blueprint $table) {
                $table->id();
                $table->string('ledger_number')->unique();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->decimal('total_debit', 12, 2)->default(0);
                $table->decimal('total_credit', 12, 2)->default(0);
                $table->decimal('opening_balance', 12, 2)->default(0);
                $table->decimal('closing_balance', 12, 2)->default(0);
                $table->decimal('credit_limit', 12, 2)->default(0);
                $table->enum('status', ['active', 'inactive', 'closed'])->default('active');
                $table->date('last_transaction_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index('customer_id');
                $table->index('status');
            });
        }

        // ── Credit Transactions ──
        if (!Schema::hasTable('credit_transactions')) {
            Schema::create('credit_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_number')->unique();
                $table->foreignId('credit_ledger_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
                $table->enum('transaction_type', ['debit', 'credit']);
                $table->decimal('amount', 12, 2);
                $table->decimal('balance_before', 12, 2);
                $table->decimal('balance_after', 12, 2);
                $table->string('reference_number')->nullable();
                $table->text('description')->nullable();
                $table->date('transaction_date');
                $table->date('due_date')->nullable();
                $table->date('payment_date')->nullable();
                $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
                $table->json('items')->nullable();
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->decimal('remaining_amount', 12, 2)->default(0);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // ── Ledger Entries ──
        if (!Schema::hasTable('ledger_entries')) {
            Schema::create('ledger_entries', function (Blueprint $table) {
                $table->id();
                $table->string('entry_number')->unique();
                $table->date('entry_date');
                $table->string('account_type');
                $table->string('transaction_type');
                $table->nullableMorphs('reference');
                $table->string('reference_number')->nullable();
                $table->string('description');
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->string('payment_method')->nullable();
                $table->string('party_type')->nullable();
                $table->unsignedBigInteger('party_id')->nullable();
                $table->string('party_name')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['entry_date', 'account_type']);
                $table->index('transaction_type');
                $table->index(['party_type', 'party_id']);
            });
        }

        // ── Ledger Accounts ──
        if (!Schema::hasTable('ledger_accounts')) {
            Schema::create('ledger_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('account_code')->unique();
                $table->string('name');
                $table->string('type');
                $table->string('category')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // ── Ledger Account Entries ──
        if (!Schema::hasTable('ledger_account_entries')) {
            Schema::create('ledger_account_entries', function (Blueprint $table) {
                $table->id();
                $table->string('entry_number')->unique();
                $table->foreignId('ledger_account_id')->constrained()->cascadeOnDelete();
                $table->date('entry_date');
                $table->string('description');
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_number')->nullable();
                $table->string('payment_method')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['ledger_account_id', 'entry_date']);
            });
        }

        // ── Branch Product Stock ──
        if (!Schema::hasTable('branch_product_stock')) {
            Schema::create('branch_product_stock', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->decimal('stock_quantity', 10, 2)->default(0);
                $table->decimal('reorder_level', 10, 2)->default(10);
                $table->unique(['branch_id', 'product_id']);
                $table->timestamps();
            });
        }

        // ── Supplier Payments ──
        if (!Schema::hasTable('supplier_payments')) {
            Schema::create('supplier_payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_number')->unique();
                $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('amount', 12, 2);
                $table->date('payment_date');
                $table->string('payment_method')->default('cash');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // ── Payment Methods ──
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('label');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ── Dispatch Methods ──
        if (!Schema::hasTable('dispatch_methods')) {
            Schema::create('dispatch_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('has_tracking')->default(false);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }


        // ═══════════════════════════════════════════════════════════
        // 2. ADD COLUMNS TO EXISTING TABLES (with hasColumn checks)
        // ═══════════════════════════════════════════════════════════

        // ── Orders: Change payment_method from ENUM to VARCHAR ──
        // (needed to support dynamic payment methods like jazzcash, easypaisa, etc.)
        try {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method VARCHAR(255) NULL");
        } catch (\Exception $e) {
            // Already VARCHAR or doesn't exist
        }

        // ── Orders: tax_rate column ──
        if (!Schema::hasColumn('orders', 'tax_rate')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('notes');
            });
        }

        // ── Orders: set defaults on decimal columns ──
        try {
            DB::statement("ALTER TABLE orders MODIFY COLUMN subtotal DECIMAL(10,2) DEFAULT 0");
            DB::statement("ALTER TABLE orders MODIFY COLUMN tax DECIMAL(10,2) DEFAULT 0");
            DB::statement("ALTER TABLE orders MODIFY COLUMN discount DECIMAL(10,2) DEFAULT 0");
            DB::statement("ALTER TABLE orders MODIFY COLUMN total DECIMAL(10,2) DEFAULT 0");
        } catch (\Exception $e) {
            // Already has defaults
        }

        // ── Products: track_inventory ──
        if (!Schema::hasColumn('products', 'track_inventory')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('track_inventory')->default(true);
            });
        }

        // ── Payments: rename old columns if they exist ──
        if (Schema::hasColumn('payments', 'method') && !Schema::hasColumn('payments', 'payment_method')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->renameColumn('method', 'payment_method');
            });
        }
        if (Schema::hasColumn('payments', 'reference') && !Schema::hasColumn('payments', 'reference_number')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->renameColumn('reference', 'reference_number');
            });
        }

        // ── Attendances: session + half_day ──
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'session')) {
                $table->enum('session', ['morning', 'evening', 'night'])->default('morning')->after('date');
            }
            if (!Schema::hasColumn('attendances', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        // Note: Changing enum values for status requires raw SQL
        // We'll modify 'status' to include 'half_day'
        try {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','late','on_leave','half_day') DEFAULT 'present'");
        } catch (\Exception $e) {
            // Already modified or not needed
        }

        // ── Products: prices, weight, rank, unit_id, branch_id, decimal stock ──
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'resale_price')) {
                $table->decimal('resale_price', 10, 2)->nullable()->after('sale_price');
            }
            if (!Schema::hasColumn('products', 'wholesale_price')) {
                $table->decimal('wholesale_price', 10, 2)->nullable()->after('resale_price');
            }
            if (!Schema::hasColumn('products', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable()->after('cost_price');
            }
            if (!Schema::hasColumn('products', 'rank')) {
                $table->string('rank', 50)->nullable()->after('name');
            }
            if (!Schema::hasColumn('products', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('products', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        // Change stock_quantity to decimal
        try {
            DB::statement("ALTER TABLE products MODIFY COLUMN stock_quantity DECIMAL(10,2) DEFAULT 0");
            DB::statement("ALTER TABLE products MODIFY COLUMN reorder_level INTEGER DEFAULT 0");
        } catch (\Exception $e) {
            // Already modified
        }

        // ── Customers: customer_type, barcode, credit fields, branch_id ──
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'customer_type')) {
                $table->enum('customer_type', ['customer', 'reseller', 'wholesale'])->default('customer')->after('address');
            }
            if (!Schema::hasColumn('customers', 'barcode')) {
                $table->string('barcode')->nullable()->after('id');
            }
            if (!Schema::hasColumn('customers', 'credit_enabled')) {
                $table->boolean('credit_enabled')->default(false)->after('customer_type');
            }
            if (!Schema::hasColumn('customers', 'credit_limit')) {
                $table->decimal('credit_limit', 12, 2)->default(0)->after('credit_enabled');
            }
            if (!Schema::hasColumn('customers', 'current_balance')) {
                $table->decimal('current_balance', 12, 2)->default(0)->after('credit_limit');
            }
            if (!Schema::hasColumn('customers', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        // ── Suppliers: branch_id ──
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        // ── Orders: dispatch, delivery, receipt_token, balance columns, branch_id ──
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'dispatch_method')) {
                $table->string('dispatch_method')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'tracking_id')) {
                $table->string('tracking_id')->nullable()->after('dispatch_method');
            }
            if (!Schema::hasColumn('orders', 'delivery_charges')) {
                $table->decimal('delivery_charges', 10, 2)->default(0)->after('discount');
            }
            if (!Schema::hasColumn('orders', 'receipt_token')) {
                $table->string('receipt_token')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('orders', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('orders', 'previous_balance')) {
                $table->decimal('previous_balance', 10, 2)->default(0)->after('paid_amount');
            }
            if (!Schema::hasColumn('orders', 'balance_amount')) {
                $table->decimal('balance_amount', 10, 2)->default(0)->after('previous_balance');
            }
            if (!Schema::hasColumn('orders', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        // Generate receipt tokens for existing orders that don't have one
        DB::table('orders')->whereNull('receipt_token')->orderBy('id')->each(function ($order) {
            DB::table('orders')->where('id', $order->id)->update([
                'receipt_token' => bin2hex(random_bytes(16)),
            ]);
        });

        // ── Payments: credit-related fields, payment_type ──
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_number')) {
                $table->string('payment_number')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('payments', 'payment_type')) {
                $table->string('payment_type')->default('order')->after('payment_number');
            }
            if (!Schema::hasColumn('payments', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('payments', 'payment_date')) {
                $table->date('payment_date')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_date');
            }
            if (!Schema::hasColumn('payments', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable()->after('reference_number');
            }
            if (!Schema::hasColumn('payments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });

        // Make payments.order_id nullable (for khata payments)
        try {
            DB::statement("ALTER TABLE payments MODIFY COLUMN order_id BIGINT UNSIGNED NULL");
        } catch (\Exception $e) {
            // Already nullable
        }

        // ── Users: branch_id ──
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        // ── Employees: branch_id ──
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        // ── Purchases: branch_id ──
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        // ── Inventory Logs: branch_id ──
        Schema::table('inventory_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_logs', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        // ── Categories: branch_id ──
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });


        // ═══════════════════════════════════════════════════════════
        // 3. DATA MIGRATIONS (assign default branch to all records)
        // ═══════════════════════════════════════════════════════════

        // Create default branch if none exists
        $defaultBranch = DB::table('branches')->first();
        if (!$defaultBranch) {
            $branchId = DB::table('branches')->insertGetId([
                'name'       => 'Almufeed Saqafti Markaz',
                'code'       => 'ASM',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $branchId = $defaultBranch->id;
        }

        // Assign default branch to all records that have NULL branch_id
        // NOTE: 'users' excluded — admin/manager users should keep branch_id=NULL
        //       so they can freely switch branches. Only employees get locked.
        $tables = ['orders', 'employees', 'attendances', 'payrolls',
                    'purchases', 'inventory_logs', 'customers', 'suppliers',
                    'products', 'categories'];

        foreach ($tables as $tbl) {
            if (Schema::hasColumn($tbl, 'branch_id')) {
                DB::table($tbl)->whereNull('branch_id')->update(['branch_id' => $branchId]);
            }
        }

        // Only lock employee-role users to the default branch (not admin/manager)
        if (Schema::hasColumn('users', 'branch_id')) {
            $adminManagerUserIds = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereIn('roles.name', ['admin', 'super_admin', 'manager'])
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->pluck('model_has_roles.model_id')
                ->toArray();

            DB::table('users')
                ->whereNull('branch_id')
                ->whereNotIn('id', $adminManagerUserIds)
                ->update(['branch_id' => $branchId]);
        }

        // Migrate product stock to branch_product_stock (for products not yet in there)
        $existingProductIds = DB::table('branch_product_stock')
            ->where('branch_id', $branchId)
            ->pluck('product_id')
            ->toArray();

        $products = DB::table('products')
            ->whereNotIn('id', $existingProductIds)
            ->get(['id', 'stock_quantity', 'reorder_level']);

        foreach ($products as $product) {
            DB::table('branch_product_stock')->insert([
                'branch_id'      => $branchId,
                'product_id'     => $product->id,
                'stock_quantity' => $product->stock_quantity ?? 0,
                'reorder_level'  => $product->reorder_level ?? 10,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }


        // ═══════════════════════════════════════════════════════════
        // 4. PERMISSIONS (add if they don't exist)
        // ═══════════════════════════════════════════════════════════

        // Create super_admin role if it doesn't exist
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web']
        );

        // Assign super_admin role to all users who have admin role (if they don't already have it)
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminUserIds = DB::table('model_has_roles')
                ->where('role_id', $adminRole->id)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('model_id')
                ->toArray();

            $alreadySuperAdmin = DB::table('model_has_roles')
                ->where('role_id', $superAdminRole->id)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('model_id')
                ->toArray();

            foreach ($adminUserIds as $userId) {
                if (!in_array($userId, $alreadySuperAdmin)) {
                    DB::table('model_has_roles')->insert([
                        'role_id'    => $superAdminRole->id,
                        'model_type' => 'App\\Models\\User',
                        'model_id'   => $userId,
                    ]);
                }
            }
        }

        // Add new permissions
        $newPermissions = [
            'manage credit', 'view credit dashboard', 'enable credit',
            'disable credit', 'collect credit payment', 'view credit statement',
            'export credit statement', 'view overdue report',
            'manage branches', 'view all branches',
            'manage ledger',
        ];

        // Gather ALL existing permissions to assign to super_admin
        $allPermissions = Permission::all();

        foreach ($newPermissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm, 'guard_name' => 'web']
            );
        }

        // Give ALL permissions to super_admin role
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        // Give new permissions to admin role too
        if ($adminRole) {
            foreach ($newPermissions as $perm) {
                $permission = Permission::where('name', $perm)->first();
                if ($permission && !$adminRole->hasPermissionTo($perm)) {
                    $adminRole->givePermissionTo($permission);
                }
            }
        }


        // ═══════════════════════════════════════════════════════════
        // 5. SEED LOOKUP TABLES (if empty)
        // ═══════════════════════════════════════════════════════════

        // Payment Methods
        if (DB::table('payment_methods')->count() === 0) {
            $paymentMethods = [
                ['name' => 'cash',      'label' => 'Cash',    'sort_order' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'jazzcash',  'label' => 'Jazz',    'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'easypaisa', 'label' => 'Easy',    'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'bank',      'label' => 'Bank',    'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'cod',       'label' => 'COD',     'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'pending',   'label' => 'Pending', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ];
            DB::table('payment_methods')->insert($paymentMethods);
        }

        // Dispatch Methods
        if (DB::table('dispatch_methods')->count() === 0) {
            $dispatchMethods = [
                ['name' => 'Self Pickup', 'has_tracking' => false, 'sort_order' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'By Bus',      'has_tracking' => false, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'TCS',         'has_tracking' => true,  'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Pak Post',    'has_tracking' => true,  'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'PostEx',      'has_tracking' => true,  'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ];
            DB::table('dispatch_methods')->insert($dispatchMethods);
        }

        // Units
        if (DB::table('units')->count() === 0) {
            $units = [
                ['name' => 'Piece',    'abbreviation' => 'pc',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Kilogram', 'abbreviation' => 'kg',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Gram',     'abbreviation' => 'g',   'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Dozen',    'abbreviation' => 'dz',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Meter',    'abbreviation' => 'm',   'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Liter',    'abbreviation' => 'L',   'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Box',      'abbreviation' => 'box', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Pack',     'abbreviation' => 'pk',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ];
            DB::table('units')->insert($units);
        }

        echo "\n✅ Safe deployment migration completed successfully!\n";
        echo "   - Default branch: Almufeed Saqafti Markaz (ID: {$branchId})\n";
        echo "   - All existing records assigned to default branch\n";
        echo "   - All new tables created\n";
        echo "   - All new columns added\n";
        echo "   - Permissions assigned\n";
        echo "   - Lookup tables seeded\n\n";
    }

    public function down(): void
    {
        // This is a one-way deployment migration.
        // Rolling back would risk data loss.
        // If needed, restore from backup.
    }
};
