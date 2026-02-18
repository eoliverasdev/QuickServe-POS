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
            // El preu total de la comanda amb IVA
            $table->decimal('total_price', 10, 2);
            // 'Efectiu' o 'Targeta' per poder quadrar la caixa al final del dia
            $table->string('payment_method')->default('Tarjeta');
            // 'Pagat' o 'Pendent' (per si algú s'oblida la cartera i torna més tard)
            $table->string('status')->default('Pendent');
            // Relació opcional amb l'usuari (treballador) que fa la venda
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
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