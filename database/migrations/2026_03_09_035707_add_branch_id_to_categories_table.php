<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'branch_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        $defaultBranch = DB::table('branches')->first();
        if ($defaultBranch && Schema::hasColumn('categories', 'branch_id')) {
            DB::table('categories')->whereNull('branch_id')->update(['branch_id' => $defaultBranch->id]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'branch_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
    }
};
