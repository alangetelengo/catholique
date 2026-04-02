<?php

namespace App\Http\Controllers;

use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Support\PaginationPerPage;
use App\Traits\LogsErrors;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RevenueController extends Controller
{
    use LogsErrors;

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
            $montantDerniereRecette = $this->revenuesIndexFilteredQuery($request)
                ->orderByDesc('date_recette')
                ->orderByDesc('id')
                ->value('montant');
            $montantDerniereRecette = $montantDerniereRecette !== null ? (float) $montantDerniereRecette : null;

            $categories = RevenueCategory::query()->with('types')->orderBy('ordre')->orderBy('nom')->get();

            return view('revenues.index', compact(
                'revenues',
                'categories',
                'totalMontantRecettes',
                'montantDerniereRecette',
            ));
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement des recettes');
            throw $e;
        }
    }

    public function create(): View
    {
        $revenue = new Revenue([
            'date_recette' => now()->toDateString(),
            'methode_paiement' => 'especes',
        ]);
        $categories = RevenueCategory::query()->with('types')->where('actif', true)->orderBy('ordre')->orderBy('nom')->get();

        return view('revenues.create', compact('revenue', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validateRevenue($request);
            $validated['paroisse_id'] = $this->resolveParoisseId($request);
            $validated['created_by'] = $request->user()?->id;
            $validated['reference_paiement'] = $this->generateReference();

            $revenue = Revenue::create($validated);
            $this->logInfo('Recette créée', ['revenue_id' => $revenue->id, 'montant' => $revenue->montant]);

            return redirect()->route('revenues.index')->with('success', 'Recette enregistrée avec succès.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la création de la recette', ['data' => $request->all()]);
            throw $e;
        }
    }

    public function edit(Revenue $revenue): View
    {
        $categories = RevenueCategory::query()->with('types')->where('actif', true)->orderBy('ordre')->orderBy('nom')->get();

        return view('revenues.edit', compact('revenue', 'categories'));
    }

    public function update(Request $request, Revenue $revenue): RedirectResponse
    {
        try {
            $validated = $this->validateRevenue($request);
            $validated['paroisse_id'] = $revenue->paroisse_id ?: $this->resolveParoisseId($request);
            if (empty($revenue->reference_paiement)) {
                $validated['reference_paiement'] = $this->generateReference();
            }

            $revenue->update($validated);
            $this->logInfo('Recette mise à jour', ['revenue_id' => $revenue->id, 'montant' => $revenue->montant]);

            return redirect()->route('revenues.index')->with('success', 'Recette mise à jour.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la mise à jour de la recette', ['revenue_id' => $revenue->id]);
            throw $e;
        }
    }

    public function destroy(Revenue $revenue): RedirectResponse
    {
        try {
            $revenue->delete();
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

    private function validateRevenue(Request $request): array
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
            'jour_semaine' => ['nullable', 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche'],
            'mois_location' => ['nullable', 'in:01,02,03,04,05,06,07,08,09,10,11,12'],
        ]);

        $category = RevenueCategory::find($validated['revenue_category_id']);
        $revenueType = RevenueType::find($validated['revenue_type_id']);

        if ($category && $category->code === 'quete_ordinaire') {
            $weekdayMap = [
                0 => 'dimanche',
                1 => 'lundi',
                2 => 'mardi',
                3 => 'mercredi',
                4 => 'jeudi',
                5 => 'vendredi',
                6 => 'samedi',
            ];
            $autoDay = $weekdayMap[Carbon::parse($validated['date_recette'])->dayOfWeek] ?? null;
            if ($autoDay === null) {
                throw ValidationException::withMessages([
                    'date_recette' => 'Impossible de déterminer le jour de la semaine pour cette date.',
                ]);
            }

            $validated['jour_semaine'] = $autoDay;
            $validated['periode_messe'] = $autoDay === 'dimanche' ? 'dimanche' : 'semaine';
            $validated['mois_location'] = null;
        } elseif ($category && $category->code === 'location' && $revenueType && in_array($revenueType->code, ['loyer-boutique', 'loyer_boutique'], true)) {
            if (empty($validated['mois_location'])) {
                throw ValidationException::withMessages([
                    'mois_location' => 'Le mois de location est obligatoire pour un loyer boutique.',
                ]);
            }

            $validated['jour_semaine'] = null;
            $validated['periode_messe'] = null;
        } else {
            $validated['jour_semaine'] = null;
            $validated['periode_messe'] = null;
            $validated['mois_location'] = null;
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
