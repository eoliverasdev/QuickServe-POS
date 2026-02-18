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
            // --- ENTRANTS I PRIMERS ---
            ['name' => 'Cargols', 'cats' => ['Entrants'], 'price' => 10.80, 'is_gluten_free' => false, 'image_path' => 'images/entrants/cargolsllauna.jpg'],
            ['name' => 'Esqueixada', 'cats' => ['Entrants'], 'price' => 6.50, 'is_gluten_free' => true, 'image_path' => 'images/entrants/esqueixadabacalla.jpg'],
            ['name' => 'Escalivada', 'cats' => ['Entrants'], 'price' => 6.50, 'is_gluten_free' => true, 'image_path' => 'images/entrants/escalivada.jpg'],
            ['name' => 'Ensaladilla', 'cats' => ['Entrants'], 'price' => 3.90, 'is_gluten_free' => false, 'image_path' => 'images/entrants/ensaladilla.jpg'],
            ['name' => 'Ous farcits', 'cats' => ['Entrants'], 'price' => 6.80, 'is_gluten_free' => true, 'image_path' => 'images/entrants/ousfarcits.jpg'],
            ['name' => 'Pastís de tonyina', 'cats' => ['Entrants'], 'price' => 6.90, 'is_gluten_free' => false, 'image_path' => 'images/complements/tonyina.jpg'],
            //['name' => 'Trinxat de la Cerdanya', 'cats' => ['Entrants'], 'price' => 6.70, 'is_gluten_free' => true, 'image_path' => 'images/entrants/trinxat.jpg'],
            //['name' => 'Brou', 'cats' => ['Entrants'], 'price' => 4.30, 'is_gluten_free' => true, 'image_path' => 'images/entrants/brou.jpg'],
            ['name' => 'Pebrots piquillo', 'cats' => ['Entrants'], 'price' => 6.80, 'is_gluten_free' => true, 'image_path' => 'images/entrants/pebrotspiquillo.jpg'],
            ['name' => 'Crema de verdures', 'cats' => ['Entrants'], 'price' => 3.40, 'is_gluten_free' => true, 'image_path' => 'images/entrants/creamverdures.jpg'],
            ['name' => 'Gaspatxo', 'cats' => ['Entrants'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/complements/gazpacho.jpg'],
            ['name' => 'Arròs de verdures', 'cats' => ['Entrants'], 'price' => 6.80, 'is_gluten_free' => true, 'image_path' => 'images/entrants/arrosverdures.jpg'],
            ['name' => 'Albergínia farcida', 'cats' => ['Entrants'], 'price' => 6.70, 'is_gluten_free' => false, 'image_path' => 'images/entrants/alberginiafarcida.jpg'],
            //['name' => 'Empanada Gallega', 'cats' => ['Entrants'], 'price' => 17.00, 'is_gluten_free' => false, 'image_path' => 'images/entrants/empanada.jpg'],
            ['name' => 'Truita', 'cats' => ['Entrants'], 'price' => 7.50, 'is_gluten_free' => true, 'image_path' => 'images/entrants/truita.jpg'],
            //['name' => 'Grana', 'cats' => ['Entrants'], 'price' => 2.90, 'is_gluten_free' => true, 'image_path' => 'images/entrants/grana.jpg'],
            ['name' => 'Patates amb carn gratinades', 'cats' => ['Entrants'], 'price' => 6.70, 'is_gluten_free' => false, 'image_path' => 'images/complements/patatacarn.jpg'],

            // --- AMANIDES ---
            ['name' => 'Amanida verda', 'cats' => ['Amanides'], 'price' => 4.80, 'is_gluten_free' => true, 'image_path' => 'images/amanides/amanidaverda.jpg'],
            ['name' => 'Amanida de formatge', 'cats' => ['Amanides'], 'price' => 5.80, 'is_gluten_free' => true, 'image_path' => 'images/amanides/amanidaformatge.jpg'],
            ['name' => 'Amanida de pasta', 'cats' => ['Amanides'], 'price' => 5.70, 'is_gluten_free' => false, 'image_path' => 'images/amanides/amanidapasta.jpg'],
            ['name' => 'Amanida d\'arròs', 'cats' => ['Amanides'], 'price' => 5.70, 'is_gluten_free' => true, 'image_path' => 'images/amanides/amanida_arros.jpg'],

            // --- Entrants ---
            ['name' => 'Fideuà', 'cats' => ['Entrants'], 'price' => 7.20, 'is_gluten_free' => false, 'image_path' => 'images/entrants/fideua.jpg'],
            ['name' => 'Espaguetis carbonara', 'cats' => ['Entrants'], 'price' => 5.30, 'is_gluten_free' => false, 'image_path' => 'images/entrants/espaguetis.jpg'],
            ['name' => 'Noddles asiàtics', 'cats' => ['Entrants'], 'price' => 7.20, 'is_gluten_free' => false, 'image_path' => 'images/entrants/noddles.jpg'],
            ['name' => 'Macarrons', 'cats' => ['Entrants'], 'price' => 5.70, 'is_gluten_free' => false, 'image_path' => 'images/entrants/macarrons.jpg'],
            ['name' => 'Lassanya de carn', 'cats' => ['Entrants'], 'price' => 5.90, 'is_gluten_free' => false, 'image_path' => 'images/entrants/lasanya.jpg'],
            ['name' => 'Lassanya de verdures', 'cats' => ['Entrants'], 'price' => 5.90, 'is_gluten_free' => false, 'image_path' => 'images/entrants/lasanya.jpg'],
            ['name' => '6 Canelons', 'cats' => ['Entrants'], 'price' => 9.50, 'is_gluten_free' => false, 'image_path' => 'images/entrants/canelontres.jpg'],
            ['name' => '12 Canelons', 'cats' => ['Entrants'], 'price' => 19.00, 'is_gluten_free' => false, 'image_path' => 'images/entrants/canelonsis.jpg'],
            ['name' => 'Fideus a la cassola', 'cats' => ['Entrants'], 'price' => 6.80, 'is_gluten_free' => false, 'image_path' => 'images/entrants/fideus_cassola.jpg'],
            //['name' => 'Pasta pollastre', 'cats' => ['Entrants'], 'price' => 5.90, 'is_gluten_free' => false, 'image_path' => 'images/entrants/pasta_pollastre.jpg'],

            // --- CARNS ---
            ['name' => 'Pollastre', 'cats' => ['Carn'], 'price' => 12.80, 'is_gluten_free' => true, 'image_path' => 'images/carns/pollastre.jpg'],
            ['name' => '1/2 Pollastre', 'cats' => ['Carn'], 'price' => 6.50, 'is_gluten_free' => true, 'image_path' => 'images/carns/migpollastre.jpg'],
            ['name' => 'Conill', 'cats' => ['Carn'], 'price' => 18.00, 'is_gluten_free' => true, 'image_path' => 'images/carns/conill.jpg'],
            ['name' => 'Paletilla de Cordero', 'cats' => ['Carn'], 'price' => 20.00, 'is_gluten_free' => true, 'image_path' => 'images/carns/xai.jpg'],
            ['name' => 'Cap de Llom', 'cats' => ['Carn'], 'price' => 25.00, 'is_gluten_free' => true, 'image_path' => 'images/carns/capdellom.jpg'],
            ['name' => 'Cuixa d\'Ànec', 'cats' => ['Carn'], 'price' => 4.80, 'is_gluten_free' => true, 'image_path' => 'images/carns/anec.jpg'],
            ['name' => 'Picantó', 'cats' => ['Carn'], 'price' => 5.50, 'is_gluten_free' => true, 'image_path' => 'images/carns/picanto.jpg'],
            ['name' => 'Butifarra', 'cats' => ['Carn'], 'price' => 2.80, 'is_gluten_free' => true, 'image_path' => 'images/carns/butifarra.jpg'],
            ['name' => 'Xoriç', 'cats' => ['Carn'], 'price' => 2.80, 'is_gluten_free' => true, 'image_path' => 'images/carns/xoric.jpg'],
            ['name' => 'Galta de porc', 'cats' => ['Carn'], 'price' => 3.60, 'is_gluten_free' => true, 'image_path' => 'images/carns/galta.jpg'],
            ['name' => 'Costella de porc', 'cats' => ['Carn'], 'price' => 7.50, 'is_gluten_free' => true, 'image_path' => 'images/carns/costella.jpg'],
            ['name' => 'Xurrasco de vedella', 'cats' => ['Carn'], 'price' => 7.00, 'is_gluten_free' => true, 'image_path' => 'images/carns/xurrasco.jpg'],
            ['name' => 'Pinxos de pollastre', 'cats' => ['Carn'], 'price' => 2.50, 'is_gluten_free' => true, 'image_path' => 'images/complements/pinxopollastre.jpg'],

            // --- GUISATS ---
            ['name' => 'Vedella amb bolets', 'cats' => ['Guisats'], 'price' => 7.80, 'is_gluten_free' => true, 'image_path' => 'images/guisats/vedella.jpg'],
            ['name' => 'Callos', 'cats' => ['Guisats'], 'price' => 5.20, 'is_gluten_free' => false, 'image_path' => 'images/guisats/callos.jpg'],
            ['name' => 'Bacallà musselina d\'all', 'cats' => ['Guisats'], 'price' => 7.90, 'is_gluten_free' => true, 'image_path' => 'images/guisats/bacallamuselina.jpg'],
            ['name' => 'Bacallà amb sanfaina', 'cats' => ['Guisats'], 'price' => 7.20, 'is_gluten_free' => true, 'image_path' => 'images/guisats/bacallasamfaina.jpg'],
            ['name' => 'Peus de porc', 'cats' => ['Guisats'], 'price' => 7.30, 'is_gluten_free' => true, 'image_path' => 'images/guisats/peus_porc.jpg'],
            ['name' => 'Mandonguilles', 'cats' => ['Guisats'], 'price' => 6.50, 'is_gluten_free' => false, 'image_path' => 'images/guisats/mandonguilles.jpg'],
            ['name' => 'Sípia amb pèsols', 'cats' => ['Guisats'], 'price' => 6.80, 'is_gluten_free' => true, 'image_path' => 'images/guisats/sepia.jpg'],

            // --- COMPLEMENTS ---
            ['name' => 'Patates fregides', 'cats' => ['Complements'], 'price' => 2.20, 'is_gluten_free' => true, 'image_path' => 'images/complements/fregides.jpg'],
            ['name' => 'Patates all i oli', 'cats' => ['Complements'], 'price' => 3.20, 'is_gluten_free' => true, 'image_path' => 'images/complements/allioli.jpg'],
            ['name' => 'Patates braves', 'cats' => ['Complements'], 'price' => 3.20, 'is_gluten_free' => true, 'image_path' => 'images/complements/braves.jpg'],
            ['name' => 'Patata d\'Olot', 'cats' => ['Complements'], 'price' => 1.40, 'is_gluten_free' => false, 'image_path' => 'images/complements/patataolot.jpg'],
            ['name' => 'Patates all i julivert', 'cats' => ['Complements'], 'price' => 0.60, 'is_gluten_free' => true, 'image_path' => 'images/complements/all_julivert.jpg'],
            ['name' => 'Calamars romana', 'cats' => ['Complements'], 'price' => 5.00, 'is_gluten_free' => false, 'image_path' => 'images/complements/calamar.jpg'],
            ['name' => 'Croquetes de carn', 'cats' => ['Complements'], 'price' => 4.70, 'is_gluten_free' => false, 'image_path' => 'images/complements/croquetes.jpg'],
            ['name' => 'Croquetes de formatge', 'cats' => ['Complements'], 'price' => 4.70, 'is_gluten_free' => false, 'image_path' => 'images/complements/formatge.jpg'],
            ['name' => 'Aletes adobades', 'cats' => ['Complements'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/complements/aletes.jpg'],

            // --- SENSE GLUTEN ---
            ['name' => 'Lassanya SG', 'cats' => ['Sense Gluten'], 'price' => 8.95, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/lassanya_sg.jpg'],
            ['name' => 'Canelons carn SG', 'cats' => ['Sense Gluten'], 'price' => 9.50, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/canelons_sg.jpg'],
            ['name' => 'Croquetes SG (5u)', 'cats' => ['Sense Gluten'], 'price' => 5.50, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/croquetes_sg.jpg'],
            ['name' => 'Patata d\'Olot SG', 'cats' => ['Sense Gluten'], 'price' => 1.90, 'is_gluten_free' => true, 'image_path' => 'images/complements/patataolot.jpg'],
            ['name' => 'Fingers pollastre SG', 'cats' => ['Sense Gluten'], 'price' => 5.95, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/fingers_sg.jpg'],
            ['name' => 'Pa SG', 'cats' => ['Sense Gluten'], 'price' => 2.20, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/pa_sg.jpg'],

            // --- POSTRES ---
            ['name' => 'Braç nata (Petit)', 'cats' => ['Postres'], 'price' => 2.70, 'is_gluten_free' => false, 'image_path' => 'images/postres/bracetnata.jpg'],
            ['name' => 'Braç trufa (Petit)', 'cats' => ['Postres'], 'price' => 2.70, 'is_gluten_free' => false, 'image_path' => 'images/postres/bracetrufa.jpg'],
            ['name' => 'Crocant nata', 'cats' => ['Postres'], 'price' => 2.90, 'is_gluten_free' => false, 'image_path' => 'images/postres/crocantnata.jpg'],
            ['name' => 'Sara', 'cats' => ['Postres'], 'price' => 3.20, 'is_gluten_free' => false, 'image_path' => 'images/postres/sara.jpg'],
            ['name' => 'Mousse fruits vermells (SG)', 'cats' => ['Postres', 'Sense Gluten'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/postres/moussefruits.jpg'],
            ['name' => 'Mousse mango (SG)', 'cats' => ['Postres', 'Sense Gluten'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/postres/moussemango.jpg'],
            ['name' => 'Mousse llimona (SG)', 'cats' => ['Postres', 'Sense Gluten'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/postres/moussellimona.jpg'],
            ['name' => 'Tiramisú (SG)', 'cats' => ['Postres', 'Sense Gluten'], 'price' => 5.50, 'is_gluten_free' => true, 'image_path' => 'images/postres/tiramisu.jpg'],
            ['name' => 'Pastís pastanaga (SG)', 'cats' => ['Postres', 'Sense Gluten'], 'price' => 5.00, 'is_gluten_free' => true, 'image_path' => 'images/postres/pastispastanaga.jpg'],
            ['name' => 'Coulant xoco (SG)', 'cats' => ['Postres', 'Sense Gluten'], 'price' => 5.50, 'is_gluten_free' => true, 'image_path' => 'images/postres/coulantxoco.jpg'],
            ['name' => 'Mousse xoco (SG)', 'cats' => ['Postres', 'Sense Gluten'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/postres/moussexoco.jpg'],
            ['name' => 'Red Velvet (SG)', 'cats' => ['Postres', 'Sense Gluten'], 'price' => 5.50, 'is_gluten_free' => true, 'image_path' => 'images/postres/redvelvet.jpg'],
            ['name' => 'Bracet Nata SG', 'cats' => ['Sense Gluten'], 'price' => 4.50, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/bracet-nata-gluten-1024x682.jpg'],
            ['name' => 'Sara SG', 'cats' => ['Sense Gluten'], 'price' => 5.00, 'is_gluten_free' => true, 'image_path' => 'images/sensegluten/sara-gluten-1024x682.jpg'],
        ];

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