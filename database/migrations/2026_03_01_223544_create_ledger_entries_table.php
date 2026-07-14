<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();                     // LED-202603-0001
            $table->date('entry_date');                                   // date of transaction
            $table->string('account_type');                               // sales, purchases, expenses, cash_in, cash_out, accounts_receivable, refunds
            $table->string('transaction_type');                           // sale, purchase, expense, refund, credit_sale, credit_payment, payroll
            $table->nullableMorphs('reference');                          // polymorphic: order, purchase, expense, refund, etc.
            $table->string('reference_number')->nullable();               // ORD-xxx, INV-xxx
            $table->string('description');
            $table->decimal('debit', 15, 2)->default(0);                 // money going out / cost
            $table->decimal('credit', 15, 2)->default(0);                // money coming in / income
            $table->string('payment_method')->nullable();                 // cash, card, mobile_money, credit
            $table->string('party_type')->nullable();                     // customer, supplier, employee
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('party_name')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['entry_date', 'account_type']);
            $table->index(['transaction_type']);
            $table->index(['party_type', 'party_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};