<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a "show on website" toggle to categories and branches, mirroring the
 * existing products.show_on_website flag. Defaults to true so all existing
 * rows keep showing on the storefront exactly as before (POS unaffected).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'show_on_website')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('show_on_website')->default(true)->after('is_featured');
            });
        }

        if (!Schema::hasColumn('branches', 'show_on_website')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->boolean('show_on_website')->default(true)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'show_on_website')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('show_on_website');
            });
        }
        if (Schema::hasColumn('branches', 'show_on_website')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('show_on_website');
            });
        }
    }
};
