<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropUnique(['batch_number']);
            $table->unique(['product_id', 'batch_number']);
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'batch_number']);
            $table->unique('batch_number');
        });
    }
};
