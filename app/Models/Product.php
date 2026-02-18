<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id', // Guardem l'ID de la taula categories
        'price',
        'is_gluten_free',
        'description',
        'image_path'
    ];

    public function categories()
    {
        // Un producte pertany a una categoria
        return $this->belongsToMany(Category::class);
    }
}