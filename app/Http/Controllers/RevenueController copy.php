<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use App\Models\Revenue;
use App\Models\Paroisse;
use Illuminate\View\View;
use App\Traits\LogsErrors;
use App\Helpers\FlashAlert;
use App\Models\RevenueType;
use Illuminate\Http\Request;
use App\Models\RevenueCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class RevenueController extends Controller
{
    use LogsErrors;

    public function __construct()
    {
        $this->middleware('permission:view_revenues')->only(['index']);
        $this->middleware('permission:create_revenues')->only(['create', 'store']);
        $this->middleware('permission:edit_revenues')->only(['edit', 'update']);
        $this->middleware('permission:delete_revenues')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        try {
            $user = $request->user();

            $query = Revenue::query()
                ->with(['paroisse', 'category', 'type', 'event'])
                ->orderByDesc('created_at');

            if (! $user->hasRole('super_admin')) {
                $query->where('paroisse_id', $user->paroisse_id);
            }

            // Par défaut : uniquement l'année en cours ; le filtre date permet d'afficher d'autres périodes
            if (! $request->filled('date_from') && ! $request->filled('date_to')) {
                $query->whereYear('date_recette', now()->year);
            }

            if ($request->filled('categorie')) {
                $query->whereHas('category', function ($q) use ($request): void {
                    $q->where('code', $request->string('categorie')->value());
                });
            }

            if ($request->filled('type')) {
                $query->whereHas('type', function ($q) use ($request): void {
                    $q->where('code', $request->string('type')->value());
                });
            }

            if ($request->filled('paroisse_id')) {
                $query->where('paroisse_id', $request->integer('paroisse_id'));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('date_recette', '>=', $request->date('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('date_recette', '<=', $request->date('date_to'));
            }

            if ($request->filled('q')) {
                $q = $request->string('q')->lower()->value();
                $query->where(function ($sub) use ($q): void {
                    $sub->whereRaw('LOWER(notes) LIKE ?', ["%{$q}%"])
                        ->orWhereRaw('LOWER(reference_paiement) LIKE ?', ["%{$q}%"]);
                });
            }

            $revenues = $query->paginate(15)->withQueryString();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : collect();

            $paroisseIdFilter = $request->filled('paroisse_id') ? $request->integer('paroisse_id') : $user->paroisse_id;
            $categories = $paroisseIdFilter
                ? RevenueCategory::where('paroisse_id', $paroisseIdFilter)->orderBy('ordre')->get()->unique('code')->values()
                : RevenueCategory::orderBy('ordre')->get();

            return view('revenues.index', [
                'revenues' => $revenues,
                'paroisses' => $paroisses,
                'categories' => $categories,
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement de la liste des recettes');
            FlashAlert::error('Une erreur est survenue lors du chargement des recettes.');

            return view('revenues.index', [
                'revenues' => collect(),
                'paroisses' => collect(),
                'categories' => collect(),
            ]);
        }
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        $paroisses = $user->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($user->paroisse_id)->get();

        $paroisseId = $user->hasRole('super_admin') && $request->filled('paroisse_id')
            ? $request->integer('paroisse_id')
            : $user->paroisse_id;
        $categories = $paroisseId
            ? RevenueCategory::with('types')->where('paroisse_id', $paroisseId)->orderBy('ordre')->get()->unique('code')->values()
            : RevenueCategory::with('types')->orderBy('ordre')->get();

        return view('revenues.create', [
            'revenue' => new Revenue(),
            'paroisses' => $paroisses,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['nullable', 'exists:paroisses,id'],
                'revenue_category_id' => ['required', 'exists:revenue_categories,id'],
                'revenue_type_id' => ['required', 'exists:revenue_types,id'],
                'date_recette' => ['required', 'date'],
                'montant' => ['required', 'numeric', 'min:0'],
                'methode_paiement' => ['required', 'in:especes,cheque,virement,carte,mobile_money'],
                'notes' => ['nullable', 'string'],
                'donateur_nom' => ['nullable', 'string', 'max:255'],
                'donateur_telephone' => ['nullable', 'string', 'max:50'],
            ]);

            $category = RevenueCategory::find($validated['revenue_category_id']);
            $revenueType = RevenueType::find($validated['revenue_type_id']);

            // Procure : champs donateur optionnels (sinon on les vide)
            if (! $category || $category->code !== 'procure') {
                $validated['donateur_nom'] = null;
                $validated['donateur_telephone'] = null;
            } else {
                // Nom en majuscules, téléphone avec préfixe 242
                if (! empty($validated['donateur_nom'])) {
                    $validated['donateur_nom'] = mb_strtoupper($validated['donateur_nom'], 'UTF-8');
                }
                if (! empty($validated['donateur_telephone'])) {
                    $validated['donateur_telephone'] = self::normalizePhone242($validated['donateur_telephone']);
                }
            }

            // Quête ordinaire : gestion du jour de la semaine
            if ($category && $category->code === 'quete_ordinaire') {
                $jourData = $request->validate([
                    'jour_semaine' => ['required', 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche'],
                ]);
                $validated['jour_semaine'] = $jourData['jour_semaine'];
                $validated['periode_messe'] = $jourData['jour_semaine'] === 'dimanche' ? 'dimanche' : 'semaine';

                // Cohérence entre date et jour de la semaine
                $weekdayMap = [
                    0 => 'dimanche',
                    1 => 'lundi',
                    2 => 'mardi',
                    3 => 'mercredi',
                    4 => 'jeudi',
                    5 => 'vendredi',
                    6 => 'samedi',
                ];
                $expectedJour = $weekdayMap[Carbon::parse($validated['date_recette'])->dayOfWeek] ?? null;
                if ($expectedJour !== null && $jourData['jour_semaine'] !== $expectedJour) {
                    throw ValidationException::withMessages([
                        'jour_semaine' => 'Le jour choisi ne correspond pas à la date de la recette (devrait être ' . ucfirst($expectedJour) . ').',
                    ]);
                }
                $validated['mois_location'] = null;
            }
            // Location + Loyer Boutique : gestion du mois de location
            elseif ($category && $category->code === 'location' && $revenueType && $revenueType->code === 'loyer_boutique') {
                $moisData = $request->validate([
                    'mois_location' => ['required', 'regex:/^\d{4}-\d{2}$/'],
                ]);
                $validated['mois_location'] = $moisData['mois_location'];
                $validated['jour_semaine'] = null;
                $validated['periode_messe'] = null;
            } else {
                $validated['jour_semaine'] = null;
                $validated['periode_messe'] = null;
                $validated['mois_location'] = null;
            }

            if (! $user->hasRole('super_admin')) {
                $validated['paroisse_id'] = $user->paroisse_id;
            }

            // Référence paiement générée automatiquement par le contrôleur
            $validated['reference_paiement'] = 'REV-' . now()->format('YmdHis') . '-' . strtoupper(str()->random(4));

            $validated['created_by'] = $user->id;

            Revenue::create($validated);

            FlashAlert::success('Recette enregistrée avec succès.');

            // return redirect()->route('revenues.index');
            return redirect()->back();
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la création de la recette', ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de l\'enregistrement de la recette.');

            return redirect()->back()->withInput();
        }
    }

    public function edit(Request $request, Revenue $revenue): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole('super_admin') && $revenue->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Vous ne pouvez modifier que les recettes de votre paroisse.');

            return redirect()->route('revenues.index');
        }

        $paroisses = $user->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($user->paroisse_id)->get();

        $paroisseId = $revenue->paroisse_id;
        $categories = $paroisseId
            ? RevenueCategory::with('types')->where('paroisse_id', $paroisseId)->orderBy('ordre')->get()->unique('code')->values()
            : RevenueCategory::with('types')->orderBy('ordre')->get();

        return view('revenues.edit', [
            'revenue' => $revenue,
            'paroisses' => $paroisses,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Revenue $revenue): RedirectResponse
    {
        try {
            $user = $request->user();

            if (! $user->hasRole('super_admin') && $revenue->paroisse_id !== $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez modifier que les recettes de votre paroisse.');

                return redirect()->route('revenues.index');
            }

            $validated = $request->validate([
                'paroisse_id' => ['nullable', 'exists:paroisses,id'],
                'revenue_category_id' => ['required', 'exists:revenue_categories,id'],
                'revenue_type_id' => ['required', 'exists:revenue_types,id'],
                'date_recette' => ['required', 'date'],
                'montant' => ['required', 'numeric', 'min:0'],
                'methode_paiement' => ['required', 'in:especes,cheque,virement,carte,mobile_money'],
                'notes' => ['nullable', 'string'],
                'donateur_nom' => ['nullable', 'string', 'max:255'],
                'donateur_telephone' => ['nullable', 'string', 'max:50'],
            ]);

            $category = RevenueCategory::find($validated['revenue_category_id']);
            $revenueType = RevenueType::find($validated['revenue_type_id']);

            // Procure : champs donateur optionnels (sinon on les vide)
            if (! $category || $category->code !== 'procure') {
                $validated['donateur_nom'] = null;
                $validated['donateur_telephone'] = null;
            } else {
                // Nom en majuscules, téléphone avec préfixe 242
                if (! empty($validated['donateur_nom'])) {
                    $validated['donateur_nom'] = mb_strtoupper($validated['donateur_nom'], 'UTF-8');
                }
                if (! empty($validated['donateur_telephone'])) {
                    $validated['donateur_telephone'] = self::normalizePhone242($validated['donateur_telephone']);
                }
            }

            // Quête ordinaire : gestion du jour de la semaine
            if ($category && $category->code === 'quete_ordinaire') {
                $jourData = $request->validate([
                    'jour_semaine' => ['required', 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche'],
                ]);
                $validated['jour_semaine'] = $jourData['jour_semaine'];
                $validated['periode_messe'] = $jourData['jour_semaine'] === 'dimanche' ? 'dimanche' : 'semaine';

                $weekdayMap = [
                    0 => 'dimanche',
                    1 => 'lundi',
                    2 => 'mardi',
                    3 => 'mercredi',
                    4 => 'jeudi',
                    5 => 'vendredi',
                    6 => 'samedi',
                ];
                $expectedJour = $weekdayMap[Carbon::parse($validated['date_recette'])->dayOfWeek] ?? null;
                if ($expectedJour !== null && $jourData['jour_semaine'] !== $expectedJour) {
                    throw ValidationException::withMessages([
                        'jour_semaine' => 'Le jour choisi ne correspond pas à la date de la recette (devrait être ' . ucfirst($expectedJour) . ').',
                    ]);
                }
                $validated['mois_location'] = null;
            }
            // Location + Loyer Boutique : gestion du mois de location
            elseif ($category && $category->code === 'location' && $revenueType && $revenueType->code === 'loyer_boutique') {
                $moisData = $request->validate([
                    'mois_location' => ['required', 'regex:/^\d{4}-\d{2}$/'],
                ]);
                $validated['mois_location'] = $moisData['mois_location'];
                $validated['jour_semaine'] = null;
                $validated['periode_messe'] = null;
            } else {
                $validated['jour_semaine'] = null;
                $validated['periode_messe'] = null;
                $validated['mois_location'] = null;
            }

            if (! $user->hasRole('super_admin')) {
                $validated['paroisse_id'] = $user->paroisse_id;
            }

            // Référence paiement : générer uniquement si pas encore définie
            if (empty($revenue->reference_paiement)) {
                $validated['reference_paiement'] = 'REV-' . now()->format('YmdHis') . '-' . strtoupper(str()->random(4));
            }

            $revenue->update($validated);

            FlashAlert::success('Recette mise à jour avec succès.');

            return redirect()->route('revenues.index');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la mise à jour de la recette', ['revenue_id' => $revenue->id]);
            FlashAlert::error('Une erreur est survenue lors de la mise à jour de la recette.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, Revenue $revenue): RedirectResponse
    {
        try {
            $user = $request->user();

            if (! $user->hasRole('super_admin') && $revenue->paroisse_id !== $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez supprimer que les recettes de votre paroisse.');

                return redirect()->route('revenues.index');
            }

            $revenue->delete();

            FlashAlert::success('Recette supprimée avec succès.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la suppression de la recette', ['revenue_id' => $revenue->id]);
            FlashAlert::error('Une erreur est survenue lors de la suppression de la recette.');
        }

        return redirect()->route('revenues.index');
    }

    /**
     * Normalise le numéro de téléphone avec le préfixe 242 (Congo).
     */
    private static function normalizePhone242(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '242')) {
            return '242' . substr($digits, 3);
        }

        return '242' . $digits;
    }
}

