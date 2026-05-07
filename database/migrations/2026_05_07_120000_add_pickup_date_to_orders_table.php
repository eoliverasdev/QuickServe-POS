<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->date('pickup_date')->nullable()->after('pickup_time');
        });

        // Backfill: per als encàrrecs ja existents agafem la data de creació
        // com a "pickup_date" perquè la numeració per dia es mantingui
        // coherent amb la lògica anterior.
        DB::table('orders')
            ->where('is_preorder', true)
            ->whereNull('pickup_date')
            ->update(['pickup_date' => DB::raw('DATE(created_at)')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pickup_date');
        });
    }
};
