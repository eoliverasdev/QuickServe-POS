<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;

/**
 * Token per invalidar tickets aparcats al localStorage quan el catàleg (BD) canvia
 * de manera incompatible — p. ex. migrate:fresh --seed.
 */
final class ParkedTicketsSync
{
    public static function token(): string
    {
        if (! Schema::hasTable('products')) {
            return hash('sha256', config('app.url').'|no-products-table');
        }

        $maxUpdated = Product::query()->max('updated_at');
        $count = Product::query()->count();

        return hash('sha256', implode('|', [
            config('app.url'),
            (string) $maxUpdated,
            (string) $count,
        ]));
    }
}
