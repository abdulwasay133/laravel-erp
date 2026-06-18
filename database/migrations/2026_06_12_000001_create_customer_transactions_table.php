<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->date('date')->index();
            $table->string('type')->nullable(); // invoice, payment, credit_note
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0); // amount owed by customer
            $table->decimal('credit', 15, 2)->default(0); // payments
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_transactions');
    }
};
