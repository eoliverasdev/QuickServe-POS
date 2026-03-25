<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Carns',
            'Entrants Freds',
            'Entrants Calents',
            'Peix',
            'Complements',
            'Postres',
            'Begudes',
            'Sense Gluten',
            'Nadal'
        ];

        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat
            ]);
        }
    }
}