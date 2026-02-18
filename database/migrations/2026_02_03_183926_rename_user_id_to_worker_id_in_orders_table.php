<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Reanomenem la columna de user_id a worker_id
            $table->renameColumn('user_id', 'worker_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Si tirem enrere la migració, desfem el canvi
            $table->renameColumn('worker_id', 'user_id');
        });
    }
};