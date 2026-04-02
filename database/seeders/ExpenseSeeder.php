<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Paroisse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paroisse = Paroisse::first();

        if (! $paroisse) {
            $this->command?->warn('Aucune paroisse trouvée, ExpenseSeeder ignoré.');

            return;
        }

        $user = User::first();

        // Supprimer les dépenses existantes pour éviter les doublons
        Expense::query()->delete();

        $expenses = [];

        // Générer des dépenses pour les 3 derniers mois
        $startDate = now()->subMonths(3)->startOfMonth();
        $endDate = now()->endOfMonth();

        // Catégories et types de charges
        $categories = [
            'charge_fixe' => [
                'electricite' => ['Électricité', 50000, 150000],
                'eau' => ['Eau', 20000, 60000],
                'gaz' => ['Gaz', 30000, 80000],
                'internet' => ['Internet', 25000, 50000],
                'salaire_ouvrier' => ['Salaire ouvrier', 100000, 200000],
            ],
            'charge_variable' => [
                'carburant' => ['Carburant', 50000, 150000],
                'hosties' => ['Hosties', 10000, 30000],
                'maintenance_materiel' => ['Maintenance matériel', 50000, 200000],
                'gardiennage' => ['Gardiennage', 20000, 50000],
            ],
            'charge_exceptionnelle' => [
                'autre' => ['Autre', 50000, 300000],
            ],
        ];

        $fournisseurs = [
            'SNE (Société Nationale d\'Électricité)',
            'SNDE (Société Nationale de Distribution d\'Eau)',
            'Station Total',
            'Station Shell',
            'Orange Congo',
            'Airtel Congo',
            'Fournisseur Général',
            'Entreprise de Maintenance',
        ];

        $methodesPaiement = ['especes', 'cheque', 'virement', 'mobile_money'];

        // Générer des dépenses récurrentes (charges fixes) - mensuelles
        $currentMonth = $startDate->copy()->startOfMonth();
        while ($currentMonth->lte($endDate)) {
            // Charges fixes mensuelles
            foreach ($categories['charge_fixe'] as $type => $details) {
                [$nom, $min, $max] = $details;
                $expenses[] = [
                    'paroisse_id' => $paroisse->id,
                    'categorie_charge' => 'charge_fixe',
                    'type_charge' => $type,
                    'montant' => fake()->numberBetween($min, $max),
                    'date_depense' => $currentMonth->copy()->day(fake()->numberBetween(1, 10)),
                    'facture_reference' => 'FACT-'.$type.'-'.$currentMonth->format('Ym').'-'.strtoupper(Str::random(4)),
                    'fournisseur' => fake()->randomElement($fournisseurs),
                    'methode_paiement' => fake()->randomElement($methodesPaiement),
                    'statut' => 'valide',
                    'notes' => fake()->boolean(30) ? fake()->sentence() : null,
                    'created_by' => $user?->id,
                    'created_at' => $currentMonth->copy(),
                    'updated_at' => $currentMonth->copy(),
                ];
            }

            $currentMonth->addMonth();
        }

        // Générer des dépenses variables - plusieurs par mois
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            // Charges variables (2-4 par mois)
            $nbVariables = fake()->numberBetween(2, 4);
            for ($i = 0; $i < $nbVariables; $i++) {
                $type = fake()->randomKey($categories['charge_variable']);
                $details = $categories['charge_variable'][$type];
                [$nom, $min, $max] = $details;

                $dateDepense = $currentDate->copy()->addDays(fake()->numberBetween(0, 27));
                $expenses[] = [
                    'paroisse_id' => $paroisse->id,
                    'categorie_charge' => 'charge_variable',
                    'type_charge' => $type,
                    'montant' => fake()->numberBetween($min, $max),
                    'date_depense' => $dateDepense,
                    'facture_reference' => 'FACT-'.$type.'-'.$dateDepense->format('Ymd').'-'.strtoupper(Str::random(4)),
                    'fournisseur' => fake()->randomElement($fournisseurs),
                    'methode_paiement' => fake()->randomElement($methodesPaiement),
                    'statut' => 'valide',
                    'notes' => fake()->boolean(20) ? fake()->sentence() : null,
                    'created_by' => $user?->id,
                    'created_at' => $currentDate->copy(),
                    'updated_at' => $currentDate->copy(),
                ];
            }

            $currentDate->addMonth();
        }

        // Générer quelques charges exceptionnelles
        for ($i = 0; $i < 3; $i++) {
            $type = 'autre';
            $details = $categories['charge_exceptionnelle'][$type];
            [$nom, $min, $max] = $details;

            $dateDepense = Carbon::parse(fake()->dateTimeBetween($startDate, $endDate));

            $expenses[] = [
                'paroisse_id' => $paroisse->id,
                'categorie_charge' => 'charge_exceptionnelle',
                'type_charge' => $type,
                'montant' => fake()->numberBetween($min, $max),
                'date_depense' => $dateDepense,
                'facture_reference' => 'FACT-EXCEPT-'.$dateDepense->format('Ymd').'-'.strtoupper(Str::random(4)),
                'fournisseur' => fake()->randomElement($fournisseurs),
                'methode_paiement' => fake()->randomElement($methodesPaiement),
                'statut' => 'valide',
                'notes' => fake()->sentence(),
                'created_by' => $user?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insérer par lots pour de meilleures performances
        $chunks = array_chunk($expenses, 100);
        foreach ($chunks as $chunk) {
            Expense::insert($chunk);
        }

        $this->command?->info(count($expenses).' dépenses de test ont été créées.');
    }
}
