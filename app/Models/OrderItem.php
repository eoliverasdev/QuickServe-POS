<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price_at_sale'];

    // Relació: Aquest detall pertany a una comanda pare
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Relació: Aquest detall fa referència a un producte
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}