<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    // Camps que permetem omplir de cop
    // El worker_id ja el tenies ben posat!
    protected $fillable = ['total_price', 'payment_method', 'status', 'worker_id'];

    /**
     * Relació: Una comanda té molts detalls (items)
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relació: Una comanda pertany a un treballador específico.
     * Canviem 'user' per 'worker' i apuntem al model Worker.
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}