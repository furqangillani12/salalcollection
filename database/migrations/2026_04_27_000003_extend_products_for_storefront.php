<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) return;

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'slug')) {
                $table->string('slug', 191)->nullable()->after('name')->index();
            }
            if (!Schema::hasColumn('products', 'summary')) {
                $table->string('summary')->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'gallery')) {
                $table->json('gallery')->nullable()->after('image');
            }
            if (!Schema::hasColumn('products', 'brand_id')) {
                $table->unsignedBigInteger('brand_id')->nullable()->after('category_id')->index();
            }
            if (!Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('products', 'show_on_website')) {
                $table->boolean('show_on_website')->default(true)->after('is_featured');
            }
            if (!Schema::hasColumn('products', 'condition_label')) {
                $table->enum('condition_label', ['default', 'new', 'hot', 'sale'])->default('default')->after('show_on_website');
            }
            if (!Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable();
            }
            if (!Schema::hasColumn('products', 'meta_description')) {
                $table->string('meta_description', 500)->nullable();
            }
            if (!Schema::hasColumn('products', 'avg_rating')) {
                $table->decimal('avg_rating', 3, 2)->default(0)->after('rank');
            }
            if (!Schema::hasColumn('products', 'review_count')) {
                $table->unsignedInteger('review_count')->default(0)->after('avg_rating');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products')) return;
        Schema::table('products', function (Blueprint $table) {
            foreach (['review_count', 'avg_rating', 'meta_description', 'meta_title', 'condition_label',
                      'show_on_website', 'is_featured', 'brand_id', 'gallery', 'summary', 'slug'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
