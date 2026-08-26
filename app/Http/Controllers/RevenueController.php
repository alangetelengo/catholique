<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Services\CaisseService;
use App\Support\PaginationPerPage;
use App\Support\SubventionMensuelle;
use App\Traits\LogsErrors;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RevenueController extends Controller
{
    use LogsErrors;

    public function __construct(
        protected CaisseService $caisseService
    ) {}

    public function index(Request $request): View
    {
        try {
            $revenues = $this->revenuesIndexFilteredQuery($request)
                ->with(['category', 'type', 'createdBy'])
                ->orderByDesc('date_recette')
                ->orderByDesc('id')
                ->paginate(PaginationPerPage::resolve($request))
                ->withQueryString();

            $totalMontantRecettes = (float) $this->revenuesIndexFilteredQuery($request)->sum('montant');
            $totalMontantDepenses = (float) $this->expensesSummaryQuery($request)->sum('montant');
            $soldeRestant = $totalMontantRecettes - $totalMontantDepenses;

            $paroisseId = $this->resolveParoisseIdForContext($request);
            $categories = $paroisseId !== null
                ? $this->revenueCategoriesWithTypesForParoisse($paroisseId)
                : collect();

            return view('revenues.index', compact(
                'revenues',
                'categories',
                'totalMontantRecettes',
                'totalMontantDepenses',
                'soldeRestant',
            ));
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement des recettes');
            throw $e;
        }
    }

    public function create(Request $request): View
    {
        $revenue = new Revenue([
            'date_recette' => now()->toDateString(),
            'methode_paiement' => 'especes',
        ]);
        $paroisseId = $this->resolveParoisseId($request);
        $categories = $this->revenueCategoriesWithTypesForParoisse($paroisseId);

        return view('revenues.create', compact('revenue', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validateRevenue($request, null);
            $validated['paroisse_id'] = $this->resolveParoisseId($request);
            $validated['created_by'] = $request->user()?->id;
            $validated['reference_paiement'] = $this->generateReference();
            $validated['statut'] = $validated['statut'] ?? 'valide';

            $revenue = DB::transaction(function () use ($validated) {
                $created = Revenue::create($validated);
                $this->caisseService->syncCreditFromBanqueRevenue($created);

                return $created;
            });
            $this->logInfo('Recette créée', ['revenue_id' => $revenue->id, 'montant' => $revenue->montant]);

            return redirect()->route('revenues.index')->with('success', 'Recette enregistrée avec succès.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la création de la recette', ['data' => $request->all()]);
            throw $e;
        }
    }

    public function edit(Request $request, Revenue $revenue): View
    {
        $paroisseId = (int) ($revenue->paroisse_id ?: $this->resolveParoisseId($request));
        $categories = $this->revenueCategoriesWithTypesForParoisse($paroisseId, $revenue->revenue_type_id);

        return view('revenues.edit', compact('revenue', 'categories'));
    }

    public function update(Request $request, Revenue $revenue): RedirectResponse
    {
        try {
            $validated = $this->validateRevenue($request, $revenue);
            $validated['paroisse_id'] = $revenue->paroisse_id ?: $this->resolveParoisseId($request);
            if (empty($revenue->reference_paiement)) {
                $validated['reference_paiement'] = $this->generateReference();
            }

            DB::transaction(function () use ($revenue, $validated): void {
                $revenue->update($validated);
                $revenue->refresh(['category', 'type']);
                $this->caisseService->syncCreditFromBanqueRevenue($revenue);
            });
            $revenue->refresh(['category', 'type']);
            $this->logInfo('Recette mise à jour', ['revenue_id' => $revenue->id, 'montant' => $revenue->montant]);

            $catNom = $revenue->category?->nom ?? '—';
            $typeNom = $revenue->type?->nom ?? '—';
            $dateStr = $revenue->date_recette?->format('d/m/Y') ?? '—';
            $successMessage = "Recette catégorie({$catNom}) du type({$typeNom}) du {$dateStr} mise à jour.";

            return redirect()->route('revenues.index')->with('success', $successMessage);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la mise à jour de la recette', ['revenue_id' => $revenue->id]);
            throw $e;
        }
    }

    public function destroy(Revenue $revenue): RedirectResponse
    {
        try {
            DB::transaction(function () use ($revenue): void {
                $this->caisseService->removeCreditsLinkedToRevenue($revenue);
                $revenue->delete();
            });
            $this->logInfo('Recette supprimée logiquement', ['revenue_id' => $revenue->id]);

            return redirect()->route('revenues.index')->with('success', 'Recette supprimée.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la suppression de la recette', ['revenue_id' => $revenue->id]);
            throw $e;
        }
    }

    private function revenuesIndexFilteredQuery(Request $request): Builder
    {
        $query = Revenue::query();

        if ($request->filled('categorie')) {
            $query->whereHas('category', function ($builder) use ($request): void {
                $builder->where('code', $request->string('categorie')->value());
            });
        }

        if ($request->filled('type')) {
            $query->whereHas('type', function ($builder) use ($request): void {
                $builder->where('code', $request->string('type')->value());
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_recette', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_recette', '<=', $request->date('date_to'));
        }

        if ($request->filled('q')) {
            $search = mb_strtolower($request->string('q')->value());
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->whereRaw('LOWER(notes) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(reference_paiement) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(donateur_nom) LIKE ?', ["%{$search}%"]);
            });
        }

        return $query;
    }

    private function expensesSummaryQuery(Request $request): Builder
    {
        $query = Expense::query();

        if ($request->filled('date_from')) {
            $query->whereDate('date_depense', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_depense', '<=', $request->date('date_to'));
        }

        return $query;
    }

    /**
     * Catégories et types alignés sur le rapport « par catégories » (paroisse, actifs, tri).
     * En édition, inclut le type courant même s'il est inactif pour conserver l'affichage cohérent.
     *
     * @return Collection<int, RevenueCategory>
     */
    private function revenueCategoriesWithTypesForParoisse(int $paroisseId, ?int $includeTypeIdIfInactive = null): Collection
    {
        return RevenueCategory::query()
            ->where('paroisse_id', $paroisseId)
            ->where('actif', true)
            ->with([
                'types' => function ($query) use ($paroisseId, $includeTypeIdIfInactive): void {
                    $query->where('paroisse_id', $paroisseId)
                        ->where(function ($q) use ($includeTypeIdIfInactive): void {
                            $q->where('actif', true);
                            if ($includeTypeIdIfInactive !== null) {
                                $q->orWhere('id', $includeTypeIdIfInactive);
                            }
                        })
                        ->orderBy('ordre')
                        ->orderBy('nom');
                },
            ])
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();
    }

    /**
     * Paroisse pour charger les filtres de l'index (sans lever d'exception si aucune paroisse).
     */
    private function resolveParoisseIdForContext(Request $request): ?int
    {
        $userParoisseId = $request->user()?->paroisse_id;
        if (! empty($userParoisseId)) {
            return (int) $userParoisseId;
        }

        $fallback = Paroisse::query()->value('id');

        return $fallback !== null ? (int) $fallback : null;
    }

    private function validateRevenue(Request $request, ?Revenue $existingRevenue): array
    {
        $validated = $request->validate([
            'revenue_category_id' => ['required', 'exists:revenue_categories,id'],
            'revenue_type_id' => ['required', 'exists:revenue_types,id'],
            'date_recette' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'min:0'],
            'methode_paiement' => ['required', 'in:especes,cheque,virement,carte,mobile_money'],
            'notes' => ['nullable', 'string'],
            'donateur_nom' => ['nullable', 'string', 'max:255'],
            'donateur_telephone' => ['nullable', 'string', 'max:50'],
            'mois_location' => ['nullable', 'in:01,02,03,04,05,06,07,08,09,10,11,12'],
            'mois_capital' => ['nullable', 'in:01,02,03,04,05,06,07,08,09,10,11,12'],
        ]);

        $paroisseIdForRules = $existingRevenue !== null && ! empty($existingRevenue->paroisse_id)
            ? (int) $existingRevenue->paroisse_id
            : $this->resolveParoisseId($request);

        $category = RevenueCategory::find($validated['revenue_category_id']);
        $revenueType = RevenueType::find($validated['revenue_type_id']);

        if (! $category || (int) $category->paroisse_id !== $paroisseIdForRules) {
            throw ValidationException::withMessages([
                'revenue_category_id' => 'La catégorie choisie n\'appartient pas à votre paroisse ou n\'est pas utilisable.',
            ]);
        }

        if ($category->code === 'subvention') {
            throw ValidationException::withMessages([
                'revenue_category_id' => 'La catégorie Subvention est remplacée par les caisses (crédit direct ou virement).',
            ]);
        }

        if (! $revenueType
            || (int) $revenueType->paroisse_id !== $paroisseIdForRules
            || (int) $revenueType->revenue_category_id !== (int) $category->id) {
            throw ValidationException::withMessages([
                'revenue_type_id' => 'Le type choisi n\'est pas valide pour cette catégorie et cette paroisse.',
            ]);
        }

        $keepsSameInactiveType = $existingRevenue !== null
            && (int) $existingRevenue->revenue_type_id === (int) $validated['revenue_type_id']
            && ! $revenueType->actif;

        if (! $revenueType->actif && ! $keepsSameInactiveType) {
            throw ValidationException::withMessages([
                'revenue_type_id' => 'Ce type de recette est inactif. Choisissez un type actif ou contactez l\'administrateur.',
            ]);
        }

        $jourDepuisDate = $this->jourSemaineKeyFromDate($validated['date_recette']);
        $validated['jour_semaine'] = $jourDepuisDate;
        $validated['periode_messe'] = $jourDepuisDate === 'dimanche' ? 'dimanche' : 'semaine';

        if (SubventionMensuelle::isBanqueCategory($category)) {
            if (empty($validated['mois_capital']) || ! SubventionMensuelle::isValidMoisCapital($validated['mois_capital'])) {
                throw ValidationException::withMessages([
                    'mois_capital' => 'Le mois du capital (janvier–décembre) est obligatoire pour une recette Banque.',
                ]);
            }
            $validated['mois_location'] = null;
        } elseif ($category && $category->code === 'location' && $revenueType && in_array($revenueType->code, ['loyer-boutique', 'loyer_boutique'], true)) {
            if (empty($validated['mois_location'])) {
                throw ValidationException::withMessages([
                    'mois_location' => 'Le mois de location est obligatoire pour un loyer boutique.',
                ]);
            }
            $validated['mois_capital'] = null;
        } else {
            $validated['mois_location'] = null;
            $validated['mois_capital'] = null;
        }

        if (! $category || $category->code !== 'procure') {
            $validated['donateur_nom'] = null;
            $validated['donateur_telephone'] = null;
        } else {
            if (! empty($validated['donateur_nom'])) {
                $validated['donateur_nom'] = mb_strtoupper($validated['donateur_nom'], 'UTF-8');
            }
            if (! empty($validated['donateur_telephone'])) {
                $validated['donateur_telephone'] = $this->normalizePhone242($validated['donateur_telephone']);
            }
        }

        return $validated;
    }

    /**
     * Clé jour de la semaine (lundi…dimanche) dérivée de la date — toujours persistée pour les rapports.
     */
    private function jourSemaineKeyFromDate(string|\DateTimeInterface $date): string
    {
        $weekdayMap = [
            0 => 'dimanche',
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi',
        ];
        $autoDay = $weekdayMap[Carbon::parse($date)->dayOfWeek] ?? null;
        if ($autoDay === null) {
            throw ValidationException::withMessages([
                'date_recette' => 'Impossible de déterminer le jour de la semaine pour cette date.',
            ]);
        }

        return $autoDay;
    }

    private function normalizePhone242(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '242')) {
            return '242'.substr($digits, 3);
        }

        return '242'.$digits;
    }

    private function generateReference(): string
    {
        return 'REV-'.now()->format('YmdHis').'-'.strtoupper((string) str()->random(4));
    }

    private function resolveParoisseId(Request $request): int
    {
        $userParoisseId = $request->user()?->paroisse_id;
        if (! empty($userParoisseId)) {
            return (int) $userParoisseId;
        }

        $fallbackParoisseId = Paroisse::query()->value('id');
        if (! empty($fallbackParoisseId)) {
            return (int) $fallbackParoisseId;
        }

        throw ValidationException::withMessages([
            'revenue_category_id' => 'Aucune paroisse disponible. Créez une paroisse avant d\'enregistrer une recette.',
        ]);
    }
}
