<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Business values were stored in French (filières, regions, lot step labels).
 * They become English keys, translated at display time.
 */
return new class extends Migration
{
    private const array FILIERES = [
        'cacao' => 'cocoa',
        'cafe_arabica' => 'arabica_coffee',
        'cafe_robusta' => 'robusta_coffee',
        'mais' => 'maize',
        'manioc' => 'cassava',
        'banane' => 'banana',
        'tomate' => 'tomato',
        'arachide' => 'groundnut',
        'igname' => 'yam',
        'huile_de_palme' => 'palm_oil',
    ];

    private const array REGIONS = [
        'Adamaoua' => 'adamawa',
        'Centre' => 'centre',
        'Est' => 'east',
        'Extrême-Nord' => 'far_north',
        'Littoral' => 'littoral',
        'Nord' => 'north',
        'Nord-Ouest' => 'north_west',
        'Ouest' => 'west',
        'Sud' => 'south',
        'Sud-Ouest' => 'south_west',
    ];

    /**
     * Step labels written by the first DemoSeeder and LotFactory.
     */
    private const array LOT_STEPS = [
        'Récolte' => 'harvest',
        'Récolte et écabossage' => 'harvest',
        'Cueillette des cerises' => 'harvest',
        'Fermentation' => 'fermentation',
        'Fermentation en bacs (6 jours)' => 'fermentation',
        'Séchage' => 'drying',
        'Séchage au soleil' => 'drying',
        'Séchage sur claies' => 'drying',
        'Dépulpage et lavage' => 'pulping',
        'Décorticage et triage' => 'hulling',
        'Égrenage et tri' => 'shelling',
        'Tri et ensachage' => 'sorting',
        'Tri et calibrage' => 'sorting',
        'Ensachage (sacs de 60 kg)' => 'bagging',
        'Conditionnement' => 'packaging',
        'Contrôle qualité (humidité < 8 %)' => 'quality_check',
        'Stockage' => 'storage',
        'Stockage en sacs' => 'storage',
        'Transport vers le marché' => 'transport',
        'Expédition vers le port de Douala' => 'shipping',
    ];

    public function up(): void
    {
        $this->convert(self::FILIERES, self::REGIONS, self::LOT_STEPS);
    }

    public function down(): void
    {
        // Several French labels map to one key: each step goes back to one of its original labels.
        $this->convert(array_flip(self::FILIERES), array_flip(self::REGIONS), array_flip(self::LOT_STEPS));
    }

    /**
     * @param  array<string, string>  $filieres
     * @param  array<string, string>  $regions
     * @param  array<string, string>  $steps
     */
    private function convert(array $filieres, array $regions, array $steps): void
    {
        foreach ($filieres as $from => $to) {
            DB::table('products')->where('filiere', $from)->update(['filiere' => $to]);
            DB::table('market_prices')->where('filiere', $from)->update(['filiere' => $to]);
        }

        foreach ($regions as $from => $to) {
            foreach (['users', 'cooperatives', 'market_prices'] as $table) {
                DB::table($table)->where('region', $from)->update(['region' => $to]);
            }
        }

        DB::table('cooperatives')->select(['id', 'filieres'])->orderBy('id')->each(function (object $cooperative) use ($filieres): void {
            $values = json_decode((string) $cooperative->filieres, true) ?: [];

            DB::table('cooperatives')->where('id', $cooperative->id)->update([
                'filieres' => json_encode(array_map(fn (string $value): string => $filieres[$value] ?? $value, $values)),
            ]);
        });

        DB::table('lots')->select(['id', 'steps'])->orderBy('id')->each(function (object $lot) use ($steps): void {
            $values = json_decode((string) $lot->steps, true) ?: [];

            DB::table('lots')->where('id', $lot->id)->update([
                'steps' => json_encode(array_map(fn (array $step): array => [
                    ...$step,
                    'label' => $steps[$step['label']] ?? $step['label'],
                ], $values), JSON_UNESCAPED_UNICODE),
            ]);
        });
    }
};
