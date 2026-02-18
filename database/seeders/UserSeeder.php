<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $noms = ['Meri', 'Roc', 'Sergi', 'Xènia', 'Robert', 'Edu'];

        foreach ($noms as $nom) {
            User::create([
                'name' => $nom,
                'email' => strtolower($nom) . '@lacresta.com', // Laravel demana un email per defecte
                'password' => Hash::make('password'), // Una contrasenya genèrica
            ]);
        }
    }
}