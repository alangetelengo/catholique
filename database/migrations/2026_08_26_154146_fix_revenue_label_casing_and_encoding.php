<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            'banque' => 'BANQUE (économat diocésain)',
            'quete_ordinaire' => 'Quête ordinaire',
            'quete_extraordinaire' => 'Quête extraordinaire',
            'location' => 'Location',
            'subvention' => 'Subvention',
            'procure' => 'Procure',
            'fete' => 'Fête',
        ];

        foreach ($categories as $code => $nom) {
            DB::table('revenue_categories')->where('code', $code)->update([
                'nom' => $nom,
                'updated_at' => now(),
            ]);
        }

        // Corrige aussi les libellés corrompus (Qu??te / F??te) quel que soit le code
        DB::table('revenue_categories')
            ->where('nom', 'like', 'Qu%te Ordinaire')
            ->orWhere('nom', 'like', 'Qu%te Extraordinaire')
            ->orWhere('nom', 'like', 'F%te')
            ->get(['id', 'code', 'nom'])
            ->each(function ($row) use ($categories): void {
                if (isset($categories[$row->code])) {
                    DB::table('revenue_categories')->where('id', $row->id)->update([
                        'nom' => $categories[$row->code],
                        'updated_at' => now(),
                    ]);
                }
            });

        $types = [
            'revenu_principal' => 'Revenu principal',
            'messe_semaine' => 'Messe semaine',
            'messe_dimanche' => 'Messe dimanche',
            'mariage' => 'Mariage',
            'obseques' => 'Obsèques',
            'action_grace' => 'Action de grâce',
            'caritas' => 'Caritas',
            'grotte' => 'La grotte',
            'autre_extraordinaire' => 'Autre extraordinaire',
            'nsinsani' => 'NSINSANI',
            'loyer_boutique' => 'Loyer boutique',
            'salle_fete' => 'Salle de fête',
            'chapiteau' => 'Chapiteau',
            'cour_paroisse' => 'Cour de la paroisse',
            'subvention_carburant' => 'Subvention carburant',
            'subvention_hosties' => 'Subvention hosties',
            'subvention_gardiennage' => 'Subvention gardiennage',
            'subvention_gaz' => 'Subvention gaz',
            'subvention_internet' => 'Subvention internet',
            'subvention_eau' => 'Subvention eau',
            'subvention_electricite' => 'Subvention électricité',
            'subvention_salaires' => 'Subvention salaires',
            'subvention_popote' => 'Subvention popote',
            'dime' => 'Dîme',
            'denier_culte' => 'Denier du culte',
            'casuel_bapteme' => 'Casuel (baptêmes)',
            'don' => 'Don',
            'nsinsani-d' => 'NSINSANI diocésain',
            'card' => 'CARDINAL',
            'fete-paroisse' => 'Fête patronale paroissiale',
            'repas-doy' => 'Repas du doyenné',
            'renc-doy' => 'Rencontre du doyenné',
            'repas-natif' => 'Repas des natifs',
            'rev-principal' => 'Revenu principal',
        ];

        foreach ($types as $code => $nom) {
            DB::table('revenue_types')->where('code', $code)->update([
                'nom' => $nom,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Pas de restauration des libellés corrompus.
    }
};
