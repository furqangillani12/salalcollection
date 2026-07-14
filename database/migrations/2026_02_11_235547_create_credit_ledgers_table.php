<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('credit_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('ledger_number')->unique(); // Format: LED-2024-0001
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->decimal('total_debit', 12, 2)->default(0); // Total purchases on credit
            $table->decimal('total_credit', 12, 2)->default(0); // Total payments made
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->default(0);
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'closed'])->default('active');
            $table->date('last_transaction_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('customer_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_ledgers');
    }
};