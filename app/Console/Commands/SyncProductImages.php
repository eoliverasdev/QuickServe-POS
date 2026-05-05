<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class SyncProductImages extends Command
{
    protected $signature = 'products:sync-images
        {--dry-run : Mostra els canvis sense escriure a la BD}
        {--only-empty : Només actualitza productes que no tenen imatge}';

    protected $description = "Sincronitza les rutes d'imatge dels productes a partir del seeder, sense tocar preu ni stock.";

    /**
     * Mapa nom_producte → image_path (relatiu a public/).
     * Mantingues sincronitzat amb database/seeders/ProductSeeder.php.
     */
    protected array $imageMap = [
        // Carns
        'Pollastre' => 'images/carns/pollastre.jpg',
        '1/2 Pollastre' => 'images/carns/migpollastre.jpg',
        'Conill' => 'images/carns/conill.jpg',
        'Espatlla Xai' => 'images/carns/xai.jpg',
        'Picantó' => 'images/carns/picanto.jpg',
        'Costelló Porc' => 'images/carns/costella.jpg',
        'Butifarra' => 'images/carns/butifarra.jpg',
        'Xoriço' => 'images/carns/xoric.jpg',
        'Galta Porc' => 'images/carns/galta.jpg',
        "Cuixa d'Ànec" => 'images/carns/anec.jpg',
        'Xurrasc' => 'images/carns/xurrasco.jpg',
        'Peus Porc' => 'images/guisats/peusporc.jpg',
        'Mandonguilles' => 'images/carns/mandonguilles.jpg',
        'Vadella Bolets' => 'images/carns/vadella.jpg',
        'Aletes' => 'images/complements/aletes.jpg',
        'Garri' => 'images/carns/garri.jpg',
        'Cabrit' => 'images/carns/cabrit.jpg',
        'Galta al Forn' => 'images/carns/galta.jpg',
        'Pinxo Pollo' => 'images/complements/pinxopollastre.jpg',
        'Morro de Porc' => 'images/carns/morro.jpg',

        // Entrants Freds
        'Pastís Tonyina' => 'images/complements/tonyina.jpg',
        'Escalivada' => 'images/entrants/escalivada.jpg',
        'Esqueixada' => 'images/entrants/esqueixadabacalla.jpg',
        'Ensaladilla' => 'images/entrants/ensaladilla.jpg',
        'Amanida Pasta' => 'images/amanides/amanidapasta.jpg',
        'Amanida Arròs' => 'images/amanides/amanidaarroz.jpg',
        'Amanida Formatge' => 'images/amanides/amanidaformatge.jpg',
        'Ous Farcits' => 'images/entrants/ousfarcits.jpg',
        'Gaspatx' => 'images/complements/gazpacho.jpg',
        'Amanida Verda' => 'images/amanides/amanidaverda.jpg',
        'Cargol' => 'images/entrants/cargolsllauna.jpg',
        'Cargol Grans' => 'images/entrants/cargolsllauna.jpg',
        'Macarrons' => 'images/entrants/macarrons.jpg',
        'Trinxat' => 'images/entrantsfreds/trinxat.jpg',
        'Brou' => 'images/entrantsfreds/brou.jpg',
        'Spaguetis Carbonara' => 'images/entrants/carbonara.jpg',
        'Pop a la Gallega' => 'images/entrantsfreds/popgallega.jpg',

        // Entrants Calents
        '6 Canelons' => 'images/entrants/canelontres.jpg',
        '12 Canelons' => 'images/entrants/canelonsis.jpg',
        'Albergínia Farcida' => 'images/entrants/alberginiafarcida.jpg',
        'Fideuà' => 'images/entrants/fideua.jpg',
        'Truita Patata' => 'images/entrants/truita.jpg',
        '1/2 Truita Patata' => 'images/entrants/truita.jpg',
        'Arròs Verdura' => 'images/entrants/arrosverdures.jpg',
        'Noodles Asiàtic' => 'images/entrants/noddles.jpg',
        'Caneló Bolets' => 'images/entrants/canelontres.jpg',
        'Patates amb carn' => 'images/complements/patatacarn.jpg',
        'Lassanya' => 'images/entrants/lasanya.jpg',

        // Peix
        'Sèpia amb Pèsols' => 'images/peix/sepia.jpg',
        'Rap al Forn' => 'images/peix/rapforn.jpg',
        'Bacallà Xamfaina' => 'images/guisats/bacallasamfaina.jpg',
        'Bacallà Mouselina' => 'images/guisats/bacallamuselina.jpg',
        'Calamars Farcits' => 'images/peix/calamarfarcit.jpg',
        'Salmó Llimona' => 'images/peix/salmonlimon.jpg',
        'Pebrots Piquillo' => 'images/entrants/pebrotspiquillo.jpg',

        // Complements
        'Patates Fregides' => 'images/complements/patfritas.jpg',
        'Patates Allioli' => 'images/complements/patalioli.jpg',
        'Patates Braves' => 'images/complements/bravas.jpg',
        "Patates d'Olot" => 'images/complements/patataolot.jpg',
        'Patata Alli Juiver' => 'images/complements/patjuli.jpg',
        'Allioli' => 'images/complements/alioli.jpg',
        'Croquetes' => 'images/complements/croquetes.jpg',
        'Calama Romana' => 'images/complements/calamar.jpg',
        'Carbasso Arrebossat' => 'images/complements/carbasso.jpg',
        'Fingers Pollo' => 'images/complements/fingers.jpg',
        'Maionesa / Ketchup / BBQ' => 'images/complements/salses.jpg',
        'Xips Gran' => 'images/complements/bolsapatatas.jpg',
        'Xips Palla' => 'images/complements/paja.jpg',
        'Coberts' => 'images/complements/cuberts.jpg',
        'Salsa Brava' => 'images/complements/brava.jpg',
        '2 Carxofes' => 'images/complements/alcatwo.jpg',
        'Plat 4 Carxofes' => 'images/complements/alcafor.jpg',

        // Postres
        'Gelat Tarrina Petita' => 'images/postres/gelatpetit.jpg',
        'Gelat Tarrina Gran' => 'images/postres/gelatgran.jpg',
        'Tronc Nadal Petit' => 'images/postres/troncpetit.jpg',
        'Tronc Nadal Gran' => 'images/postres/troncgran.jpg',

        // Begudes
        'Aigua 1,5L' => 'images/begudes/aigua.jpg',
        'Aigua 0,50cl' => 'images/begudes/aigua.jpg',
        'Aigua Gas' => 'images/begudes/aiguagas.jpg',
        'Gasosa' => 'images/begudes/aiguagas.jpg',
        'Coca Cola 2L' => 'images/begudes/cola.jpg',
        'Fanta 2L' => 'images/begudes/fanta.jpg',
        'Llauna Refresc' => 'images/begudes/refresc.jpg',
        'Vi Taula' => 'images/begudes/taula.jpg',
        'Vi Cresta Rosa' => 'images/begudes/rosa.jpg',
        'Blanc Pescador' => 'images/begudes/blanc.jpg',
        'Vermut Padró' => 'images/begudes/vermut.jpg',
        'Vi Rioja' => 'images/begudes/rioja.jpg',
        'Xibeca' => 'images/begudes/xibeca.jpg',
        'Estrella' => 'images/begudes/estrella.jpg',
        'Cervesa Sense Gluten' => 'images/begudes/daura.jpg',
        'Cava Brut' => 'images/begudes/cavabrut.jpg',

        // Sense Gluten
        'Croquetes S/Gluten (5U)' => 'images/complements/croquetes.jpg',
        'Fingers Pollo S/Gluten' => 'images/complements/fingers.jpg',
        'Patates Olot S/Gluten' => 'images/complements/patataolot.jpg',
        'Caneló Peix Ànec' => 'images/entrants/canelontres.jpg',
        'Caneló Sense Gluten' => 'images/entrants/canelonsis.jpg',
        'Lassanya S/Gluten' => 'images/entrants/lasanya.jpg',

        // Nadal
        'Caneló Peix' => 'images/entrants/canelontres.jpg',
        'Caneló Ànec Foie' => 'images/entrants/canelontres.jpg',
        'Pastís Llamàntol' => 'images/peix/llamantol.jpg',
        'Còctel de Gambes' => 'images/peix/gamba.jpg',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $onlyEmpty = (bool) $this->option('only-empty');

        $total = 0;
        $updated = 0;
        $skipped = 0;
        $missingFile = [];
        $unmatched = [];

        foreach ($this->imageMap as $name => $path) {
            $product = Product::where('name', $name)->first();
            if (!$product) {
                $unmatched[] = $name;
                continue;
            }
            $total++;

            $absolute = public_path($path);
            if (!file_exists($absolute)) {
                $missingFile[] = $path;
            }

            if ($onlyEmpty && !empty($product->image_path)) {
                $skipped++;
                continue;
            }

            if ($product->image_path === $path) {
                $skipped++;
                continue;
            }

            $previous = $product->image_path ?: '(buit)';
            $this->line(sprintf('  · %s: %s → %s', $name, $previous, $path));

            if (!$dryRun) {
                $product->image_path = $path;
                $product->save();
            }
            $updated++;
        }

        $this->newLine();
        $this->info(sprintf('Productes processats: %d', $total));
        $this->info(sprintf('Actualitzats: %d', $updated) . ($dryRun ? ' (dry-run, no s\'ha escrit res)' : ''));
        $this->info(sprintf('Sense canvis: %d', $skipped));

        if (!empty($unmatched)) {
            $this->warn('Productes del mapa que no existeixen a la BD: ' . count($unmatched));
            foreach ($unmatched as $u) {
                $this->line('  - ' . $u);
            }
        }

        if (!empty($missingFile)) {
            $this->warn('Fitxers d\'imatge inexistents a public/: ' . count($missingFile));
            foreach (array_unique($missingFile) as $m) {
                $this->line('  ! ' . $m);
            }
        }

        // Productos en BD sin imagen y que no estaban en el mapa.
        $orphaned = Product::whereNull('image_path')
            ->orWhere('image_path', '')
            ->orWhere('image_path', 'images/default.jpg')
            ->orWhere('image_path', 'images/plats-sense-foto-1024x724.jpg')
            ->get();

        $orphanedNotMapped = $orphaned->filter(fn(Product $p) => !array_key_exists($p->name, $this->imageMap));
        if ($orphanedNotMapped->isNotEmpty()) {
            $this->warn('Productes sense imatge i fora del mapa: ' . $orphanedNotMapped->count());
            foreach ($orphanedNotMapped as $p) {
                $this->line('  ? ' . $p->name);
            }
        }

        return self::SUCCESS;
    }
}
