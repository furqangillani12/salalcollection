<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'original_price')) {
                $table->decimal('original_price', 10, 2)->nullable()->after('unit_price');
            }
            if (!Schema::hasColumn('order_items', 'line_discount')) {
                $table->decimal('line_discount', 10, 2)->default(0)->after('original_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'line_discount']);
        });
    }
};
