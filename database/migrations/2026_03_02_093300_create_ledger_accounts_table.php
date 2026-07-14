<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Ledger Accounts (Chart of Accounts) ─────────────────────────────
        // Admin creates these manually: "Shop Rent", "Salary", "Sales", etc.
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();           // ACC-001
            $table->string('name');                             // Shop Rent, Electricity, Sales
            $table->string('type');                             // expense | income | asset | liability
            $table->string('category')->nullable();            // e.g. "Office Expenses", "Utilities"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Ledger Account Entries ───────────────────────────────────────────
        // Manual journal entries posted to any ledger account
        Schema::create('ledger_account_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();           // JV-202603-0001
            $table->foreignId('ledger_account_id')->constrained('ledger_accounts')->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('description');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('reference_type')->nullable();       // 'order','purchase','expense','manual'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable();     // ORD-xxx, INV-xxx
            $table->string('payment_method')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['ledger_account_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_account_entries');
        Schema::dropIfExists('ledger_accounts');
    }
};