<?php

namespace Database\Seeders;

use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RevenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paroisse = Paroisse::first();

        if (! $paroisse) {
            $this->command?->warn('Aucune paroisse trouvée, RevenueSeeder ignoré.');

            return;
        }

        $user = User::first();

        // Récupérer les catégories et types de cette paroisse
        $scope = fn ($code) => RevenueCategory::where('paroisse_id', $paroisse->id)->where('code', $code)->first();
        $queteOrdinaire = $scope('quete_ordinaire');
        $queteExtraordinaire = $scope('quete_extraordinaire');
        $location = $scope('location');
        $popoteSubvention = $scope('popote_subvention');
        $procure = $scope('procure');

        if (! $queteOrdinaire) {
            $this->command?->warn('Les catégories de recettes ne sont pas créées pour cette paroisse. Exécutez RevenueCategorySeeder puis RevenueTypeSeeder.');

            return;
        }

        $typeScope = fn ($code) => RevenueType::where('paroisse_id', $paroisse->id)->where('code', $code)->first();
        $messeSemaine = $typeScope('messe_semaine');
        $messeDimanche = $typeScope('messe_dimanche');
        $popoteMensuelle = $typeScope('popote_mensuelle');

        // Supprimer les revenus existants pour éviter les doublons
        Revenue::query()->delete();

        $revenues = [];

        // Générer des revenus pour les 3 derniers mois
        $startDate = now()->subMonths(3)->startOfMonth();
        $endDate = now()->endOfMonth();

        // 1. Revenus de quête ordinaire (semaine et dimanche)
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeek; // 0 = dimanche, 1 = lundi, etc.

            // Dimanche
            if ($dayOfWeek === 0) {
                if ($messeDimanche) {
                    $montant = fake()->numberBetween(50000, 200000); // 50k à 200k FCFA
                    $revenues[] = [
                        'paroisse_id' => $paroisse->id,
                        'revenue_category_id' => $queteOrdinaire->id,
                        'revenue_type_id' => $messeDimanche->id,
                        'periode_messe' => 'dimanche',
                        'jour_semaine' => 'dimanche',
                        'montant' => $montant,
                        'date_recette' => $currentDate->copy(),
                        'methode_paiement' => fake()->randomElement(['especes', 'cheque', 'mobile_money']),
                        'reference_paiement' => 'REV-'.$currentDate->format('Ymd').'-'.strtoupper(Str::random(4)),
                        'statut' => 'valide',
                        'created_by' => $user?->id,
                        'created_at' => $currentDate->copy(),
                        'updated_at' => $currentDate->copy(),
                    ];
                }
            } else {
                // Semaine (lundi à samedi)
                if ($messeSemaine && fake()->boolean(70)) { // 70% de chance d'avoir une messe en semaine
                    $montant = fake()->numberBetween(20000, 80000); // 20k à 80k FCFA
                    $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
                    $jour = $jours[$dayOfWeek - 1];

                    $revenues[] = [
                        'paroisse_id' => $paroisse->id,
                        'revenue_category_id' => $queteOrdinaire->id,
                        'revenue_type_id' => $messeSemaine->id,
                        'periode_messe' => 'semaine',
                        'jour_semaine' => $jour,
                        'montant' => $montant,
                        'date_recette' => $currentDate->copy(),
                        'methode_paiement' => fake()->randomElement(['especes', 'cheque']),
                        'reference_paiement' => 'REV-'.$currentDate->format('Ymd').'-'.strtoupper(Str::random(4)),
                        'statut' => 'valide',
                        'created_by' => $user?->id,
                        'created_at' => $currentDate->copy(),
                        'updated_at' => $currentDate->copy(),
                    ];
                }
            }

            $currentDate->addDay();
        }

        // 2. Revenus popote/subvention (mensuels)
        $currentMonth = $startDate->copy()->startOfMonth();
        while ($currentMonth->lte($endDate)) {
            if ($popoteMensuelle) {
                $revenues[] = [
                    'paroisse_id' => $paroisse->id,
                    'revenue_category_id' => $popoteSubvention->id,
                    'revenue_type_id' => $popoteMensuelle->id,
                    'periode_messe' => null,
                    'jour_semaine' => null,
                    'montant' => fake()->numberBetween(500000, 1000000), // 500k à 1M FCFA
                    'date_recette' => $currentMonth->copy()->day(5), // Le 5 de chaque mois
                    'methode_paiement' => 'virement',
                    'reference_paiement' => 'REV-POPOTE-'.$currentMonth->format('Ym'),
                    'statut' => 'valide',
                    'created_by' => $user?->id,
                    'created_at' => $currentMonth->copy(),
                    'updated_at' => $currentMonth->copy(),
                ];
            }

            $currentMonth->addMonth();
        }

        // 3. Quelques revenus extraordinaires
        $mariage = $typeScope('mariage');
        $obseques = $typeScope('obseques');
        $actionGrace = $typeScope('action_grace');

        for ($i = 0; $i < 5; $i++) {
            $type = fake()->randomElement([$mariage, $obseques, $actionGrace]);
            if ($type) {
                $dateRecette = Carbon::parse(fake()->dateTimeBetween($startDate, $endDate));
                $revenues[] = [
                    'paroisse_id' => $paroisse->id,
                    'revenue_category_id' => $queteExtraordinaire->id,
                    'revenue_type_id' => $type->id,
                    'periode_messe' => null,
                    'jour_semaine' => null,
                    'montant' => fake()->numberBetween(100000, 500000),
                    'date_recette' => $dateRecette,
                    'methode_paiement' => fake()->randomElement(['especes', 'cheque', 'mobile_money']),
                    'reference_paiement' => 'REV-'.$dateRecette->format('Ymd').'-'.strtoupper(Str::random(4)),
                    'statut' => 'valide',
                    'created_by' => $user?->id,
                    'created_at' => $dateRecette,
                    'updated_at' => $dateRecette,
                ];
            }
        }

        // Insérer par lots pour de meilleures performances
        $chunks = array_chunk($revenues, 100);
        foreach ($chunks as $chunk) {
            Revenue::insert($chunk);
        }

        $this->command?->info(count($revenues).' revenus de test ont été créés.');
    }
}
