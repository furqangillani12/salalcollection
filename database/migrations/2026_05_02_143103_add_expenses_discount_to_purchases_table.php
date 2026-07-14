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
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'expenses')) {
                $table->json('expenses')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('purchases', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('expenses');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['expenses', 'discount']);
        });
    }
};
