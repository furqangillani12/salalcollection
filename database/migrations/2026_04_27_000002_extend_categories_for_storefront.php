<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categories')) return;

        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->string('slug', 191)->nullable()->after('name')->index();
            }
            if (!Schema::hasColumn('categories', 'photo')) {
                $table->string('photo')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('categories', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('branch_id')->index();
            }
            if (!Schema::hasColumn('categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('photo');
            }
            if (!Schema::hasColumn('categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
            if (!Schema::hasColumn('categories', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('categories')) return;
        Schema::table('categories', function (Blueprint $table) {
            foreach (['is_featured', 'is_active', 'sort_order', 'parent_id', 'photo', 'slug'] as $col) {
                if (Schema::hasColumn('categories', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
