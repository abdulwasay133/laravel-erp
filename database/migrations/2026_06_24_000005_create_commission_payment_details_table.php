<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_payment_id')->constrained('commission_payments')->cascadeOnDelete();
            $table->foreignId('commission_id')->constrained('commissions')->cascadeOnDelete();
            $table->decimal('paid_amount', 18, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_payment_details');
    }
};
