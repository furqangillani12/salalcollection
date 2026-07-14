<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['customers', 'suppliers', 'products'] as $tbl) {
            if (Schema::hasTable($tbl) && !Schema::hasColumn($tbl, 'branch_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
                });
            }
        }

        $defaultBranch = DB::table('branches')->first();
        if ($defaultBranch) {
            foreach (['customers', 'suppliers', 'products'] as $tbl) {
                if (Schema::hasColumn($tbl, 'branch_id')) {
                    DB::table($tbl)->whereNull('branch_id')->update(['branch_id' => $defaultBranch->id]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['customers', 'suppliers', 'products'] as $tbl) {
            if (Schema::hasColumn($tbl, 'branch_id')) {
                Schema::table($tbl, function (Blueprint $t) {
                    $t->dropForeign(['branch_id']);
                    $t->dropColumn('branch_id');
                });
            }
        }
    }
};
