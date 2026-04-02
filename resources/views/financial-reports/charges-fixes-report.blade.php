@extends('layouts.app')

@section('title', 'Rapport Charges fixes')
@section('page-title', 'Rapport des charges fixes')

@push('styles')
<style>
.page-list .card { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: none; }
.page-list .card-header { background: linear-gradient(135deg, var(--titre-page, #003366) 0%, var(--titre-page-dark, #002244) 100%); color: #fff; border-radius: 12px 12px 0 0; padding: 1.25rem 1.5rem; }
.page-list .card-title { font-weight: 600; font-size: 1.2rem; }
.page-list .filters-card { background: #f8f9fa; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem; }
.page-list .form-control { border-radius: 8px; }
.page-list .period-btn { border: 1px solid var(--danger, #DC143C); color: #333; }
.page-list .period-btn:hover { border-color: var(--danger); color: var(--danger); }
.page-list .period-btn.active { background: var(--danger, #DC143C); color: #fff; border-color: var(--danger); }
.page-list .stat-card { border-radius: 12px; overflow: hidden; }
.page-list .stat-card .text-secondary { color: var(--danger, #DC143C) !important; }
.page-list .detail-card .card-header { background: linear-gradient(135deg, var(--titre-page, #003366) 0%, var(--titre-page-dark, #002244) 100%); color: #fff; border: none; }
.page-list .table-list thead th { background: var(--titre-page, #003366); color: #fff; font-weight: 600; padding: 14px 16px; }
.page-list .table-list td { padding: 14px 16px; vertical-align: middle; }
.page-list .table-list tbody tr:hover { background: var(--rgba-primary-1, rgba(0,51,102,0.1)); }
.page-list .btn-action-view { background: var(--bouton-ajout, #FFEA00) !important; border: 1px solid var(--bouton-ajout-hover, #FFD200) !important; color: #1a1a1a !important; font-weight: 600; }
.page-list .btn-action-view:hover { background: var(--bouton-ajout-hover, #FFD200) !important; border-color: var(--bouton-ajout-hover, #FFD200) !important; color: #1a1a1a !important; }
.page-list .btn-action-view *, .page-list .btn-action-view i { color: #1a1a1a !important; }
.page-list .card-header .btn-retour { background: #fff !important; color: #1a1a1a !important; border: 1px solid rgba(255,255,255,0.5); }
.page-list .card-header .btn-retour:hover { background: rgba(255,255,255,0.9) !important; color: #1a1a1a !important; border-color: #fff; }
.page-list .card-header .btn-retour, .page-list .card-header .btn-retour * { color: #1a1a1a !important; }
</style>
@endpush

@section('content')
<div class="page-list">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h4 class="card-title mb-0 d-flex align-items-center">
                        <i class="fas fa-file-invoice-dollar me-3" style="font-size: 1.4rem;"></i>
                        Rapport des charges fixes
                    </h4>
                    <a href="{{ route('financial-reports.index') }}" class="btn btn-retour btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Retour rapports
                    </a>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">Les charges fixes ne sont déduites d'aucune recette. Ce rapport permet à la paroisse de faire un rapport à sa hiérarchie des différentes charges fixes enregistrées sur la période (mensuel ou annuel).</p>

                    <div class="filters-card">
                        <form method="GET" action="{{ route('financial-reports.charges-fixes') }}">
                            <input type="hidden" name="period_type" id="period_type" value="{{ $periodType }}">
                            <div class="row g-3 align-items-end">
                                @if(auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-muted">Paroisse</label>
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

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold small text-muted">Période</label>
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-sm period-btn {{ $periodType === 'month' ? 'active' : '' }}" data-period="month">Mensuel</button>
                                        <button type="button" class="btn btn-sm period-btn {{ $periodType === 'year' ? 'active' : '' }}" data-period="year">Annuel</button>
                                    </div>
                                </div>

                                <div class="col-md-2 mois-field" style="{{ $periodType === 'year' ? 'display:none' : '' }}">
                                    <label class="form-label fw-semibold small text-muted">Mois</label>
                                    <select name="month" class="form-control">
                                        @foreach([1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'] as $num => $nom)
                                            <option value="{{ $num }}" @selected($selectedMonth == $num)>{{ $nom }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold small text-muted">Année</label>
                                    <select name="year" class="form-control">
                                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                                            <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-action-view w-100">
                                        <i class="fas fa-list me-1"></i> Voir le rapport
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if($report)
                        <div class="row mb-4">
                                <div class="col-md-12">
                                <div class="card stat-card bg-light border">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1"><i class="fas fa-file-invoice-dollar me-2" style="color: var(--danger, #DC143C);"></i>Total charges fixes</h5>
                                            <small class="text-muted">Période : {{ $report['date_debut']->format('d/m/Y') }} — {{ $report['date_fin']->format('d/m/Y') }} · {{ $report['expenses']->count() }} enregistrement(s)</small>
                                        </div>
                                        <h3 class="mb-0" style="color: var(--danger, #DC143C); font-weight: 700;">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total']) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card detail-card">
                            <div class="card-header">
                                <i class="fas fa-receipt me-2"></i>Liste des charges fixes enregistrées
                            </div>
                            <div class="card-body p-0">
                                @if($report['expenses']->count() > 0)
                                    @php $typeLabels = $report['type_labels'] ?? []; @endphp
                                    <div class="table-responsive">
                                        <table class="table table-list mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type de charge</th>
                                                    <th>Réf. facture</th>
                                                    <th>Fournisseur</th>
                                                    <th class="text-end">Montant</th>
                                                    <th>Méthode</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report['expenses'] as $exp)
                                                    <tr>
                                                        <td>{{ $exp->date_depense?->format('d/m/Y') }}</td>
                                                        <td><span class="badge" style="background: var(--rgba-primary-1); color: var(--primary, #003366);">{{ $typeLabels[$exp->type_charge] ?? $exp->type_charge }}</span></td>
                                                        <td>{{ $exp->facture_reference ?? '—' }}</td>
                                                        <td>{{ $exp->fournisseur ?? '—' }}</td>
                                                        <td class="text-end fw-semibold">{{ \App\Helpers\ParoisseConfig::formatMontant($exp->montant) }}</td>
                                                        <td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $exp->methode_paiement)) }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-light fw-bold">
                                                    <td colspan="4" class="text-end">Total</td>
                                                    <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total']) }}</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted p-4 mb-0">Aucune charge fixe enregistrée pour cette période.</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-muted">Sélectionnez une paroisse et une période puis cliquez sur « Voir le rapport ».</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.period-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var period = this.dataset.period;
        document.getElementById('period_type').value = period;
        document.querySelectorAll('.period-btn').forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        document.querySelectorAll('.mois-field').forEach(function(el) { el.style.display = period === 'month' ? '' : 'none'; });
    });
});
</script>
@endpush
@endsection
