@extends('layouts.app')

@section('title', 'Rapports financiers')
@section('page-title', 'Rapports financiers')

@push('styles')
<style>
.page-list .card { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: none; }
.page-list .card-header { background: linear-gradient(135deg, var(--primary, #6A1B9A) 0%, #552586 100%); color: #fff; border-radius: 12px 12px 0 0; padding: 1.25rem 1.5rem; }
.page-list .card-title { font-weight: 600; font-size: 1.2rem; }
.page-list .filters-card { background: #f8f9fa; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem; }
.page-list .form-control { border-radius: 8px; border: 1px solid #dee2e6; }
.page-list .btn-filter { padding: 10px 24px; border-radius: 8px; font-weight: 600; }
.page-list .stat-card { border-radius: 12px; overflow: hidden; }
.page-list .stat-card .card-body { padding: 1.5rem; }
.page-list .stat-card h3 { font-size: 1.75rem; font-weight: 700; }
.page-list .detail-card { border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.page-list .detail-card .card-header { background: #f8f9fa; border-bottom: 1px solid #e9ecef; font-weight: 600; }
.page-list .table-list { font-size: 0.95rem; }
.page-list .table-list thead th { background: var(--primary, #6A1B9A); color: #fff; font-weight: 600; padding: 14px 16px; border: none; }
.page-list .table-list thead th:first-child { border-radius: 8px 0 0 0; }
.page-list .table-list thead th:last-child { border-radius: 0 8px 0 0; }
.page-list .table-list tbody tr { transition: background 0.2s; }
.page-list .table-list tbody tr:hover { background: rgba(106, 27, 154, 0.04); }
.page-list .table-list td { padding: 14px 16px; vertical-align: middle; }
.page-list .empty-state { padding: 4rem 2rem; }
.page-list .empty-state .empty-icon { font-size: 5rem; color: #dee2e6; margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<div class="page-list">
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h4 class="card-title mb-0 d-flex align-items-center">
                    <i class="fas fa-chart-pie me-3" style="font-size: 1.4rem; opacity: 0.9;"></i>
                    Rapport mensuel de justification
                </h4>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#financialReportHelpModal" title="Aide">
                        <i class="fas fa-info-circle me-1"></i> Aide
                    </button>
                    <a href="{{ route('financial-reports.revenues-by-category') }}" class="btn btn-action btn-secondary-action">
                        <i class="fas fa-layer-group"></i> Rapport par catégories
                    </a>
                    <a href="{{ route('financial-reports.popote') }}" class="btn btn-action btn-secondary-action">
                        <i class="fas fa-utensils"></i> Rapport Subvention Popote
                    </a>
                    <a href="{{ route('financial-reports.charges-fixes') }}" class="btn btn-action btn-secondary-action">
                        <i class="fas fa-file-invoice-dollar"></i> Rapport Charges fixes
                    </a>
                    <a href="{{ route('financial-reports.list') }}" class="btn btn-action btn-secondary-action">
                        <i class="fas fa-history"></i> Voir les rapports enregistrés
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Filtres --}}
                <div class="filters-card">
                    <form method="GET" action="{{ route('financial-reports.index') }}">
                        <div class="row g-3 align-items-end">
                            @if(auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-muted">Paroisse <span class="text-danger">*</span></label>
                                <select name="paroisse_id" class="form-control" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($paroisses as $paroisse)
                                        <option value="{{ $paroisse->id }}" @selected($selectedParoisseId == $paroisse->id)>{{ $paroisse->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                            <input type="hidden" name="paroisse_id" value="{{ auth()->user()->paroisse_id }}">
                            @endif

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-muted">Mois <span class="text-danger">*</span></label>
                                <select name="month" class="form-control" required>
                                    @foreach([
                                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                                        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                                        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                                    ] as $num => $nom)
                                        <option value="{{ $num }}" @selected($selectedMonth == $num)>{{ $nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-muted">Année <span class="text-danger">*</span></label>
                                <select name="year" class="form-control" required>
                                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary btn-filter">
                                    <i class="fas fa-calculator me-1"></i> Calculer le rapport
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                @if($report)
                    {{-- Statistiques --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-2"><i class="fas fa-arrow-up me-2"></i>Total Recettes</h5>
                                    <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_recettes']) }}</h3>
                                    <small class="opacity-75">Popote / Subvention</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-2"><i class="fas fa-arrow-down me-2"></i>Total Dépenses</h5>
                                    <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_depenses']) }}</h3>
                                    <small class="opacity-75">Toutes catégories</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card {{ $report['solde'] >= 0 ? 'bg-info' : 'bg-warning' }} text-white">
                                <div class="card-body text-center">
                                    <h5 class="mb-2"><i class="fas fa-balance-scale me-2"></i>Solde</h5>
                                    <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['solde']) }}</h3>
                                    <small class="opacity-75">{{ $report['solde'] >= 0 ? 'Excédent' : 'Déficit' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Détails --}}
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card detail-card">
                                <div class="card-header">
                                    <i class="fas fa-coins me-2"></i>Détails des Recettes (Popote/Subvention)
                                </div>
                                <div class="card-body">
                                    @if(count($report['details_recettes']) > 0)
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th class="text-end">Montant</th>
                                                    <th class="text-center">Nb</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report['details_recettes'] as $detail)
                                                    <tr>
                                                        <td>{{ $detail['nom'] }}</td>
                                                        <td class="text-end fw-semibold">{{ \App\Helpers\ParoisseConfig::formatMontant($detail['montant']) }}</td>
                                                        <td class="text-center"><span class="badge badge-info">{{ $detail['count'] }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted mb-0">Aucune recette popote/subvention pour cette période.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card detail-card">
                                <div class="card-header">
                                    <i class="fas fa-receipt me-2"></i>Détails des Dépenses par Catégorie
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Catégorie</th>
                                                <th class="text-end">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Charges fixes</td>
                                                <td class="text-end fw-semibold">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_depenses']['charge_fixe']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Charges variables</td>
                                                <td class="text-end fw-semibold">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_depenses']['charge_variable']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Charges exceptionnelles</td>
                                                <td class="text-end fw-semibold">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_depenses']['charge_exceptionnelle']) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Liste détaillée des dépenses --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="card detail-card">
                                <div class="card-header">
                                    <i class="fas fa-list me-2"></i>Liste détaillée des Dépenses
                                </div>
                                <div class="card-body">
                                    @if($report['expenses']->count() > 0)
                                        <div class="table-responsive rounded overflow-hidden">
                                            <table class="table table-list table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Catégorie</th>
                                                        <th>Type</th>
                                                        <th>Fournisseur</th>
                                                        <th class="text-end">Montant</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $cats = [
                                                            'charge_fixe' => 'Charge fixe',
                                                            'charge_variable' => 'Charge variable',
                                                            'charge_exceptionnelle' => 'Charge exceptionnelle',
                                                        ];
                                                        $types = [
                                                            'carburant' => 'Carburant',
                                                            'hosties' => 'Hosties',
                                                            'internet' => 'Internet',
                                                            'maintenance_materiel' => 'Maintenance matériel',
                                                            'gaz' => 'Gaz',
                                                            'eau' => 'Eau',
                                                            'electricite' => 'Électricité',
                                                            'jardinage' => 'Jardinage',
                                                            'salaire_ouvrier' => 'Salaire ouvrier',
                                                            'autre' => 'Autre',
                                                        ];
                                                    @endphp
                                                    @foreach($report['expenses'] as $expense)
                                                        <tr>
                                                            <td><span class="text-nowrap">{{ $expense->date_depense?->format('d/m/Y') }}</span></td>
                                                            <td><span class="badge" style="background: rgba(106, 27, 154, 0.12); color: var(--primary, #6A1B9A);">{{ $cats[$expense->categorie_charge] ?? $expense->categorie_charge }}</span></td>
                                                            <td>{{ $types[$expense->type_charge] ?? $expense->type_charge }}</td>
                                                            <td>{{ $expense->fournisseur ?? '—' }}</td>
                                                            <td class="text-end fw-semibold" style="color: var(--primary, #6A1B9A);">{{ \App\Helpers\ParoisseConfig::formatMontant($expense->montant) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">Aucune dépense enregistrée pour cette période.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @can('generate_financial_reports')
                    <div class="mt-4 text-end">
                        <form action="{{ route('financial-reports.store') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="paroisse_id" value="{{ $selectedParoisseId }}">
                            <input type="hidden" name="month" value="{{ $selectedMonth }}">
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                            <button type="submit" class="btn btn-primary btn-action">
                                <i class="fas fa-save"></i> Enregistrer ce rapport
                            </button>
                        </form>
                    </div>
                    @endcan
                @else
                    <div class="empty-state text-center">
                        <i class="fas fa-chart-pie empty-icon d-block"></i>
                        <h5 class="text-muted mb-2">Sélectionnez une période</h5>
                        <p class="text-muted mb-0">Le rapport calculera automatiquement les recettes popote/subvention et les dépenses pour la période choisie.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

@include('financial-reports._help_modal')
@endsection
