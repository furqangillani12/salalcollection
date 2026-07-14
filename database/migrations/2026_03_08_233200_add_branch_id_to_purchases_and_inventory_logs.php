<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('purchases', 'branch_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('inventory_logs', 'branch_id')) {
            Schema::table('inventory_logs', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        // Set existing records to the default branch
        $defaultBranch = DB::table('branches')->first();
        if ($defaultBranch) {
            DB::table('purchases')->whereNull('branch_id')->update(['branch_id' => $defaultBranch->id]);
            DB::table('inventory_logs')->whereNull('branch_id')->update(['branch_id' => $defaultBranch->id]);
        }
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });

        Schema::table('inventory_logs', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_logs', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });
    }
};
