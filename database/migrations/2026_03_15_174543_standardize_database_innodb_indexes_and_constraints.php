<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Standardizes the entire database:
 *   1. Converts every table from MyISAM → InnoDB (FK support, transactions, row-level locking).
 *   2. Enforces all foreign-key constraints that were silently ignored under MyISAM.
 *   3. Adds missing unique constraints for data integrity.
 *   4. Adds indexes on columns used in WHERE / ORDER BY clauses.
 */
return new class extends Migration
{
    // ── All application + framework tables ───────────────────────────────────
    private array $allTables = [
        'cache', 'cache_locks',
        'departments',
        'employee_details',
        'failed_jobs',
        'holidays',
        'job_batches',
        'jobs',
        'leave_balances',
        'leave_transactions',
        'migrations',
        'password_reset_tokens',
        'sessions',
        'site_settings',
        'users',
    ];

    public function up(): void
    {
        // ── Step 1: Convert all tables to InnoDB ──────────────────────────────
        // FK_CHECKS disabled so we can reorder without constraint errors.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->allTables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── Step 2: Foreign key constraints ───────────────────────────────────
        // All were declared in earlier migrations but never created because the
        // tables were MyISAM. We add them now using the same names Laravel would
        // have chosen so the down() rollback can drop them by name.

        // users → departments (department_id)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('department_id', 'users_department_id_foreign')
                  ->references('id')->on('departments')
                  ->nullOnDelete();

            $table->foreign('level1_manager_id', 'users_level1_manager_id_foreign')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->foreign('level2_manager_id', 'users_level2_manager_id_foreign')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });

        // employee_details → users
        Schema::table('employee_details', function (Blueprint $table) {
            $table->foreign('user_id', 'employee_details_user_id_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        // leave_balances → users
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->foreign('user_id', 'leave_balances_user_id_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        // leave_transactions → users
        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->foreign('user_id', 'leave_transactions_user_id_foreign')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        // holidays → departments
        Schema::table('holidays', function (Blueprint $table) {
            $table->foreign('department_id', 'holidays_department_id_foreign')
                  ->references('id')->on('departments')
                  ->nullOnDelete();
        });

        // ── Step 3: Unique constraints ─────────────────────────────────────────

        // employee_details: one row per user (1-to-1 with users)
        Schema::table('employee_details', function (Blueprint $table) {
            $table->unique('user_id', 'employee_details_user_id_unique');
        });

        // departments: code should be unique when present
        Schema::table('departments', function (Blueprint $table) {
            $table->unique('code', 'departments_code_unique');
        });

        // ── Step 4: Performance indexes ───────────────────────────────────────

        // users — frequently filtered by role and emp_status
        Schema::table('users', function (Blueprint $table) {
            $table->index('role',       'users_role_index');
            $table->index('emp_status', 'users_emp_status_index');
            $table->index('doj',        'users_doj_index');
            // Composite for the common "active employees in dept" query
            $table->index(['department_id', 'emp_status'], 'users_department_emp_status_index');
        });

        // departments — filtered by status on every listing
        Schema::table('departments', function (Blueprint $table) {
            $table->index('status', 'departments_status_index');
        });

        // holidays — queried by date and status constantly
        Schema::table('holidays', function (Blueprint $table) {
            $table->index('date',   'holidays_date_index');
            $table->index('status', 'holidays_status_index');
            $table->index('type',   'holidays_type_index');
            // Composite for "upcoming active holidays" query
            $table->index(['date', 'status'], 'holidays_date_status_index');
        });

        // leave_transactions — date used in range queries
        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->index('date', 'leave_transactions_date_index');
        });
    }

    public function down(): void
    {
        // ── Reverse Step 4: Drop indexes ──────────────────────────────────────
        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->dropIndex('leave_transactions_date_index');
        });

        Schema::table('holidays', function (Blueprint $table) {
            $table->dropIndex('holidays_date_status_index');
            $table->dropIndex('holidays_type_index');
            $table->dropIndex('holidays_status_index');
            $table->dropIndex('holidays_date_index');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex('departments_status_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_department_emp_status_index');
            $table->dropIndex('users_doj_index');
            $table->dropIndex('users_emp_status_index');
            $table->dropIndex('users_role_index');
        });

        // ── Reverse Step 3: Drop unique constraints ───────────────────────────
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique('departments_code_unique');
        });

        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropUnique('employee_details_user_id_unique');
        });

        // ── Reverse Step 2: Drop foreign keys ─────────────────────────────────
        Schema::table('leave_transactions', function (Blueprint $table) {
            $table->dropForeign('leave_transactions_user_id_foreign');
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->dropForeign('leave_balances_user_id_foreign');
        });

        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropForeign('employee_details_user_id_foreign');
        });

        Schema::table('holidays', function (Blueprint $table) {
            $table->dropForeign('holidays_department_id_foreign');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_level2_manager_id_foreign');
            $table->dropForeign('users_level1_manager_id_foreign');
            $table->dropForeign('users_department_id_foreign');
        });

        // ── Reverse Step 1: Revert all tables back to MyISAM ─────────────────
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->allTables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `{$table}` ENGINE=MyISAM");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
