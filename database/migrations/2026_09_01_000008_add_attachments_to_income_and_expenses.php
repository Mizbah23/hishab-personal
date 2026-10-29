<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table): void {
            $table->string('attachment_path')->nullable()->after('notes');
            $table->string('attachment_name')->nullable()->after('attachment_path');
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->string('attachment_path')->nullable()->after('notes');
            $table->string('attachment_name')->nullable()->after('attachment_path');
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table): void {
            $table->dropColumn(['attachment_path', 'attachment_name']);
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropColumn(['attachment_path', 'attachment_name']);
        });
    }
};
