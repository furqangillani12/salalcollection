<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Storefront control + bank-account details for payment methods, and a
 * show-on-website + courier-logo for dispatch methods. All additive; POS keeps
 * using these tables exactly as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_methods', 'show_on_website')) $table->boolean('show_on_website')->default(false)->after('is_active');
            if (!Schema::hasColumn('payment_methods', 'is_cod'))          $table->boolean('is_cod')->default(false)->after('show_on_website');
            if (!Schema::hasColumn('payment_methods', 'account_title'))   $table->string('account_title')->nullable()->after('is_cod');
            if (!Schema::hasColumn('payment_methods', 'account_number'))  $table->string('account_number')->nullable()->after('account_title');
            if (!Schema::hasColumn('payment_methods', 'bank_name'))       $table->string('bank_name')->nullable()->after('account_number');
            if (!Schema::hasColumn('payment_methods', 'instructions'))    $table->text('instructions')->nullable()->after('bank_name');
        });

        Schema::table('dispatch_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_methods', 'show_on_website')) $table->boolean('show_on_website')->default(true)->after('is_active');
            if (!Schema::hasColumn('dispatch_methods', 'logo'))            $table->string('logo')->nullable()->after('show_on_website');
        });

        // Keep the storefront working out of the box: surface COD + Bank, and
        // flag COD so it never asks for a payment screenshot.
        DB::table('payment_methods')->whereIn('name', ['cod', 'bank'])->update(['show_on_website' => true]);
        DB::table('payment_methods')->where('name', 'cod')->update(['is_cod' => true]);
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            foreach (['show_on_website', 'is_cod', 'account_title', 'account_number', 'bank_name', 'instructions'] as $c) {
                if (Schema::hasColumn('payment_methods', $c)) $table->dropColumn($c);
            }
        });
        Schema::table('dispatch_methods', function (Blueprint $table) {
            foreach (['show_on_website', 'logo'] as $c) {
                if (Schema::hasColumn('dispatch_methods', $c)) $table->dropColumn($c);
            }
        });
    }
};
