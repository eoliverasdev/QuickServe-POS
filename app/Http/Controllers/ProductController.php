<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Worker; // Importa el nou model

class ProductController extends Controller
{
    public function index()
    {
        return view('tpv.index', [
            'products' => Product::with('categories')->get(),
            'categories' => Category::all(),
            'workers' => Worker::all() // Enviem els 6 treballadors (Meri, Roc, etc.)
        ]);
    }
}
