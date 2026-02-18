<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Worker; // <--- Afegeix això per evitar la línia vermella!

class WorkerSeeder extends Seeder
{
    public function run(): void
    {
        $noms = ['Meri', 'Roc', 'Sergi', 'Xènia', 'Robert', 'Edu'];
        foreach ($noms as $nom) {
            Worker::create(['name' => $nom]);
        }
    }
}