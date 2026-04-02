<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Revenue;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * API de synchronisation pour le mode offline.
 * Accepte des recettes et dépenses créées hors ligne et les enregistre côté serveur.
 */
class SyncController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'revenues' => ['sometimes', 'array'],
            'revenues.*.action' => ['required', 'in:create'],
            'revenues.*.data' => ['required', 'array'],
            'revenues.*.data.paroisse_id' => ['nullable', 'exists:paroisses,id'],
            'revenues.*.data.revenue_category_id' => ['required', 'exists:revenue_categories,id'],
            'revenues.*.data.revenue_type_id' => ['required', 'exists:revenue_types,id'],
            'revenues.*.data.date_recette' => ['required', 'date'],
            'revenues.*.data.montant' => ['required', 'numeric', 'min:0'],
            'revenues.*.data.methode_paiement' => ['required', 'in:especes,cheque,virement,carte,mobile_money'],
            'revenues.*.data.reference_paiement' => ['nullable', 'string', 'max:255'],
            'revenues.*.data.notes' => ['nullable', 'string'],
            'revenues.*.data.donateur_nom' => ['nullable', 'string', 'max:255'],
            'revenues.*.data.donateur_telephone' => ['nullable', 'string', 'max:50'],
            'revenues.*.data.jour_semaine' => ['nullable', 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche'],
            'revenues.*.data.periode_messe' => ['nullable', 'string'],
            'revenues.*.data.mois_location' => ['nullable', 'string'],
            'expenses' => ['sometimes', 'array'],
            'expenses.*.action' => ['required', 'in:create'],
            'expenses.*.data' => ['required', 'array'],
            'expenses.*.data.paroisse_id' => ['nullable', 'exists:paroisses,id'],
            'expenses.*.data.categorie_charge' => ['required', 'in:charge_fixe,charge_variable,charge_exceptionnelle,alimentation_popote'],
            'expenses.*.data.type_charge' => ['nullable', 'in:carburant,hosties,internet,maintenance_materiel,gaz,eau,electricite,gardiennage,salaire_ouvrier,autre,alimentation'],
            'expenses.*.data.date_depense' => ['required', 'date'],
            'expenses.*.data.montant' => ['required', 'numeric', 'min:0'],
            'expenses.*.data.jour_semaine' => ['nullable', 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche'],
            'expenses.*.data.libelle' => ['nullable', 'string', 'max:500'],
            'expenses.*.data.facture_reference' => ['nullable', 'string', 'max:255'],
            'expenses.*.data.fournisseur' => ['nullable', 'string', 'max:255'],
            'expenses.*.data.methode_paiement' => ['required', 'in:especes,cheque,virement,carte,mobile_money'],
            'expenses.*.data.notes' => ['nullable', 'string'],
        ]);

        $results = ['revenues' => [], 'expenses' => [], 'errors' => []];

        DB::beginTransaction();
        try {
            foreach (($validated['revenues'] ?? []) ?: [] as $idx => $item) {
                try {
                    $data = $this->prepareRevenueData($item['data'], $user);
                    $revenue = Revenue::create($data);
                    $results['revenues'][$idx] = ['id' => $revenue->id, 'temp_id' => $item['data']['_temp_id'] ?? null];
                } catch (ValidationException $e) {
                    $results['errors'][] = ['type' => 'revenue', 'index' => $idx, 'message' => $e->getMessage()];
                }
            }

            foreach (($validated['expenses'] ?? []) ?: [] as $idx => $item) {
                try {
                    $data = $this->prepareExpenseData($item['data'], $user);
                    $expense = Expense::create($data);
                    $results['expenses'][$idx] = ['id' => $expense->id, 'temp_id' => $item['data']['_temp_id'] ?? null];
                } catch (ValidationException $e) {
                    $results['errors'][] = ['type' => 'expense', 'index' => $idx, 'message' => $e->getMessage()];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la synchronisation : ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Synchronisation terminée.',
            'results' => $results,
        ]);
    }

    private function prepareRevenueData(array $data, $user): array
    {
        unset($data['_temp_id']);

        if (! $user->hasRole('super_admin')) {
            $data['paroisse_id'] = $user->paroisse_id;
        }

        $data['created_by'] = $user->id;
        $data['reference_paiement'] = $data['reference_paiement'] ?? 'REV-' . now()->format('YmdHis') . '-' . strtoupper(str()->random(4));

        $category = \App\Models\RevenueCategory::find($data['revenue_category_id']);
        if ($category && $category->code === 'procure') {
            if (! empty($data['donateur_nom'])) {
                $data['donateur_nom'] = mb_strtoupper($data['donateur_nom'], 'UTF-8');
            }
            if (! empty($data['donateur_telephone'])) {
                $data['donateur_telephone'] = $this->normalizePhone242($data['donateur_telephone']);
            }
        } else {
            $data['donateur_nom'] = null;
            $data['donateur_telephone'] = null;
        }

        return array_intersect_key($data, array_flip((new Revenue)->getFillable()));
    }

    private function prepareExpenseData(array $data, $user): array
    {
        unset($data['_temp_id']);

        if (! $user->hasRole('super_admin')) {
            $data['paroisse_id'] = $user->paroisse_id;
        }

        $data['created_by'] = $user->id;
        $data['piece_facture_path'] = null;
        $data['piece_recu_path'] = null;

        if ($data['categorie_charge'] === 'alimentation_popote') {
            $data['type_charge'] = 'alimentation';
            if (empty($data['jour_semaine']) && ! empty($data['date_depense'])) {
                $d = Carbon::parse($data['date_depense']);
                $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
                $data['jour_semaine'] = $jours[$d->dayOfWeek] ?? null;
            }
        } else {
            $data['libelle'] = null;
            $data['jour_semaine'] = null;
        }

        return array_intersect_key($data, array_flip((new Expense)->getFillable()));
    }

    private function normalizePhone242(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits !== '' && ! str_starts_with($digits, '242')) {
            $digits = '242' . $digits;
        }

        return $digits;
    }
}
