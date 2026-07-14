<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // How much the customer actually paid on this order
            $table->decimal('paid_amount', 10, 2)->default(0)->after('total');
            // Customer's balance BEFORE this order (from previous transactions)
            $table->decimal('previous_balance', 10, 2)->default(0)->after('paid_amount');
            // Remaining balance on THIS order (total - paid_amount)
            $table->decimal('balance_amount', 10, 2)->default(0)->after('previous_balance');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'previous_balance', 'balance_amount']);
        });
    }
};