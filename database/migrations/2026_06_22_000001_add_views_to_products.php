<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Lightweight popularity counter for storefront recommendations. Additive. */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'views')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('views')->default(0)->after('review_count');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'views')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('views');
            });
        }
    }
};
