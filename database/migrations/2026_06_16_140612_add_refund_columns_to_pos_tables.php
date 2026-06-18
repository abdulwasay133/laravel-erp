<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->text('refund_reason')->nullable()->after('notes');
        });

        Schema::table('pos_transaction_items', function (Blueprint $table) {
            $table->decimal('refunded_quantity', 10, 2)->default(0)->after('cost');
        });
    }

    public function down(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropColumn('refund_reason');
        });

        Schema::table('pos_transaction_items', function (Blueprint $table) {
            $table->dropColumn('refunded_quantity');
        });
    }
};
