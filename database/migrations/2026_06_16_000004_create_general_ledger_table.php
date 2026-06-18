<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chart_of_account_id')->constrained();
            $table->foreignId('journal_entry_id')->constrained();
            $table->foreignId('journal_entry_line_id')->constrained();
            $table->date('date');
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->string('voucher_no');
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['chart_of_account_id', 'date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_ledger');
    }
};
