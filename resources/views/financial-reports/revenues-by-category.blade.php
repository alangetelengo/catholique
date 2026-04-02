@extends('layouts.app')

@section('title', 'Rapport par catégories de recettes')
@section('page-title', 'Rapport par catégories de recettes')
@section('page-title-info', 'Recettes par catégorie — filtres, totaux et export PDF')

@push('styles')
<style>
.rapport-recettes .hero-bar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 4px solid var(--primary, #6A1B9A);
    border-radius: 10px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}
.rapport-recettes .hero-bar h4 { margin: 0; font-size: 1rem; font-weight: 600; color: #1e293b; }
.rapport-recettes .hero-bar .meta { font-size: .8rem; color: #64748b; }
.rapport-recettes .filter-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.rapport-recettes .filter-card label { color: #475569; font-weight: 500; }
.rapport-recettes .filter-card .form-control,
.rapport-recettes .filter-card .form-select {
    background: #fff;
    border: 1px solid #cbd5e1;
    color: #1e293b;
}
.rapport-recettes .filter-card .form-control:focus,
.rapport-recettes .filter-card .form-select:focus {
    border-color: var(--primary, #6A1B9A);
    box-shadow: 0 0 0 2px rgba(106, 27, 154, 0.15);
}
.rapport-recettes .filter-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
    padding: .875rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.rapport-recettes .filter-header h5 { margin: 0; font-size: .95rem; font-weight: 600; color: #334155; }
.rapport-recettes .filter-header .presets { display: flex; gap: .35rem; flex-wrap: wrap; }
.rapport-recettes .filter-header .presets .btn-preset {
    font-size: .7rem;
    padding: .25rem .5rem;
    border-radius: 6px;
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #64748b;
    text-decoration: none;
    transition: all .15s;
}
.rapport-recettes .filter-header .presets .btn-preset:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #334155;
}
.rapport-recettes .filter-body { padding: 1.25rem 1.5rem; }
.rapport-recettes .filter-row { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem; }
.rapport-recettes .filter-group { display: flex; flex-direction: column; gap: .35rem; min-width: 0; }
.rapport-recettes .filter-group.w-date { width: 160px; }
.rapport-recettes .filter-group.w-type { width: 180px; }
.rapport-recettes .filter-group.flex-1 { flex: 1; min-width: 140px; }
.rapport-recettes .filter-group label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
.rapport-recettes .filter-actions { display: flex; gap: .5rem; align-items: flex-end; }
.rapport-recettes .filter-actions .btn { min-width: 130px; padding: .5rem 1rem; font-weight: 600; font-size: .9rem; border-radius: 8px; }
.rapport-recettes .stat-card { border-radius: 12px; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.rapport-recettes .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,.08); }
.rapport-recettes .table-card { border-radius: 14px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.06); border: 1px solid #e2e8f0; }
.rapport-recettes .table-card .card-header {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 1rem 1.25rem;
}
.rapport-recettes .table thead th {
    background: var(--primary, #6A1B9A);
    color: #fff;
    font-size: .75rem;
    padding: 12px 14px;
}
.rapport-recettes .table tbody tr:nth-child(even) { background: #fafbfc; }
.rapport-recettes .table tbody tr:hover { background: rgba(106, 27, 154, 0.04) !important; }
.rapport-recettes .empty-state { padding: 4rem 2rem; text-align: center; color: #64748b; }
.rapport-recettes .empty-state i { font-size: 4rem; opacity: .4; display: block; margin-bottom: 1rem; }
@media (max-width: 991px) {
    .rapport-recettes .filter-row { flex-direction: column; align-items: stretch; }
    .rapport-recettes .filter-group.w-date, .rapport-recettes .filter-group.w-type { width: 100%; }
}
</style>
@endpush

@section('content')
<div class="rapport-recettes" style="padding: 0 .5rem;">
    {{-- Hero-bar --}}
    <div class="hero-bar no-print">
        <div>
            <h4><i class="fas fa-chart-pie me-2" style="color: var(--primary, #6A1B9A);"></i>Recettes par catégorie — Rapport</h4>
            <div class="meta">
                Édité le {{ now()->format('d/m/Y à H:i') }}
                @if($dateDebut && $dateFin)
                    · Période : {{ $dateDebut }} → {{ $dateFin }}
                @endif
            </div>
        </div>
        <a href="{{ route('financial-reports.index') }}" class="btn btn-outline-secondary btn-sm">← Retour aux rapports</a>
    </div>

    {{-- Filtres --}}
    <div class="filter-card no-print">
        <form method="GET" action="{{ route('financial-reports.revenues-by-category') }}" id="filterForm">
            <div class="filter-header">
                <h5><i class="fas fa-funnel-dollar me-2 text-muted"></i>Filtres de recherche</h5>
                <div class="presets">
                    @php
                        $today = now()->format('Y-m-d');
                        $debMois = now()->startOfMonth()->format('Y-m-d');
                        $finMois = now()->endOfMonth()->format('Y-m-d');
                        $debSem = now()->startOfWeek()->format('Y-m-d');
                        $finSem = now()->endOfWeek()->format('Y-m-d');
                        $baseParams = array_filter(request()->only(['revenue_category_id']));
                    @endphp
                    <a href="{{ route('financial-reports.revenues-by-category', array_merge($baseParams, ['date_debut' => $today, 'date_fin' => $today])) }}" class="btn-preset">Aujourd'hui</a>
                    <a href="{{ route('financial-reports.revenues-by-category', array_merge($baseParams, ['date_debut' => $debSem, 'date_fin' => $finSem])) }}" class="btn-preset">Semaine</a>
                    <a href="{{ route('financial-reports.revenues-by-category', array_merge($baseParams, ['date_debut' => $debMois, 'date_fin' => $finMois])) }}" class="btn-preset">Mois en cours</a>
                </div>
            </div>
            <div class="filter-body">
                <div class="filter-row">
                    @if(auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                    <div class="filter-group w-type">
                        <label><i class="fas fa-church me-1"></i>Paroisse <span class="text-danger">*</span></label>
                        <select name="paroisse_id" class="form-select" required>
                            <option value="">Sélectionner...</option>
                            @foreach($paroisses as $p)
                                <option value="{{ $p->id }}" @selected($selectedParoisseId == $p->id)>{{ $p->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="filter-group w-date">
                        <label><i class="fas fa-calendar-alt me-1"></i>Date début</label>
                        <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut ?? $debMois }}">
                    </div>
                    <div class="filter-group w-date">
                        <label><i class="fas fa-calendar-alt me-1"></i>Date fin</label>
                        <input type="date" name="date_fin" class="form-control" value="{{ $dateFin ?? $finMois }}">
                    </div>
                    <div class="filter-group w-type">
                        <label><i class="fas fa-tag me-1"></i>Catégorie</label>
                        <select name="revenue_category_id" class="form-select">
                            <option value="">Toutes les catégories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected($selectedCategoryId == $cat->id)>{{ $cat->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-calculator me-1"></i> Calculer le rapport</button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('financial-reports.revenues-by-category') }}'"><i class="fas fa-times me-1"></i> Réinitialiser</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @if($report)
        {{-- Stats --}}
        <div class="row g-3 mb-4 no-print">
            <div class="col-sm-6 col-lg-4">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded p-2" style="background: rgba(106, 27, 154, 0.1);"><i class="fas fa-coins text-primary fs-4"></i></div>
                        <div>
                            <div class="stat-label text-muted small">Total recettes</div>
                            <div class="stat-value fw-bold text-primary">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded p-2" style="background: rgba(25, 135, 84, 0.1);"><i class="fas fa-list text-success fs-4"></i></div>
                        <div>
                            <div class="stat-label text-muted small">Nombre de recettes</div>
                            <div class="stat-value fw-bold text-success">{{ $report['revenues']->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded p-2" style="background: rgba(13, 110, 253, 0.1);"><i class="fas fa-folder-open text-info fs-4"></i></div>
                        <div>
                            <div class="stat-label text-muted small">Catégories</div>
                            <div class="stat-value fw-bold text-info">{{ count($report['by_category']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Détails par catégorie --}}
        @if(count($report['by_category']) > 0)
        <div class="row mb-4 no-print">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header"><i class="fas fa-layer-group me-2"></i>Répartition par catégorie</div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th class="text-center">Nb recettes</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['by_category'] as $cat)
                                <tr>
                                    <td><strong>{{ $cat['nom'] }}</strong></td>
                                    <td class="text-center"><span class="badge bg-light text-dark">{{ $cat['count'] }}</span></td>
                                    <td class="text-end fw-bold" style="color: var(--primary, #6A1B9A);">{{ \App\Helpers\ParoisseConfig::formatMontant($cat['montant']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td>TOTAL</td>
                                    <td class="text-center">{{ $report['revenues']->count() }}</td>
                                    <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Tableau détaillé --}}
        <div class="card table-card no-print">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-table me-2" style="color: var(--primary, #6A1B9A);"></i>Liste des recettes</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('financial-reports.revenues-by-category.pdf', array_filter([
                        'paroisse_id' => $selectedParoisseId,
                        'date_debut' => $dateDebut,
                        'date_fin' => $dateFin,
                        'revenue_category_id' => $selectedCategoryId ?: null,
                    ])) }}" class="btn btn-danger btn-sm" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> Exporter PDF
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Catégorie</th>
                                <th>Type</th>
                                <th>Méthode</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['revenues'] as $index => $r)
                            <tr>
                                <td class="text-muted fw-semibold">{{ $index + 1 }}</td>
                                <td>{{ $r->date_recette?->format('d/m/Y') }}</td>
                                <td><span class="badge" style="background: rgba(106, 27, 154, 0.12); color: var(--primary, #6A1B9A);">{{ $r->category?->nom ?? '—' }}</span></td>
                                <td>{{ $r->type?->nom ?? '—' }}</td>
                                <td><small>{{ $r->methode_paiement ?? '—' }}</small></td>
                                <td class="text-end fw-bold">{{ \App\Helpers\ParoisseConfig::formatMontant($r->montant) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox d-block mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                                    Aucune recette trouvée pour cette période.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($report['revenues']->count() > 0)
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="5" class="text-end">TOTAL</td>
                                <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        @can('generate_financial_reports')
        <div class="mt-4 text-end no-print">
            <form action="{{ route('financial-reports.revenues-by-category.store') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="paroisse_id" value="{{ $selectedParoisseId }}">
                <input type="hidden" name="date_debut" value="{{ $dateDebut }}">
                <input type="hidden" name="date_fin" value="{{ $dateFin }}">
                @if($selectedCategoryId)
                <input type="hidden" name="revenue_category_id" value="{{ $selectedCategoryId }}">
                @endif
                <button type="submit" class="btn btn-primary btn-action">
                    <i class="fas fa-save me-1"></i> Enregistrer ce rapport
                </button>
            </form>
        </div>
        @endcan
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body empty-state">
                <i class="fas fa-chart-pie"></i>
                <h5 class="text-muted mb-2">Sélectionnez une paroisse et une période</h5>
                <p class="mb-0">Utilisez les filtres puis cliquez sur <strong>Calculer le rapport</strong> pour afficher les recettes par catégorie.</p>
            </div>
        </div>
    @endif
</div>
@endsection
