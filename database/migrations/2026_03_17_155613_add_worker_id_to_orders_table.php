<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    if (!Schema::hasColumn('orders', 'worker_id')) {

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('worker_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('workers')
                  ->nullOnDelete();
        });

    }
}

public function down(): void
{
    if (Schema::hasColumn('orders', 'worker_id')) {

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['worker_id']);
            $table->dropColumn('worker_id');
        });

    }
}
};
