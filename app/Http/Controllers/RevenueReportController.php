<?php

namespace App\Http\Controllers;

use App\Models\FinancialReport;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Traits\LogsErrors;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class RevenueReportController extends Controller
{
    use LogsErrors;

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = FinancialReport::query()
            ->with(['paroisse', 'createdBy'])
            ->whereIn('periode_type', ['total', 'revenues_by_category'])
            ->orderByDesc('created_at');

        if (! $user?->hasRole('super_admin')) {
            $query->where('paroisse_id', $user?->paroisse_id);
        } elseif ($request->filled('paroisse_id')) {
            $query->where('paroisse_id', (int) $request->integer('paroisse_id'));
        }

        if ($request->filled('year')) {
            $query->whereYear('date_debut', (int) $request->integer('year'));
        }

        if ($request->filled('report_target')) {
            $mapped = $request->string('report_target')->value() === 'categorie' ? 'revenues_by_category' : 'total';
            $query->where('periode_type', $mapped);
        }

        $reports = $query->paginate(20)->withQueryString();
        $paroisses = $user?->hasRole('super_admin') ? Paroisse::query()->orderBy('nom')->get() : collect();

        return view('revenue-reports.index', compact('reports', 'paroisses'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $selectedParoisseId = $this->resolveParoisseId($request);
        if (empty($selectedParoisseId)) {
            $selectedParoisseId = (int) Paroisse::query()->value('id');
        }

        $paroisses = $user?->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : Paroisse::query()->whereKey($selectedParoisseId)->get();

        $categories = RevenueCategory::query()
            ->where('actif', true)
            ->when($selectedParoisseId, fn ($q) => $q->where('paroisse_id', $selectedParoisseId))
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        return view('revenue-reports.create', compact('paroisses', 'categories', 'selectedParoisseId'));
    }

    public function store(Request $request): RedirectResponse|Response
    {
        try {
            $validated = $this->validatePayload($request);
            $reportData = $this->buildReportData($validated);
            // Deux actions possibles depuis le formulaire:
            // - preview: générer un rapport non persistant
            // - save: générer puis enregistrer en base
            $actionMode = $request->input('action_mode', 'save');

            if ($actionMode === 'preview') {
                $this->logInfo('Rapport recettes généré sans enregistrement', [
                    'paroisse_id' => $validated['paroisse_id'],
                    'period_kind' => $validated['period_kind'],
                    'report_target' => $validated['report_target'],
                ]);

                return response()->view('revenue-reports.preview', [
                    'reportData' => $reportData,
                    'input' => $validated,
                    'paroisse' => Paroisse::query()->find($validated['paroisse_id']),
                ]);
            }

            $report = FinancialReport::create([
                'paroisse_id' => $validated['paroisse_id'],
                'periode_type' => $validated['report_target'] === 'categorie' ? 'revenues_by_category' : 'total',
                'date_debut' => $reportData['date_debut'],
                'date_fin' => $reportData['date_fin'],
                'total_recettes' => $reportData['total_recettes'],
                'total_depenses' => 0,
                'solde' => $reportData['total_recettes'],
                'details_recettes' => $reportData['details_recettes'],
                'details_depenses' => [],
                'created_by' => $request->user()?->id,
            ]);

            $this->logInfo('Rapport recettes créé', ['report_id' => $report->id]);

            return redirect()->route('revenue-reports.show', $report)->with('success', 'Rapport de recettes généré et enregistré.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la création du rapport de recettes', ['data' => $request->all()]);

            return back()->withInput()->with('error', 'Impossible de générer le rapport de recettes.');
        }
    }

    public function show(FinancialReport $revenueReport): View
    {
        $this->authorizeAccess($revenueReport, request()->user()?->id, request()->user()?->paroisse_id, request()->user()?->hasRole('super_admin'));

        $details = (array) ($revenueReport->details_recettes ?? []);
        $rows = collect($details['revenues'] ?? []);
        $byType = collect($details['by_type'] ?? []);

        return view('revenue-reports.show', [
            'report' => $revenueReport,
            'details' => $details,
            'rows' => $rows,
            'byType' => $byType,
        ]);
    }

    public function print(FinancialReport $revenueReport): View
    {
        $this->authorizeAccess($revenueReport, request()->user()?->id, request()->user()?->paroisse_id, (bool) request()->user()?->hasRole('super_admin'));

        $details = (array) ($revenueReport->details_recettes ?? []);
        $rows = collect($details['revenues'] ?? []);
        $paroisse = $revenueReport->paroisse;

        return view('revenue-reports.print', [
            'report' => $revenueReport,
            'details' => $details,
            'rows' => $rows,
            'paroisse' => $paroisse,
            'signataires' => [
                ['titre' => 'Le Curé', 'nom' => 'Nom et signature'],
                ['titre' => 'Le Gestionnaire', 'nom' => 'Nom et signature'],
                ['titre' => 'Le Vicaire Économe', 'nom' => 'Nom et signature'],
            ],
        ]);
    }

    public function edit(FinancialReport $revenueReport, Request $request): View
    {
        $this->authorizeAccess($revenueReport, $request->user()?->id, $request->user()?->paroisse_id, $request->user()?->hasRole('super_admin'));

        $selectedParoisseId = (int) $revenueReport->paroisse_id;
        $paroisses = $request->user()?->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : Paroisse::query()->whereKey($selectedParoisseId)->get();

        $categories = RevenueCategory::query()
            ->where('actif', true)
            ->where('paroisse_id', $selectedParoisseId)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        $details = (array) ($revenueReport->details_recettes ?? []);

        return view('revenue-reports.edit', compact('revenueReport', 'paroisses', 'categories', 'details'));
    }

    public function update(Request $request, FinancialReport $revenueReport): RedirectResponse
    {
        try {
            $this->authorizeAccess($revenueReport, $request->user()?->id, $request->user()?->paroisse_id, $request->user()?->hasRole('super_admin'));

            $validated = $this->validatePayload($request);
            $reportData = $this->buildReportData($validated);

            $revenueReport->update([
                'paroisse_id' => $validated['paroisse_id'],
                'periode_type' => $validated['report_target'] === 'categorie' ? 'revenues_by_category' : 'total',
                'date_debut' => $reportData['date_debut'],
                'date_fin' => $reportData['date_fin'],
                'total_recettes' => $reportData['total_recettes'],
                'total_depenses' => 0,
                'solde' => $reportData['total_recettes'],
                'details_recettes' => $reportData['details_recettes'],
            ]);

            $this->logInfo('Rapport recettes mis à jour', ['report_id' => $revenueReport->id]);

            return redirect()->route('revenue-reports.show', $revenueReport)->with('success', 'Rapport de recettes mis à jour.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la mise à jour du rapport de recettes', ['report_id' => $revenueReport->id]);

            return back()->withInput()->with('error', 'Impossible de mettre à jour le rapport.');
        }
    }

    public function destroy(FinancialReport $revenueReport): RedirectResponse
    {
        try {
            $this->authorizeAccess($revenueReport, request()->user()?->id, request()->user()?->paroisse_id, request()->user()?->hasRole('super_admin'));
            $revenueReport->delete();
            $this->logInfo('Rapport recettes supprimé logiquement', ['report_id' => $revenueReport->id]);

            return redirect()->route('revenue-reports.index')->with('success', 'Rapport supprimé.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la suppression du rapport de recettes', ['report_id' => $revenueReport->id]);

            return back()->with('error', 'Suppression impossible.');
        }
    }

    private function validatePayload(Request $request): array
    {
        $user = $request->user();

        $validated = $request->validate([
            'paroisse_id' => ['required', 'integer', Rule::exists('paroisses', 'id')],
            'report_target' => ['required', Rule::in(['global', 'categorie'])],
            'period_kind' => ['required', Rule::in(['mensuel', 'annuel'])],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'revenue_category_id' => ['nullable', 'integer', Rule::exists('revenue_categories', 'id')],
        ]);

        if (! $user?->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user?->paroisse_id) {
            abort(403);
        }

        if ($validated['period_kind'] === 'mensuel' && empty($validated['month'])) {
            throw ValidationException::withMessages([
                'month' => 'Le mois est obligatoire pour un rapport mensuel.',
            ]);
        }

        if ($validated['report_target'] === 'categorie' && empty($validated['revenue_category_id'])) {
            throw ValidationException::withMessages([
                'revenue_category_id' => 'La catégorie est obligatoire pour un rapport par catégorie.',
            ]);
        }

        return $validated;
    }

    private function buildReportData(array $validated): array
    {
        $year = (int) $validated['year'];
        $month = (int) ($validated['month'] ?? now()->month);
        $isMonthly = $validated['period_kind'] === 'mensuel';
        $categoryId = $validated['report_target'] === 'categorie' ? (int) $validated['revenue_category_id'] : null;

        $dateDebut = $isMonthly
            ? Carbon::create($year, $month, 1)->startOfMonth()
            : Carbon::create($year, 1, 1)->startOfYear();
        $dateFin = $isMonthly
            ? $dateDebut->copy()->endOfMonth()
            : $dateDebut->copy()->endOfYear();

        $query = Revenue::query()
            ->with(['category', 'type'])
            ->where('paroisse_id', (int) $validated['paroisse_id'])
            ->where('statut', 'valide')
            ->whereDate('date_recette', '>=', $dateDebut)
            ->whereDate('date_recette', '<=', $dateFin);

        if (! empty($categoryId)) {
            $query->where('revenue_category_id', $categoryId);
        }

        $revenues = $query->orderBy('date_recette')->orderBy('id')->get();
        $totalRecettes = (float) $revenues->sum('montant');
        $category = ! empty($categoryId) ? RevenueCategory::query()->find($categoryId) : null;

        $byType = [];
        foreach ($revenues->groupBy('revenue_type_id') as $typeId => $items) {
            $first = $items->first();
            $byType[] = [
                'type_id' => $typeId,
                'type_nom' => $first?->type?->nom ?? 'Sans type',
                'montant' => (float) $items->sum('montant'),
                'count' => $items->count(),
            ];
        }

        return [
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'total_recettes' => $totalRecettes,
            'details_recettes' => [
                'report_target' => $validated['report_target'],
                'period_kind' => $validated['period_kind'],
                'month' => $isMonthly ? $month : null,
                'year' => $year,
                'revenue_category_id' => $categoryId,
                'revenue_category_nom' => $category?->nom,
                'by_type' => $byType,
                'revenues' => $revenues->map(fn (Revenue $r) => [
                    'id' => $r->id,
                    'date' => optional($r->date_recette)->format('Y-m-d'),
                    'category' => $r->category?->nom,
                    'type' => $r->type?->nom,
                    'reference' => $r->reference_paiement,
                    'montant' => (float) $r->montant,
                ])->values()->all(),
            ],
        ];
    }

    private function resolveParoisseId(Request $request): ?int
    {
        $sessionParoisseId = (int) $request->session()->get('active_paroisse_id', 0);

        if ($request->user()?->hasRole('super_admin')) {
            if ($request->filled('paroisse_id')) {
                return (int) $request->integer('paroisse_id');
            }

            if (! empty($sessionParoisseId)) {
                return $sessionParoisseId;
            }

            return (int) (Paroisse::query()->value('id') ?? 0);
        }

        return (int) ($request->user()?->paroisse_id ?: $sessionParoisseId ?: Paroisse::query()->value('id'));
    }

    private function authorizeAccess(FinancialReport $report, ?int $userId, ?int $userParoisseId, bool $isSuperAdmin): void
    {
        if ($isSuperAdmin) {
            return;
        }

        if ((int) $report->paroisse_id !== (int) $userParoisseId) {
            abort(403);
        }
    }
}

