@extends('layouts.app')

@section('title', 'Rapports financiers enregistrés')
@section('page-title', 'Rapports financiers enregistrés')

@push('styles')
<style>
.page-list .card { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: none; }
.page-list .card-header { background: linear-gradient(135deg, var(--primary, #6A1B9A) 0%, #552586 100%); color: #fff; border-radius: 12px 12px 0 0; padding: 1.25rem 1.5rem; }
.page-list .card-title { font-weight: 600; font-size: 1.2rem; }
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
                    <i class="fas fa-list me-3" style="font-size: 1.4rem; opacity: 0.9;"></i>
                    Liste des rapports enregistrés
                </h4>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('financial-reports.list') }}" class="btn btn-action btn-refresh">
                        <i class="fas fa-sync-alt"></i> Rafraîchir
                    </a>
                    <a href="{{ route('financial-reports.index') }}" class="btn btn-action btn-add" title="Créer et enregistrer un nouveau rapport financier">
                        <i class="fas fa-file-invoice-dollar"></i> Générer un nouveau rapport
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('financial-reports.list') }}" class="mb-3">
                    <div class="row g-3 align-items-end">
                        @if(auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                            <div class="col-md-4">
                                <label class="form-label">Paroisse</label>
                                <select name="paroisse_id" class="form-control">
                                    <option value="">Toutes</option>
                                    @foreach($paroisses as $paroisse)
                                        <option value="{{ $paroisse->id }}" @selected(request('paroisse_id') == $paroisse->id)>
                                            {{ $paroisse->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <label class="form-label">Année</label>
                            <select name="year" class="form-control">
                                <option value="">Toutes</option>
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-filter">
                                <i class="fas fa-filter me-1"></i> Filtrer
                            </button>
                            <a href="{{ route('financial-reports.list') }}" class="btn btn-secondary">
                                <i class="fas fa-undo me-1"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>

                @if($reports->count() > 0)
                    <div class="table-responsive rounded overflow-hidden">
                        <table class="table table-list table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Période</th>
                                    @if(auth()->user()->hasRole('super_admin'))
                                        <th>Paroisse</th>
                                    @endif
                                    <th class="text-end">Total Recettes</th>
                                    <th class="text-end">Total Dépenses</th>
                                    <th class="text-end">Solde</th>
                                    <th>Créé le</th>
                                    <th>Créé par</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $report)
                                    <tr>
                                        <td>
                                            {{ $report->date_debut->format('F Y') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $report->date_debut->format('d/m/Y') }} - {{ $report->date_fin->format('d/m/Y') }}
                                            </small>
                                        </td>
                                        @if(auth()->user()->hasRole('super_admin'))
                                            <td>{{ $report->paroisse->nom ?? '—' }}</td>
                                        @endif
                                        <td class="text-end">
                                            <span class="text-success fw-bold">
                                                {{ \App\Helpers\ParoisseConfig::formatMontant($report->total_recettes) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-danger fw-bold">
                                                {{ \App\Helpers\ParoisseConfig::formatMontant($report->total_depenses) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold {{ $report->solde >= 0 ? 'text-info' : 'text-warning' }}">
                                                {{ \App\Helpers\ParoisseConfig::formatMontant($report->solde) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $report->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            {{ $report->createdBy->name ?? '—' }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <a href="{{ route('financial-reports.show', $report) }}" class="btn btn-view btn-info btn-sm" title="Voir et imprimer">
                                                    <i class="fas fa-eye"></i> Voir / Imprimer
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $reports->links() }}
                    </div>
                @else
                    <div class="empty-state text-center">
                        <i class="fas fa-calculator empty-icon d-block"></i>
                        <h5 class="text-muted mb-2">Aucun rapport enregistré</h5>
                        <p class="text-muted mb-4">Générez un rapport mensuel pour commencer.</p>
                        <a href="{{ route('financial-reports.index') }}" class="btn btn-add btn-action">
                            <i class="fas fa-file-invoice-dollar"></i> Générer un rapport
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
