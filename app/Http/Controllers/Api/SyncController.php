<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Services\CaisseService;
use App\Support\CapitalMensuel;
use App\Support\SubventionMensuelle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * API de synchronisation pour le mode offline.
 * Accepte des recettes et dépenses créées hors ligne et les enregistre côté serveur.
 */
class SyncController extends Controller
{
    public function __construct(
        protected CaisseService $caisseService
    ) {}

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
            'revenues.*.data.mois_capital' => ['nullable', 'in:01,02,03,04,05,06,07,08,09,10,11,12'],
            'expenses' => ['sometimes', 'array'],
            'expenses.*.action' => ['required', 'in:create'],
            'expenses.*.data' => ['required', 'array'],
            'expenses.*.data.paroisse_id' => ['nullable', 'exists:paroisses,id'],
            'expenses.*.data.revenue_category_id' => ['nullable', 'exists:revenue_categories,id'],
            'expenses.*.data.revenue_type_id' => ['nullable', 'exists:revenue_types,id'],
            'expenses.*.data.expense_type_id' => ['required', 'integer', 'exists:expense_types,id,actif,1'],
            'expenses.*.data.date_depense' => ['required', 'date'],
            'expenses.*.data.mois_capital' => ['required', 'in:01,02,03,04,05,06,07,08,09,10,11,12'],
            'expenses.*.data.annee_capital' => ['required', 'integer', 'min:2000', 'max:2100'],
            'expenses.*.data.montant' => ['required', 'numeric', 'min:0'],
            'expenses.*.data.jour_semaine' => ['nullable', 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche'],
            'expenses.*.data.libelle' => ['required', 'string', 'max:500'],
            'expenses.*.data.facture_reference' => ['nullable', 'string', 'max:255'],
            'expenses.*.data.fournisseur' => ['nullable', 'string', 'max:255'],
            'expenses.*.data.methode_paiement' => ['required', 'in:especes,cheque,virement,carte,mobile_money'],
            'expenses.*.data.notes' => ['nullable', 'string'],
            'expenses.*.data.funding_sources' => ['required', 'array', 'min:1'],
            'expenses.*.data.funding_sources.*.caisse_id' => ['required', 'integer', 'exists:caisses,id'],
            'expenses.*.data.funding_sources.*.montant_alloue' => ['required', 'numeric', 'min:0.01'],
        ]);

        $results = ['revenues' => [], 'expenses' => [], 'errors' => []];

        foreach (($validated['revenues'] ?? []) ?: [] as $idx => $item) {
            try {
                DB::transaction(function () use ($item, $user, $idx, &$results): void {
                    $data = $this->prepareRevenueData($item['data'], $user);
                    $revenue = Revenue::create($data);
                    $revenue->load(['category', 'type']);
                    $this->caisseService->syncCreditFromBanqueRevenue($revenue);
                    $results['revenues'][$idx] = ['id' => $revenue->id, 'temp_id' => $item['data']['_temp_id'] ?? null];
                });
            } catch (ValidationException $e) {
                $message = collect($e->errors())->flatten()->implode(' ');
                $results['errors'][] = ['type' => 'revenue', 'index' => $idx, 'message' => $message ?: $e->getMessage()];
            } catch (InvalidArgumentException $e) {
                $results['errors'][] = ['type' => 'revenue', 'index' => $idx, 'message' => $e->getMessage()];
            } catch (\Throwable $e) {
                $results['errors'][] = ['type' => 'revenue', 'index' => $idx, 'message' => $e->getMessage()];
            }
        }

        foreach (($validated['expenses'] ?? []) ?: [] as $idx => $item) {
            try {
                DB::transaction(function () use ($item, $user, $idx, &$results): void {
                    $raw = $item['data'];
                    $fundingSources = $raw['funding_sources'] ?? [];
                    $data = $this->prepareExpenseData($raw, $user);

                    $paroisseId = (int) ($data['paroisse_id'] ?? $user->paroisse_id);
                    $validation = $this->caisseService->validateFundingSources(
                        $fundingSources,
                        null,
                        $paroisseId,
                        $data['mois_capital'],
                        (int) $data['annee_capital']
                    );

                    if (! $validation['valid']) {
                        throw ValidationException::withMessages([
                            'funding_sources' => $validation['errors'],
                        ]);
                    }

                    if (abs((float) $data['montant'] - $validation['total_alloue']) > 0.01) {
                        throw ValidationException::withMessages([
                            'funding_sources' => [
                                sprintf(
                                    'Le total des caisses (%s FCFA) doit être égal au montant de la dépense (%s FCFA)',
                                    number_format($validation['total_alloue'], 0, ',', ' '),
                                    number_format((float) $data['montant'], 0, ',', ' ')
                                ),
                            ],
                        ]);
                    }

                    $expense = Expense::create($data);

                    foreach ($fundingSources as $index => $source) {
                        if (! empty($source['caisse_id']) && ! empty($source['montant_alloue'])) {
                            $expense->fundingSources()->create([
                                'caisse_id' => $source['caisse_id'],
                                'montant_alloue' => $source['montant_alloue'],
                                'mois_capital' => $data['mois_capital'],
                                'annee_capital' => $data['annee_capital'],
                                'ordre' => $index + 1,
                            ]);
                        }
                    }

                    $expense->load('fundingSources');
                    $this->caisseService->syncDepenseMouvements($expense, $fundingSources);

                    $results['expenses'][$idx] = ['id' => $expense->id, 'temp_id' => $item['data']['_temp_id'] ?? null];
                });
            } catch (ValidationException $e) {
                $message = collect($e->errors())->flatten()->implode(' ');
                $results['errors'][] = ['type' => 'expense', 'index' => $idx, 'message' => $message ?: $e->getMessage()];
            } catch (InvalidArgumentException $e) {
                $results['errors'][] = ['type' => 'expense', 'index' => $idx, 'message' => $e->getMessage()];
            } catch (\Throwable $e) {
                $results['errors'][] = ['type' => 'expense', 'index' => $idx, 'message' => $e->getMessage()];
            }
        }

        $hasErrors = ! empty($results['errors']);
        $hasSuccess = ! empty($results['revenues']) || ! empty($results['expenses']);

        return response()->json([
            'success' => ! $hasErrors || $hasSuccess,
            'message' => $hasErrors
                ? ($hasSuccess ? 'Synchronisation partielle terminée.' : 'Aucune écriture n\'a pu être synchronisée.')
                : 'Synchronisation terminée.',
            'results' => $results,
        ], $hasErrors && ! $hasSuccess ? 422 : 200);
    }

    private function prepareRevenueData(array $data, $user): array
    {
        unset($data['_temp_id']);

        if (! $user->hasRole('super_admin')) {
            $data['paroisse_id'] = $user->paroisse_id;
        }

        $data['created_by'] = $user->id;
        $data['reference_paiement'] = $data['reference_paiement'] ?? 'REV-'.now()->format('YmdHis').'-'.strtoupper(str()->random(4));
        $data['statut'] = $data['statut'] ?? 'valide';

        $category = RevenueCategory::find($data['revenue_category_id']);
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

        if ($category && $category->code === 'banque') {
            if (empty($data['mois_capital']) || ! SubventionMensuelle::isValidMoisCapital((string) $data['mois_capital'])) {
                throw ValidationException::withMessages([
                    'mois_capital' => 'Le mois du capital est obligatoire pour une recette Banque.',
                ]);
            }
        } else {
            $data['mois_capital'] = null;
        }

        return array_intersect_key($data, array_flip((new Revenue)->getFillable()));
    }

    private function prepareExpenseData(array $data, $user): array
    {
        unset($data['_temp_id'], $data['funding_sources']);

        if (! $user->hasRole('super_admin')) {
            $data['paroisse_id'] = $user->paroisse_id;
        }

        $data['created_by'] = $user->id;
        $data['piece_facture_path'] = null;
        $data['piece_recu_path'] = null;
        $data['revenue_category_id'] = null;
        $data['revenue_type_id'] = null;
        $data['statut'] = $data['statut'] ?? 'valide';

        if (! CapitalMensuel::isValidEnvelope(
            $data['mois_capital'] ?? null,
            isset($data['annee_capital']) ? (int) $data['annee_capital'] : null
        )) {
            throw ValidationException::withMessages([
                'mois_capital' => 'Le mois du capital est obligatoire pour une dépense.',
            ]);
        }

        if (empty($data['jour_semaine']) && ! empty($data['date_depense'])) {
            $d = Carbon::parse($data['date_depense']);
            $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
            $data['jour_semaine'] = $jours[$d->dayOfWeek] ?? null;
        }

        return array_intersect_key($data, array_flip((new Expense)->getFillable()));
    }

    private function normalizePhone242(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits !== '' && ! str_starts_with($digits, '242')) {
            $digits = '242'.$digits;
        }

        return $digits;
    }
}
