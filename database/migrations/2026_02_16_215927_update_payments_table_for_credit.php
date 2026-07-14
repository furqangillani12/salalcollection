<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('payments', 'payment_number')) {
                $table->string('payment_number')->unique()->after('id');
            }
            
            if (!Schema::hasColumn('payments', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete()->after('order_id');
            }
            
            if (!Schema::hasColumn('payments', 'payment_date')) {
                $table->date('payment_date')->nullable()->after('amount');
            }
            
            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_date');
            }
            
            if (!Schema::hasColumn('payments', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('payment_method');
            }
            
            if (!Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable()->after('reference_number');
            }
            
            if (!Schema::hasColumn('payments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            }
            
            // Rename columns if needed (from old structure to new)
            if (Schema::hasColumn('payments', 'method') && !Schema::hasColumn('payments', 'payment_method')) {
                $table->renameColumn('method', 'payment_method');
            }
            
            if (Schema::hasColumn('payments', 'reference') && !Schema::hasColumn('payments', 'reference_number')) {
                $table->renameColumn('reference', 'reference_number');
            }
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_number',
                'customer_id',
                'payment_date',
                'reference_number',
                'notes',
                'created_by'
            ]);
            
            // Rename back if needed
            if (Schema::hasColumn('payments', 'payment_method') && !Schema::hasColumn('payments', 'method')) {
                $table->renameColumn('payment_method', 'method');
            }
            
            if (Schema::hasColumn('payments', 'reference_number') && !Schema::hasColumn('payments', 'reference')) {
                $table->renameColumn('reference_number', 'reference');
            }
        });
    }
};