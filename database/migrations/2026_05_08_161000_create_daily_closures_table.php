<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closures', function (Blueprint $table) {
            $table->id();
            $table->date('business_date');
            $table->dateTime('period_start');
            $table->dateTime('period_end');
            $table->unsignedTinyInteger('iva_percent')->default(21);
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_brut', 10, 2)->default(0);
            $table->decimal('cash_total', 10, 2)->default(0);
            $table->decimal('card_total', 10, 2)->default(0);
            $table->decimal('base_imposable', 10, 2)->default(0);
            $table->decimal('iva_quota', 10, 2)->default(0);
            $table->decimal('ticket_avg', 10, 2)->default(0);
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('business_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closures');
    }
};

