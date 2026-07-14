<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema may already be at target state on live (seeded from dump). Guard with try/catch.
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('stock_quantity', 10, 2)->default(0)->change();
                $table->integer('reorder_level')->default(0)->change();
            });
        } catch (\Throwable $e) {
            // Already at target type — skip.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('stock_quantity')->default(0)->change();
                $table->integer('reorder_level')->default(10)->change();
            });
        } catch (\Throwable $e) {
            // Already at target type — skip.
        }
    }
};
