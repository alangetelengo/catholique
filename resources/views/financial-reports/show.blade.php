@extends('layouts.app')

@section('title', 'Rapport financier')
@section('page-title', 'Rapport financier - ' . $financialReport->date_debut->format('F Y'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h4 class="card-title mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    Rapport mensuel de justification
                    <br>
                    <small class="text-muted">
                        {{ $financialReport->paroisse->nom ?? '—' }} - {{ $financialReport->date_debut->format('F Y') }}
                    </small>
                </h4>
                <div class="report-header-actions d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('financial-reports.download-pdf', $financialReport) }}" class="btn btn-danger d-inline-flex align-items-center" target="_blank" style="flex-shrink: 0;">
                        <i class="fas fa-download me-2"></i><span>Télécharger PDF</span>
                    </a>
                    <button type="button" onclick="window.print()" class="btn btn-primary d-inline-flex align-items-center" style="flex-shrink: 0;">
                        <i class="fas fa-print me-2"></i><span>Imprimer</span>
                    </button>
                    <a href="{{ route('financial-reports.list') }}" class="btn btn-secondary d-inline-flex align-items-center" style="flex-shrink: 0;">
                        <i class="fas fa-arrow-left me-2"></i><span>Retour à la liste</span>
                    </a>
                </div>
            </div>
            <div class="card-body" id="report-content">
                {{-- En-tête pour impression --}}
                <div class="text-center mb-4 print-header">
                    <h3>{{ $financialReport->paroisse->nom ?? 'Paroisse' }}</h3>
                    <h4>Rapport financier mensuel</h4>
                    <p class="text-muted">
                        Période : {{ $financialReport->date_debut->format('d/m/Y') }} au {{ $financialReport->date_fin->format('d/m/Y') }}
                    </p>
                    <p class="text-muted">
                        Généré le : {{ $financialReport->created_at->format('d/m/Y à H:i') }}
                        @if($financialReport->createdBy)
                            par {{ $financialReport->createdBy->name }}
                        @endif
                    </p>
                    <hr>
                </div>

                {{-- Résumé --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Total Recettes</h5>
                                <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_recettes']) }}</h3>
                                <small>Popote / Subvention</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h5>Total Dépenses</h5>
                                <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_depenses']) }}</h3>
                                <small>Toutes catégories</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card {{ $report['solde'] >= 0 ? 'bg-info' : 'bg-warning' }} text-white">
                            <div class="card-body text-center">
                                <h5>Solde</h5>
                                <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['solde']) }}</h3>
                                <small>{{ $report['solde'] >= 0 ? 'Excédent' : 'Déficit' }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Détails des recettes --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Détails des Recettes (Popote/Subvention)</h5>
                            </div>
                            <div class="card-body">
                                @if(count($report['details_recettes']) > 0)
                                    <table class="table table-sm table-bordered">
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
                                                    <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($detail['montant']) }}</td>
                                                    <td class="text-center">{{ $detail['count'] }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="table-success fw-bold">
                                                <td>TOTAL</td>
                                                <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_recettes']) }}</td>
                                                <td class="text-center">{{ $report['revenues']->count() }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted">Aucune recette popote/subvention pour cette période.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Détails des Dépenses par Catégorie</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Catégorie</th>
                                            <th class="text-end">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Charges fixes</td>
                                            <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_depenses']['charge_fixe']) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Charges variables</td>
                                            <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_depenses']['charge_variable']) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Charges exceptionnelles</td>
                                            <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_depenses']['charge_exceptionnelle']) }}</td>
                                        </tr>
                                        <tr class="table-danger fw-bold">
                                            <td>TOTAL</td>
                                            <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_depenses']) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Liste détaillée des recettes --}}
                @if($report['revenues']->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Liste détaillée des Recettes</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Méthode</th>
                                            <th>Référence</th>
                                            <th class="text-end">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['revenues'] as $revenue)
                                            <tr>
                                                <td>{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                                                <td>{{ $revenue->type->nom ?? '—' }}</td>
                                                <td>{{ $revenue->methode_paiement ?? '—' }}</td>
                                                <td>{{ $revenue->reference_paiement ?? '—' }}</td>
                                                <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($revenue->montant) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Liste détaillée des dépenses --}}
                @if($report['expenses']->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Liste détaillée des Dépenses</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Catégorie</th>
                                            <th>Type</th>
                                            <th>Fournisseur</th>
                                            <th>Réf. Facture</th>
                                            <th class="text-end">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['expenses'] as $expense)
                                            <tr>
                                                <td>{{ $expense->date_depense?->format('d/m/Y') }}</td>
                                                <td>
                                                    @php
                                                        $cats = [
                                                            'charge_fixe' => 'Charge fixe',
                                                            'charge_variable' => 'Charge variable',
                                                            'charge_exceptionnelle' => 'Charge exceptionnelle',
                                                        ];
                                                    @endphp
                                                    {{ $cats[$expense->categorie_charge] ?? $expense->categorie_charge }}
                                                </td>
                                                <td>
                                                    @php
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
                                                    {{ $types[$expense->type_charge] ?? $expense->type_charge }}
                                                </td>
                                                <td>{{ $expense->fournisseur ?? '—' }}</td>
                                                <td>{{ $expense->facture_reference ?? '—' }}</td>
                                                <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($expense->montant) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Pied de page pour impression --}}
                <div class="mt-4 print-footer">
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted small">
                                <strong>Note :</strong> Ce rapport justifie les dépenses effectuées contre les recettes popote/subvention reçues pour la période indiquée.
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="text-muted small">
                                Document généré le {{ now()->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .report-header-actions .btn {
        white-space: nowrap;
        min-width: fit-content;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        #report-content, #report-content * {
            visibility: visible;
        }
        #report-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .report-header-actions {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .print-header {
            margin-bottom: 30px;
        }
        .print-footer {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .table {
            font-size: 0.9em;
        }
        @page {
            margin: 1.5cm;
        }
    }
</style>
@endpush
@endsection
