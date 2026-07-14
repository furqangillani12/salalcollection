<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_account', 50); // e.g. 'cash'
            $table->string('to_account', 50);   // e.g. 'bank'
            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->date('transferred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transfers');
    }
};
