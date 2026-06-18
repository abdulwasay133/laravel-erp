<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->enum('normal_balance', ['debit', 'credit'])->default('debit')->after('subtype');
            $table->boolean('is_posting')->default(false)->after('level');
            $table->boolean('is_system')->default(false)->after('is_posting');
        });
    }

    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropColumn(['normal_balance', 'is_posting', 'is_system']);
        });
    }
};
