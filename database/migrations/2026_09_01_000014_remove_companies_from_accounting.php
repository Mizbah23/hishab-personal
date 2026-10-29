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
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }

        Schema::dropIfExists('companies');
    }

    public function down(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        foreach (['clients', 'incomes', 'expenses', 'vouchers'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            });
        }
    }
};
