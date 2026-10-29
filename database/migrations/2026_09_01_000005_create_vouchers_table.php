<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table): void {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->string('type')->default('general');
            $table->date('voucher_date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->string('narration')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
