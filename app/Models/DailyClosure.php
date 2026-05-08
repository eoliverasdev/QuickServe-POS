<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosure extends Model
{
    protected $fillable = [
        'business_date',
        'period_start',
        'period_end',
        'iva_percent',
        'orders_count',
        'total_brut',
        'cash_total',
        'card_total',
        'base_imposable',
        'iva_quota',
        'ticket_avg',
        'closed_by_user_id',
    ];

    protected $casts = [
        'business_date' => 'date:Y-m-d',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'iva_percent' => 'integer',
        'orders_count' => 'integer',
        'total_brut' => 'float',
        'cash_total' => 'float',
        'card_total' => 'float',
        'base_imposable' => 'float',
        'iva_quota' => 'float',
        'ticket_avg' => 'float',
    ];

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }
}

