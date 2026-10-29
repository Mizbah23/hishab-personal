<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->decimal('opening_balance', 15, 2)->default(0)->after('notes');
            $table->date('opening_balance_date')->nullable()->after('opening_balance');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn(['opening_balance', 'opening_balance_date']);
        });
    }
};
