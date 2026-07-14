<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend customers table so the public storefront can authenticate them.
 * POS-created customers continue to work — these fields are nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers')) return;

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
            if (!Schema::hasColumn('customers', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('password');
            }
            if (!Schema::hasColumn('customers', 'remember_token')) {
                $table->string('remember_token', 100)->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('customers', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('customers', 'avatar')) {
                $table->string('avatar')->nullable()->after('last_login_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('customers')) return;
        Schema::table('customers', function (Blueprint $table) {
            foreach (['avatar', 'last_login_at', 'remember_token', 'email_verified_at', 'password'] as $col) {
                if (Schema::hasColumn('customers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
