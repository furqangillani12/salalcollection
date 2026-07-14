<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Add session column (default: morning)
            $table->enum('session', ['morning', 'evening', 'night'])
                ->default('morning')
                ->after('date');

            // Modify status column to add 'half_day'
            $table->enum('status', ['present', 'absent', 'late', 'on_leave', 'half_day'])
                ->default('present')
                ->change();

            // Add unique constraint for employee/date/session
            $table->unique(['employee_id', 'date', 'session'], 'unique_employee_date_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop unique constraint
            $table->dropUnique('unique_employee_date_session');

            // Drop session column
            $table->dropColumn('session');

            // Revert status column back (without half_day)
            $table->enum('status', ['present', 'absent', 'late', 'on_leave'])
                ->default('present')
                ->change();
        });
    }
};
