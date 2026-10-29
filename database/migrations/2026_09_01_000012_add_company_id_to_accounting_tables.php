<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['clients', 'incomes', 'expenses', 'vouchers'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['clients', 'incomes', 'expenses', 'vouchers'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
