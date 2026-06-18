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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('ref_no')->unique();
            $table->date('order_date');
            $table->decimal('subtotal',    12, 2)->default(0);
            $table->decimal('discount',    12, 2)->default(0);
            $table->string('discount_type')->default('fixed'); // 'fixed' | 'percent'
            $table->decimal('tax_amount',  12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount',  12, 2)->default(0);
            $table->string('payment_method')->nullable(); // cash | bank
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'received', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
