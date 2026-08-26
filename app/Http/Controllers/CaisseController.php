<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use App\Models\CaisseMouvement;
use App\Models\RevenueType;
use App\Services\CaisseService;
use App\Traits\LogsErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class CaisseController extends Controller
{
    use LogsErrors;

    public function __construct(
        protected CaisseService $caisseService
    ) {}

    public function index(Request $request): View
    {
        $paroisseId = (int) $request->user()?->paroisse_id;
        $caisses = $this->caisseService->getCaissesAvecSolde($paroisseId);
        $envelopesCapital = $this->caisseService->getEnvelopesCapital($paroisseId);

        return view('caisses.index', compact('caisses', 'envelopesCapital'));
    }

    public function show(Request $request, Caisse $caisse): View
    {
        $this->authorizeCaisse($request, $caisse);

        $caisse->solde_disponible = $this->caisseService->getSolde($caisse);
        $mouvements = CaisseMouvement::query()
            ->where('caisse_id', $caisse->id)
            ->with(['revenueType', 'expense'])
            ->orderByDesc('date_mouvement')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();
        $envelopesCapital = $caisse->isTresorerie()
            ? $this->caisseService->getEnvelopesCapital((int) $caisse->paroisse_id)
            : collect();

        return view('caisses.show', compact('caisse', 'mouvements', 'envelopesCapital'));
    }

    public function createCredit(Request $request): View
    {
        $paroisseId = (int) $request->user()?->paroisse_id;
        $caisses = $this->caisseService->getCaissesAvecSolde($paroisseId, onlyOperatives: true);

        return view('caisses.credit', compact('caisses'));
    }

    public function storeCredit(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'caisse_id' => ['required', 'integer', 'exists:caisses,id'],
                'montant' => ['required', 'numeric', 'min:0.01'],
                'date_mouvement' => ['required', 'date'],
                'libelle' => ['required', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
            ]);

            $caisse = Caisse::query()->findOrFail($validated['caisse_id']);
            $this->authorizeCaisse($request, $caisse);

            $this->caisseService->creditDirect(
                $caisse,
                (float) $validated['montant'],
                $validated['date_mouvement'],
                $validated['libelle'],
                $validated['notes'] ?? null,
                $request->user()?->id
            );

            return redirect()->route('caisses.show', $caisse)->with('success', 'Crédit direct enregistré sur la caisse.');
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['caisse_id' => $e->getMessage()]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur crédit direct caisse');
            throw $e;
        }
    }

    public function createVirement(Request $request): View
    {
        $paroisseId = (int) $request->user()?->paroisse_id;
        $tresorerie = $this->caisseService->getTresorerie($paroisseId);
        $tresorerie->solde_disponible = $this->caisseService->getSolde($tresorerie);
        $caisses = $this->caisseService->getCaissesAvecSolde($paroisseId, onlyOperatives: true);
        $envelopesCapital = $this->caisseService->getEnvelopesCapital($paroisseId);
        $revenueTypes = RevenueType::query()
            ->where('paroisse_id', $paroisseId)
            ->where('actif', true)
            ->whereHas('category', fn ($q) => $q->where('actif', true)->where('code', '!=', 'banque')->where('code', '!=', 'subvention'))
            ->with('category')
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get()
            ->map(function (RevenueType $type) {
                $type->solde_disponible = $this->caisseService->getSoldeDisponibleRevenueType($type);

                return $type;
            })
            ->filter(fn (RevenueType $type) => round((float) $type->solde_disponible, 2) > 0)
            ->values();

        return view('caisses.virement', compact('tresorerie', 'caisses', 'revenueTypes', 'envelopesCapital'));
    }

    public function storeVirement(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'mode' => ['required', 'in:tresorerie,recette'],
                'caisse_id' => ['required', 'integer', 'exists:caisses,id'],
                'revenue_type_id' => ['nullable', 'integer', 'exists:revenue_types,id'],
                'mois_capital' => ['nullable', 'in:01,02,03,04,05,06,07,08,09,10,11,12'],
                'annee_capital' => ['nullable', 'integer', 'min:2000', 'max:2100'],
                'montant' => ['required', 'numeric', 'min:0.01'],
                'date_mouvement' => ['required', 'date'],
                'libelle' => ['required', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
            ]);

            $caisse = Caisse::query()->findOrFail($validated['caisse_id']);
            $this->authorizeCaisse($request, $caisse);

            if ($validated['mode'] === 'tresorerie') {
                if (empty($validated['mois_capital']) || empty($validated['annee_capital'])) {
                    throw ValidationException::withMessages([
                        'mois_capital' => 'Le mois du capital (revenu principal) est obligatoire pour un virement depuis la trésorerie.',
                    ]);
                }

                $this->caisseService->virementTresorerieVersCaisse(
                    $caisse,
                    (float) $validated['montant'],
                    $validated['date_mouvement'],
                    $validated['libelle'],
                    $validated['mois_capital'],
                    (int) $validated['annee_capital'],
                    $validated['notes'] ?? null,
                    $request->user()?->id
                );
            } else {
                if (empty($validated['revenue_type_id'])) {
                    throw ValidationException::withMessages([
                        'revenue_type_id' => 'Choisissez le type de recette source.',
                    ]);
                }

                $revenueType = RevenueType::query()->findOrFail($validated['revenue_type_id']);
                if ((int) $revenueType->paroisse_id !== (int) $request->user()?->paroisse_id) {
                    throw ValidationException::withMessages([
                        'revenue_type_id' => 'Type de recette invalide pour votre paroisse.',
                    ]);
                }

                $this->caisseService->alimentationDepuisRecette(
                    $caisse,
                    $revenueType,
                    (float) $validated['montant'],
                    $validated['date_mouvement'],
                    $validated['libelle'],
                    $validated['notes'] ?? null,
                    $request->user()?->id
                );
            }

            return redirect()->route('caisses.show', $caisse)->with('success', 'Alimentation de caisse enregistrée.');
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['montant' => $e->getMessage()]);
        } catch (Throwable $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }
            $this->logError($e, 'Erreur alimentation caisse');
            throw $e;
        }
    }

    private function authorizeCaisse(Request $request, Caisse $caisse): void
    {
        if ((int) $caisse->paroisse_id !== (int) $request->user()?->paroisse_id) {
            abort(403);
        }
    }
}
