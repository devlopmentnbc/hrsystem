<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->indexExists('shifts_group_employee', 'sg_employee_shifts_group_id_idx')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->index('shifts_group_id', 'sg_employee_shifts_group_id_idx');
            });
        }

        if (! $this->indexExists('shifts_group_employee', 'sg_employee_employee_id_idx')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->index('employee_id', 'sg_employee_employee_id_idx');
            });
        }

        if (! Schema::hasColumn('shifts_group_employee', 'effective_start_date')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->date('effective_start_date')->nullable()->after('employee_id');
            });
        }

        if (! Schema::hasColumn('shifts_group_employee', 'effective_end_date')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->date('effective_end_date')->nullable()->after('effective_start_date');
            });
        }

        if (! $this->indexExists('shifts_group_employee', 'sg_employee_effective_idx')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->index(['employee_id', 'effective_start_date', 'effective_end_date'], 'sg_employee_effective_idx');
            });
        }

        if (Schema::hasColumn('shifts_group_employee', 'effective_start_date')) {
            DB::table('shifts_group_employee')
                ->whereNull('effective_start_date')
                ->update([
                    'effective_start_date' => DB::raw('DATE(COALESCE(created_at, CURRENT_TIMESTAMP))'),
                ]);
        }

        if ($this->indexExists('shifts_group_employee', 'sg_employee_unique')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->dropUnique('sg_employee_unique');
            });
        }
    }

    public function down(): void
    {
        if (! $this->indexExists('shifts_group_employee', 'sg_employee_unique')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->unique(['shifts_group_id', 'employee_id'], 'sg_employee_unique');
            });
        }

        if ($this->indexExists('shifts_group_employee', 'sg_employee_effective_idx')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->dropIndex('sg_employee_effective_idx');
            });
        }

        if ($this->indexExists('shifts_group_employee', 'sg_employee_employee_id_idx')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->dropIndex('sg_employee_employee_id_idx');
            });
        }

        if ($this->indexExists('shifts_group_employee', 'sg_employee_shifts_group_id_idx')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->dropIndex('sg_employee_shifts_group_id_idx');
            });
        }

        if (Schema::hasColumn('shifts_group_employee', 'effective_end_date')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->dropColumn('effective_end_date');
            });
        }

        if (Schema::hasColumn('shifts_group_employee', 'effective_start_date')) {
            Schema::table('shifts_group_employee', function (Blueprint $table) {
                $table->dropColumn('effective_start_date');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};