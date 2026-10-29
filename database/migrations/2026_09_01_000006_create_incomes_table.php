<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->date('received_on');
            $table->string('category')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
