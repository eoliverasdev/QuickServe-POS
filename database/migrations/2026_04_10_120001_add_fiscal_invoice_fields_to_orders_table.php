<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fiscal_series', 32)->nullable();
            $table->unsignedBigInteger('fiscal_sequence')->nullable();
            $table->string('fiscal_full_number', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['fiscal_series', 'fiscal_sequence', 'fiscal_full_number']);
        });
    }
};
