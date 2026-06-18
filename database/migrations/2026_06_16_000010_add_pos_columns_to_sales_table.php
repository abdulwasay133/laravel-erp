<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('pos_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $table->boolean('is_pos')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['pos_transaction_id']);
            $table->dropForeign(['pos_session_id']);
            $table->dropColumn(['pos_transaction_id', 'pos_session_id', 'is_pos']);
        });
    }
};
