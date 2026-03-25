<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('name');

        $products = [
            // --- 1. CARNS ---
            ['name' => 'Pollastre', 'cats' => ['Carns'], 'price' => 12.80, 'is_gluten_free' => true, 'image_path' => 'images/carns/pollastre.jpg'],
            ['name' => '1/2 Pollastre', 'cats' => ['Carns'], 'price' => 6.50, 'is_gluten_free' => true, 'image_path' => 'images/carns/migpollastre.jpg'],
            ['name' => 'Conill', 'cats' => ['Carns'], 'price' => 18.00, 'is_gluten_free' => true, 'image_path' => 'images/carns/conill.jpg'],
            ['name' => 'Espatlla Xai', 'cats' => ['Carns'], 'price' => 20.00, 'is_gluten_free' => true, 'image_path' => 'images/carns/xai.jpg'],
            ['name' => 'Picantó', 'cats' => ['Carns'], 'price' => 5.50, 'is_gluten_free' => true, 'image_path' => 'images/carns/picanto.jpg'],
            ['name' => 'Costelló Porc', 'cats' => ['Carns'], 'price' => 7.50, 'is_gluten_free' => true, 'image_path' => 'images/carns/costella.jpg'],
            ['name' => 'Botifarra', 'cats' => ['Carns'], 'price' => 2.80, 'is_gluten_free' => true, 'image_path' => 'images/carns/butifarra.jpg'],
            ['name' => 'Xoriço', 'cats' => ['Carns'], 'price' => 2.80, 'is_gluten_free' => true, 'image_path' => 'images/carns/xoric.jpg'],
            ['name' => 'Galta Porc', 'cats' => ['Carns'], 'price' => 3.60, 'is_gluten_free' => true, 'image_path' => 'images/carns/galta.jpg'],
            ['name' => 'Cuixa d\'Ànec', 'cats' => ['Carns'], 'price' => 4.80, 'is_gluten_free' => true, 'image_path' => 'images/carns/anec.jpg'],
            ['name' => 'Xurrasc', 'cats' => ['Carns'], 'price' => 7.00, 'is_gluten_free' => true, 'image_path' => 'images/carns/xurrasco.jpg'],
            ['name' => 'Peus Porc', 'cats' => ['Carns'], 'price' => 7.30, 'is_gluten_free' => true, 'image_path' => 'images/guisats/peus_porc.jpg'],
            ['name' => 'Mandonguilles', 'cats' => ['Carns'], 'price' => 6.50, 'is_gluten_free' => false, 'image_path' => 'images/guisats/mandonguilles.jpg'],
            ['name' => 'Vedella Bolets', 'cats' => ['Carns'], 'price' => 7.80, 'is_gluten_free' => true, 'image_path' => 'images/guisats/vedella.jpg'],
            ['name' => 'Aletes', 'cats' => ['Carns'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/complements/aletes.jpg'],
            ['name' => 'Garri', 'cats' => ['Carns'], 'price' => 35.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Cabrit', 'cats' => ['Carns'], 'price' => 25.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Galta al Forn', 'cats' => ['Carns'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/carns/galta.jpg'],
            ['name' => 'Pinxo Pollo', 'cats' => ['Carns'], 'price' => 2.50, 'is_gluten_free' => true, 'image_path' => 'images/complements/pinxopollastre.jpg'],

            // --- 2. ENTRANT FREDS ---
            ['name' => 'Pastís Tonyina', 'cats' => ['Entrants Freds'], 'price' => 6.90, 'is_gluten_free' => false, 'image_path' => 'images/complements/tonyina.jpg'],
            ['name' => 'Escalivada', 'cats' => ['Entrants Freds'], 'price' => 6.50, 'is_gluten_free' => true, 'image_path' => 'images/entrants/escalivada.jpg'],
            ['name' => 'Esqueixada', 'cats' => ['Entrants Freds'], 'price' => 6.50, 'is_gluten_free' => true, 'image_path' => 'images/entrants/esqueixadabacalla.jpg'],
            ['name' => 'Ensaladilla', 'cats' => ['Entrants Freds'], 'price' => 3.90, 'is_gluten_free' => false, 'image_path' => 'images/entrants/ensaladilla.jpg'],
            ['name' => 'Amanida Pasta', 'cats' => ['Entrants Freds'], 'price' => 5.70, 'is_gluten_free' => false, 'image_path' => 'images/amanides/amanidapasta.jpg'],
            ['name' => 'Amanida Arròs', 'cats' => ['Entrants Freds'], 'price' => 5.70, 'is_gluten_free' => true, 'image_path' => 'images/amanides/amanida_arros.jpg'],
            ['name' => 'Amanida Formatge', 'cats' => ['Entrants Freds'], 'price' => 5.80, 'is_gluten_free' => true, 'image_path' => 'images/amanides/amanidaformatge.jpg'],
            ['name' => 'Ous Farcits', 'cats' => ['Entrants Freds'], 'price' => 6.80, 'is_gluten_free' => true, 'image_path' => 'images/entrants/ousfarcits.jpg'],
            ['name' => 'Gaspatx', 'cats' => ['Entrants Freds'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/complements/gazpacho.jpg'],
            ['name' => 'Amanida Verda', 'cats' => ['Entrants Freds'], 'price' => 4.80, 'is_gluten_free' => true, 'image_path' => 'images/amanides/amanidaverda.jpg'],
            ['name' => 'Cargol', 'cats' => ['Entrants Freds'], 'price' => 10.80, 'is_gluten_free' => false, 'image_path' => 'images/entrants/cargolsllauna.jpg'],
            ['name' => 'Cargol Grans', 'cats' => ['Entrants Freds'], 'price' => 12.50, 'is_gluten_free' => false, 'image_path' => 'images/entrants/cargolsllauna.jpg'],
            ['name' => 'Macarrons', 'cats' => ['Entrants Freds'], 'price' => 5.70, 'is_gluten_free' => false, 'image_path' => 'images/entrants/macarrons.jpg'],
            ['name' => 'Trinxat', 'cats' => ['Entrants Freds'], 'price' => 6.70, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Brou', 'cats' => ['Entrants Freds'], 'price' => 4.30, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Spaguetis Carbonara', 'cats' => ['Entrants Freds'], 'price' => 5.30, 'is_gluten_free' => false, 'image_path' => 'images/entrants/espaguetis.jpg'],
            ['name' => 'Pop a la Gallega', 'cats' => ['Entrants Freds'], 'price' => 12.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],

            // --- 3. ENTRANT CALENTS ---
            ['name' => '6 Canelons', 'cats' => ['Entrants Calents'], 'price' => 9.50, 'is_gluten_free' => false, 'image_path' => 'images/entrants/canelontres.jpg'],
            ['name' => '12 Canelons', 'cats' => ['Entrants Calents'], 'price' => 19.00, 'is_gluten_free' => false, 'image_path' => 'images/entrants/canelonsis.jpg'],
            ['name' => 'Albergínia Farcida', 'cats' => ['Entrants Calents'], 'price' => 6.70, 'is_gluten_free' => false, 'image_path' => 'images/entrants/alberginiafarcida.jpg'],
            ['name' => 'Fideuà', 'cats' => ['Entrants Calents'], 'price' => 7.20, 'is_gluten_free' => false, 'image_path' => 'images/entrants/fideua.jpg'],
            ['name' => 'Truita Patata', 'cats' => ['Entrants Calents'], 'price' => 7.50, 'is_gluten_free' => true, 'image_path' => 'images/entrants/truita.jpg'],
            ['name' => '1/2 Truita Patata', 'cats' => ['Entrants Calents'], 'price' => 4.00, 'is_gluten_free' => true, 'image_path' => 'images/entrants/truita.jpg'],
            ['name' => 'Arròs Verdura', 'cats' => ['Entrants Calents'], 'price' => 6.80, 'is_gluten_free' => true, 'image_path' => 'images/entrants/arrosverdures.jpg'],
            ['name' => 'Noodles Asiàtic', 'cats' => ['Entrants Calents'], 'price' => 7.20, 'is_gluten_free' => false, 'image_path' => 'images/entrants/noddles.jpg'],
            ['name' => 'Caneló Bolets', 'cats' => ['Entrants Calents'], 'price' => 2.00, 'is_gluten_free' => false, 'image_path' => 'images/entrants/canelontres.jpg'],
            ['name' => 'Patates amb carn', 'cats' => ['Entrants Calents'], 'price' => 6.70, 'is_gluten_free' => false, 'image_path' => 'images/complements/patatacarn.jpg'],
            ['name' => 'Lassanya', 'cats' => ['Entrants Calents'], 'price' => 5.90, 'is_gluten_free' => false, 'image_path' => 'images/entrants/lasanya.jpg'],

            // --- 4. PEIX ---
            ['name' => 'Sèpia amb Pèsols', 'cats' => ['Peix'], 'price' => 6.80, 'is_gluten_free' => true, 'image_path' => 'images/guisats/sepia.jpg'],
            ['name' => 'Rap al Forn', 'cats' => ['Peix'], 'price' => 12.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Bacallà Xamfaina', 'cats' => ['Peix'], 'price' => 7.20, 'is_gluten_free' => true, 'image_path' => 'images/guisats/bacallasamfaina.jpg'],
            ['name' => 'Bacallà Mouselina', 'cats' => ['Peix'], 'price' => 7.90, 'is_gluten_free' => true, 'image_path' => 'images/guisats/bacallamuselina.jpg'],
            ['name' => 'Calamars Farcits', 'cats' => ['Peix'], 'price' => 8.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Salmó Llimona', 'cats' => ['Peix'], 'price' => 9.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Pebrots Piquillo', 'cats' => ['Peix'], 'price' => 6.80, 'is_gluten_free' => true, 'image_path' => 'images/entrants/pebrotspiquillo.jpg'],

            // --- 5. COMPLEMENTS ---
            ['name' => 'Patates Fregides', 'cats' => ['Complements'], 'price' => 2.20, 'is_gluten_free' => true, 'image_path' => 'images/complements/fregides.jpg'],
            ['name' => 'Patates Allioli', 'cats' => ['Complements'], 'price' => 3.20, 'is_gluten_free' => true, 'image_path' => 'images/complements/allioli.jpg'],
            ['name' => 'Patates Braves', 'cats' => ['Complements'], 'price' => 3.20, 'is_gluten_free' => true, 'image_path' => 'images/complements/braves.jpg'],
            ['name' => 'Patates d\'Olot', 'cats' => ['Complements'], 'price' => 1.40, 'is_gluten_free' => false, 'image_path' => 'images/complements/patataolot.jpg'],
            ['name' => 'Patata Alli Juiver', 'cats' => ['Complements'], 'price' => 0.60, 'is_gluten_free' => true, 'image_path' => 'images/complements/all_julivert.jpg'],
            ['name' => 'Allioli', 'cats' => ['Complements'], 'price' => 1.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Croquetes', 'cats' => ['Complements'], 'price' => 4.70, 'is_gluten_free' => false, 'image_path' => 'images/complements/croquetes.jpg'],
            ['name' => 'Calama Romana', 'cats' => ['Complements'], 'price' => 5.00, 'is_gluten_free' => false, 'image_path' => 'images/complements/calamar.jpg'],
            ['name' => 'Carbasso Arrebossat', 'cats' => ['Complements'], 'price' => 3.50, 'is_gluten_free' => false, 'image_path' => 'images/default.jpg'],
            ['name' => 'Fingers Pollo', 'cats' => ['Complements'], 'price' => 4.50, 'is_gluten_free' => false, 'image_path' => 'images/default.jpg'],
            ['name' => 'Maionesa / Ketchup / BBQ', 'cats' => ['Complements'], 'price' => 0.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Rústica', 'cats' => ['Complements'], 'price' => 2.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Xips Gran', 'cats' => ['Complements'], 'price' => 3.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Xips Palla', 'cats' => ['Complements'], 'price' => 2.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Coberts', 'cats' => ['Complements'], 'price' => 0.50, 'is_gluten_free' => false, 'image_path' => 'images/default.jpg'],
            ['name' => 'Salsa Brava', 'cats' => ['Complements'], 'price' => 1.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Morro de Porc', 'cats' => ['Complements'], 'price' => 3.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => '2 Carxofes', 'cats' => ['Complements'], 'price' => 3.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Plat 4 Carxofes', 'cats' => ['Complements'], 'price' => 5.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],

            // --- 6. POSTRES ---
            ['name' => 'Gelat Tarrina Petita', 'cats' => ['Postres'], 'price' => 2.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Gelat Tarrina Gran', 'cats' => ['Postres'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Tronc Nadal Petit', 'cats' => ['Postres'], 'price' => 12.00, 'is_gluten_free' => false, 'image_path' => 'images/default.jpg'],
            ['name' => 'Tronc Nadal Gran', 'cats' => ['Postres'], 'price' => 22.00, 'is_gluten_free' => false, 'image_path' => 'images/default.jpg'],

            // --- 7. BEGUDES ---
            ['name' => 'Aigua 1,5L', 'cats' => ['Begudes'], 'price' => 1.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Aigua 0,50cl', 'cats' => ['Begudes'], 'price' => 1.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Aigua Gas', 'cats' => ['Begudes'], 'price' => 1.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Gasosa', 'cats' => ['Begudes'], 'price' => 1.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Coca Cola 2L', 'cats' => ['Begudes'], 'price' => 2.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Fanta 2L', 'cats' => ['Begudes'], 'price' => 2.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Llauna Refresc', 'cats' => ['Begudes'], 'price' => 1.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Vi Taula', 'cats' => ['Begudes'], 'price' => 3.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Vi Cresta Rosa', 'cats' => ['Begudes'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Blanc Pescador', 'cats' => ['Begudes'], 'price' => 5.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Vermut Padró', 'cats' => ['Begudes'], 'price' => 6.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Vi Rioja', 'cats' => ['Begudes'], 'price' => 7.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Xibeca', 'cats' => ['Begudes'], 'price' => 2.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Estrella', 'cats' => ['Begudes'], 'price' => 2.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Cervesa Sense Gluten', 'cats' => ['Begudes', 'Sense Gluten'], 'price' => 2.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Cava Brut', 'cats' => ['Begudes'], 'price' => 8.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],

            // --- 8. SENSE GLUTEN ---
            ['name' => 'Croquetes S/Gluten (5U)', 'cats' => ['Sense Gluten'], 'price' => 5.50, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/croquetes_sg.jpg'],
            ['name' => 'Fingers Pollo S/Gluten', 'cats' => ['Sense Gluten'], 'price' => 5.95, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/fingers_sg.jpg'],
            ['name' => 'Patates Olot S/Gluten', 'cats' => ['Sense Gluten'], 'price' => 1.90, 'is_gluten_free' => true, 'image_path' => 'images/complements/patataolot.jpg'],
            ['name' => 'Pastís Individual', 'cats' => ['Sense Gluten', 'Postres'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Caneló Peix Ànec', 'cats' => ['Sense Gluten'], 'price' => 2.50, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Caneló Sense Gluten', 'cats' => ['Sense Gluten'], 'price' => 2.00, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/canelons_sg.jpg'],
            ['name' => 'Lassanya S/Gluten', 'cats' => ['Sense Gluten'], 'price' => 6.95, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/lassanya_sg.jpg'],

            // --- 9. NADAL ---
            ['name' => 'Caneló Peix', 'cats' => ['Nadal'], 'price' => 2.50, 'is_gluten_free' => false, 'image_path' => 'images/default.jpg'],
            ['name' => 'Caneló Ànec Foie', 'cats' => ['Nadal'], 'price' => 3.00, 'is_gluten_free' => false, 'image_path' => 'images/default.jpg'],
            ['name' => 'Pastís Llamàntol', 'cats' => ['Nadal'], 'price' => 15.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Còctel de Gambes', 'cats' => ['Nadal'], 'price' => 12.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Plat Pates Nadal', 'cats' => ['Nadal'], 'price' => 18.00, 'is_gluten_free' => false, 'image_path' => 'images/default.jpg'],
            ['name' => 'Melós Vedella Bolets', 'cats' => ['Nadal'], 'price' => 14.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Pollast Gambes', 'cats' => ['Nadal'], 'price' => 16.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Cuixa Pollo Farcit', 'cats' => ['Nadal'], 'price' => 12.00, 'is_gluten_free' => false, 'image_path' => 'images/default.jpg'],
            ['name' => 'Espatlla Xai Forn', 'cats' => ['Nadal'], 'price' => 22.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Cua Rap Patates', 'cats' => ['Nadal'], 'price' => 18.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Ànec Guarnició', 'cats' => ['Nadal'], 'price' => 15.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Llaunes Persona', 'cats' => ['Nadal'], 'price' => 5.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
            ['name' => 'Refresc Gran Persona', 'cats' => ['Nadal'], 'price' => 3.00, 'is_gluten_free' => true, 'image_path' => 'images/default.jpg'],
        ];

        // Process products
        foreach ($products as $p) {
            $product = Product::updateOrCreate(
                ['name' => $p['name']],
                [
                    'price' => $p['price'],
                    'is_gluten_free' => $p['is_gluten_free'],
                    'image_path' => $p['image_path'],
                ]
            );

            $categoryIds = [];
            foreach ($p['cats'] as $catName) {
                if (isset($categories[$catName])) {
                    $categoryIds[] = $categories[$catName]->id;
                }
            }
            $product->categories()->sync($categoryIds);
        }
    }
}