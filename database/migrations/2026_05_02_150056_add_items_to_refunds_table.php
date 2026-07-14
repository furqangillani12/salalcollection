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
        Schema::table('refunds', function (Blueprint $table) {
            if (!Schema::hasColumn('refunds', 'items')) {
                $table->json('items')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('refunds', 'refund_number')) {
                $table->string('refund_number')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn(['items', 'refund_number']);
        });
    }
};
