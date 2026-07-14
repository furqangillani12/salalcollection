<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique(); // Format: TXN-2024-0001
            $table->foreignId('credit_ledger_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('transaction_type', ['debit', 'credit']); // debit = purchase, credit = payment
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference_number')->nullable(); // Invoice number, Payment receipt
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->date('due_date')->nullable(); // For debit transactions
            $table->date('payment_date')->nullable(); // For credit transactions
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->json('items')->nullable(); // Store products purchased
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('remaining_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Indexes
            $table->index('customer_id');
            $table->index('credit_ledger_id');
            $table->index('order_id');
            $table->index('transaction_type');
            $table->index('payment_status');
            $table->index('due_date');
            $table->index('transaction_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_transactions');
    }
};