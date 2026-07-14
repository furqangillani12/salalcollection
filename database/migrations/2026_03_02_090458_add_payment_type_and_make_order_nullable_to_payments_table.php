<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Make order_id nullable so we can record payments WITHOUT an order
            // (e.g. customer paying off their khata balance)
            $table->foreignId('order_id')->nullable()->change();

            // Add a type column to distinguish order payments vs khata payments
            if (!Schema::hasColumn('payments', 'payment_type')) {
                $table->string('payment_type')->default('order')->after('payment_number');
                // Values: 'order' = payment for a specific order
                //         'khata' = customer paying off their running balance
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable(false)->change();
            $table->dropColumn('payment_type');
        });
    }
};