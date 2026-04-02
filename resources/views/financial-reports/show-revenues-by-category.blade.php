@extends('layouts.app')

@section('title', 'Rapport par catégories de recettes')
@section('page-title', 'Rapport par catégories de recettes')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h4 class="card-title mb-0">
                    <i class="fas fa-layer-group me-2"></i>
                    Rapport par catégories de recettes
                    <br>
                    <small class="text-muted">
                        {{ $financialReport->paroisse->nom ?? '—' }} — {{ $financialReport->date_debut->format('d/m/Y') }} au {{ $financialReport->date_fin->format('d/m/Y') }}
                    </small>
                </h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('financial-reports.download-pdf', $financialReport) }}" class="btn btn-danger" target="_blank">
                        <i class="fas fa-download me-2"></i>Télécharger PDF
                    </a>
                    <button type="button" onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                    <a href="{{ route('financial-reports.list') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
            <div class="card-body" id="report-content">
                <div class="text-center mb-4 print-header">
                    <h3>{{ $financialReport->paroisse->nom ?? 'Paroisse' }}</h3>
                    <h4>Rapport par catégories de recettes</h4>
                    <p class="text-muted">
                        Période : {{ $financialReport->date_debut->format('d/m/Y') }} au {{ $financialReport->date_fin->format('d/m/Y') }}
                    </p>
                    <p class="text-muted small">
                        Généré le {{ $financialReport->created_at->format('d/m/Y à H:i') }}
                        @if($financialReport->createdBy)
                            par {{ $financialReport->createdBy->name }}
                        @endif
                    </p>
                    <hr>
                </div>

                {{-- Stats --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Total recettes</h5>
                                <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Nombre de recettes</h5>
                                <h3>{{ $report['revenues']->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>Catégories</h5>
                                <h3>{{ count($report['by_category']) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Répartition par catégorie --}}
                @if(count($report['by_category']) > 0)
                <div class="card mb-4">
                    <div class="card-header"><h5>Répartition par catégorie</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th class="text-center">Nb</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['by_category'] as $cat)
                                <tr>
                                    <td>{{ $cat['nom'] }}</td>
                                    <td class="text-center">{{ $cat['count'] }}</td>
                                    <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($cat['montant']) }}</td>
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
                @endif

                {{-- Liste des recettes --}}
                @if($report['revenues']->count() > 0)
                <div class="card mb-4">
                    <div class="card-header"><h5>Liste des recettes</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Catégorie</th>
                                        <th>Type</th>
                                        <th>Méthode</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report['revenues'] as $r)
                                    <tr>
                                        <td>{{ $r->date_recette?->format('d/m/Y') }}</td>
                                        <td>{{ $r->category?->nom ?? '—' }}</td>
                                        <td>{{ $r->type?->nom ?? '—' }}</td>
                                        <td>{{ $r->methode_paiement ?? '—' }}</td>
                                        <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($r->montant) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <td colspan="4" class="text-end">TOTAL</td>
                                        <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-4 print-footer">
                    <hr>
                    <p class="text-muted small text-center">Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    body * { visibility: hidden; }
    #report-content, #report-content * { visibility: visible; }
    #report-content { position: absolute; left: 0; top: 0; width: 100%; }
    .card-header .btn, .card-header a.btn { display: none !important; }
}
</style>
@endpush
@endsection
