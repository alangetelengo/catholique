<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\FinancialReport;
use App\Models\Paroisse;
use App\Support\PaginationPerPage;
use App\Traits\LogsErrors;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ChargesFixesReportController extends Controller
{
    use LogsErrors;

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = FinancialReport::query()
            ->with(['paroisse', 'createdBy'])
            ->where('periode_type', 'charges_fixes')
            ->orderByDesc('created_at');

        if (! $user?->hasRole('super_admin')) {
            $query->where('paroisse_id', $user?->paroisse_id);
        } elseif ($request->filled('paroisse_id')) {
            $query->where('paroisse_id', (int) $request->integer('paroisse_id'));
        }

        if ($request->filled('year')) {
            $query->whereYear('date_debut', (int) $request->integer('year'));
        }

        $reports = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $paroisses = $user?->hasRole('super_admin') ? Paroisse::query()->orderBy('nom')->get() : collect();

        return view('charges-fixes-reports.index', compact('reports', 'paroisses'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $selectedParoisseId = (int) ($user?->paroisse_id ?: Paroisse::query()->value('id'));
        $paroisses = $user?->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : Paroisse::query()->whereKey($selectedParoisseId)->get();

        return view('charges-fixes-reports.create', compact('paroisses', 'selectedParoisseId'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validatePayload($request);
            $data = $this->buildData($validated);

            $report = FinancialReport::create([
                'paroisse_id' => $validated['paroisse_id'],
                'periode_type' => 'charges_fixes',
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'total_recettes' => 0,
                'total_depenses' => $data['total_depenses'],
                'solde' => 0,
                'details_recettes' => [],
                'details_depenses' => $data['details_depenses'],
                'created_by' => $request->user()?->id,
            ]);

            $this->logInfo('Rapport charges fixes créé', ['report_id' => $report->id]);

            return redirect()->route('charges-fixes-reports.show', $report)->with('success', 'Rapport charges fixes enregistré.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur création rapport charges fixes', ['data' => $request->all()]);

            return back()->withInput()->with('error', 'Impossible de créer le rapport.');
        }
    }

    public function show(FinancialReport $chargesFixesReport): View
    {
        $this->authorizeAccess($chargesFixesReport, request()->user()?->paroisse_id, (bool) request()->user()?->hasRole('super_admin'));

        $details = (array) ($chargesFixesReport->details_depenses ?? []);
        $rows = collect($details['expenses'] ?? []);
        $byType = collect($details['by_type'] ?? []);

        return view('charges-fixes-reports.show', ['report' => $chargesFixesReport, 'details' => $details, 'rows' => $rows, 'byType' => $byType]);
    }

    public function edit(FinancialReport $chargesFixesReport, Request $request): View
    {
        $this->authorizeAccess($chargesFixesReport, $request->user()?->paroisse_id, (bool) $request->user()?->hasRole('super_admin'));

        $paroisses = $request->user()?->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : Paroisse::query()->whereKey($chargesFixesReport->paroisse_id)->get();

        $details = (array) ($chargesFixesReport->details_depenses ?? []);

        return view('charges-fixes-reports.edit', compact('chargesFixesReport', 'paroisses', 'details'));
    }

    public function update(Request $request, FinancialReport $chargesFixesReport): RedirectResponse
    {
        try {
            $this->authorizeAccess($chargesFixesReport, $request->user()?->paroisse_id, (bool) $request->user()?->hasRole('super_admin'));
            $validated = $this->validatePayload($request);
            $data = $this->buildData($validated);

            $chargesFixesReport->update([
                'paroisse_id' => $validated['paroisse_id'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'total_depenses' => $data['total_depenses'],
                'details_depenses' => $data['details_depenses'],
            ]);

            $this->logInfo('Rapport charges fixes mis à jour', ['report_id' => $chargesFixesReport->id]);

            return redirect()->route('charges-fixes-reports.show', $chargesFixesReport)->with('success', 'Rapport mis à jour.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur MAJ rapport charges fixes', ['report_id' => $chargesFixesReport->id]);

            return back()->withInput()->with('error', 'Mise à jour impossible.');
        }
    }

    public function destroy(FinancialReport $chargesFixesReport): RedirectResponse
    {
        $this->authorizeAccess($chargesFixesReport, request()->user()?->paroisse_id, (bool) request()->user()?->hasRole('super_admin'));
        $chargesFixesReport->delete();
        $this->logInfo('Rapport charges fixes supprimé logiquement', ['report_id' => $chargesFixesReport->id]);

        return redirect()->route('charges-fixes-reports.index')->with('success', 'Rapport supprimé.');
    }

    public function print(FinancialReport $chargesFixesReport): View
    {
        $this->authorizeAccess($chargesFixesReport, request()->user()?->paroisse_id, (bool) request()->user()?->hasRole('super_admin'));
        $details = (array) ($chargesFixesReport->details_depenses ?? []);
        $rows = collect($details['expenses'] ?? []);

        return view('charges-fixes-reports.print', ['report' => $chargesFixesReport, 'details' => $details, 'rows' => $rows, 'paroisse' => $chargesFixesReport->paroisse]);
    }

    public function exportPdf(FinancialReport $chargesFixesReport)
    {
        $this->authorizeAccess($chargesFixesReport, request()->user()?->paroisse_id, (bool) request()->user()?->hasRole('super_admin'));
        $details = (array) ($chargesFixesReport->details_depenses ?? []);
        $rows = collect($details['expenses'] ?? []);

        // Idem: wrapper dompdf via service container pour éviter l'erreur de classe introuvable.
        $pdf = app('dompdf.wrapper')->loadView('charges-fixes-reports.pdf', ['report' => $chargesFixesReport, 'details' => $details, 'rows' => $rows, 'paroisse' => $chargesFixesReport->paroisse])
            ->setPaper('a4', 'portrait');

        return $pdf->download('rapport-charges-fixes-'.optional($chargesFixesReport->date_debut)->format('Y-m-d').'.pdf');
    }

    private function validatePayload(Request $request): array
    {
        $user = $request->user();
        $validated = $request->validate([
            'paroisse_id' => ['required', 'integer', Rule::exists('paroisses', 'id')],
            'period_kind' => ['required', Rule::in(['mensuel', 'annuel'])],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ]);

        if (! $user?->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user?->paroisse_id) {
            abort(403);
        }

        return $validated;
    }

    private function buildData(array $validated): array
    {
        $isMonthly = $validated['period_kind'] === 'mensuel';
        $dateDebut = $isMonthly
            ? Carbon::create((int) $validated['year'], (int) $validated['month'], 1)->startOfMonth()
            : Carbon::create((int) $validated['year'], 1, 1)->startOfYear();
        $dateFin = $isMonthly ? $dateDebut->copy()->endOfMonth() : $dateDebut->copy()->endOfYear();

        // NOTE: Le rapport "Charges Fixes" n'existe plus avec le nouveau système de dépenses
        // Les dépenses sont maintenant liées à des sources de revenus (revenue_category_id, revenue_type_id)
        // Ce rapport est temporairement désactivé en attendant une refonte complète

        return [
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'by_type' => [],
            'items' => collect([]),
            'total_amount' => 0,
            'disabled' => true,
            'message' => 'Ce rapport est temporairement indisponible suite à la mise à jour du système de dépenses.',
        ];

        /* ANCIEN CODE - À REFACTORISER
        $expenses = Expense::query()
            ->where('paroisse_id', (int) $validated['paroisse_id'])
            ->where('categorie_charge', 'charge_fixe')
            ->where('statut', 'valide')
            ->whereDate('date_depense', '>=', $dateDebut)
            ->whereDate('date_depense', '<=', $dateFin)
            ->orderBy('date_depense')
            ->orderBy('id')
            ->get();

        $byType = [];
        foreach ($expenses->groupBy('type_charge') as $type => $items) {
            $byType[] = ['type' => (string) $type, 'count' => $items->count(), 'montant' => (float) $items->sum('montant')];
        }
        */

        /*  return [
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'total_depenses' => (float) $expenses->sum('montant'),
            'details_depenses' => [
                'period_kind' => $validated['period_kind'],
                'month' => $isMonthly ? (int) $validated['month'] : null,
                'year' => (int) $validated['year'],
                'by_type' => $byType,
                'expenses' => $expenses->map(fn ($e) => [
                    'id' => $e->id,
                    'date' => optional($e->date_depense)->format('Y-m-d'),
                    'type' => $e->type_charge,
                    'fournisseur' => $e->fournisseur,
                    'reference' => $e->facture_reference,
                    'montant' => (float) $e->montant,
                ])->values()->all(),
            ],
        ]; */
    }

    private function authorizeAccess(FinancialReport $report, ?int $userParoisseId, bool $isSuperAdmin): void
    {
        if ($isSuperAdmin) {
            return;
        }
        if ((int) $report->paroisse_id !== (int) $userParoisseId) {
            abort(403);
        }
    }
}
