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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Relació amb el treballador (IMPORTANT!)
            $table->foreignId('worker_id')->constrained('workers')->onDelete('cascade');
            // El preu total de la comanda amb IVA
            $table->decimal('total_price', 10, 2);
            // 'Efectiu' o 'Targeta'
            $table->string('payment_method')->default('Targeta');
            // 'Pagat' o 'Pendent'
            $table->string('status')->default('Pagat'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};