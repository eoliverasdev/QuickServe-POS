<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    protected $fillable = ['name'];

    // Per si algun dia vols saber totes les comandes d'un treballador
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}