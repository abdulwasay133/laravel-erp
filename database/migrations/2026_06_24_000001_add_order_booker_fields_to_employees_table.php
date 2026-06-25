<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('is_order_booker')->default(false)->after('status');
            $table->string('commission_type', 50)->default('fixed_percent')->after('is_order_booker');
            $table->decimal('commission_rate', 8, 2)->default(0)->after('commission_type');
            $table->string('territory', 255)->nullable()->after('commission_rate');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['is_order_booker', 'commission_type', 'commission_rate', 'territory']);
        });
    }
};
