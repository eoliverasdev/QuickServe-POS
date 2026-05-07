<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    // Camps que permetem omplir de cop
    // El worker_id ja el tenies ben posat!
    protected $fillable = [
        'total_price',
        'payment_method',
        'status',
        'worker_id',
        'is_preorder',
        'pickup_number',
        'pickup_time',
        'pickup_date',
        'customer_name',
        'fiscal_series',
        'fiscal_sequence',
        'fiscal_full_number',
    ];

    protected $casts = [
        'is_preorder' => 'boolean',
        'pickup_date' => 'date:Y-m-d',
    ];

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
        return $this->belongsTo(Worker::class)->withTrashed();
    }
}