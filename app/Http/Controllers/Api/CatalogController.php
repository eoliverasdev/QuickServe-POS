<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        $products = Product::query()
            ->with('categories:id,name')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock', 'image_path']);

        return response()->json([
            'categories' => $categories,
            'products' => $products->map(function (Product $product): array {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'stock' => $product->stock === null ? null : (int) $product->stock,
                    'image_path' => $product->image_path,
                    'category_ids' => $product->categories->pluck('id')->values(),
                    'category_names' => $product->categories->pluck('name')->values(),
                ];
            }),
        ]);
    }
}
