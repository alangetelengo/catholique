@extends('layouts.app')

@section('title', 'Rapport Subvention Popote')
@section('page-title', 'Rapport Subvention Popote — Dépenses alimentation')

@push('styles')
<style>
.page-list .card { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: none; }
.page-list .card-header { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #fff; border-radius: 12px 12px 0 0; padding: 1.25rem 1.5rem; }
.page-list .card-title { font-weight: 600; font-size: 1.2rem; }
.page-list .filters-card { background: #f8f9fa; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem; }
.page-list .form-control { border-radius: 8px; }
.page-list .btn-filter { padding: 10px 24px; border-radius: 8px; font-weight: 600; }
.page-list .stat-card { border-radius: 12px; overflow: hidden; }
.page-list .stat-card .card-body { padding: 1.5rem; }
.page-list .table-list thead th { background: var(--primary, #6A1B9A); color: #fff; font-weight: 600; padding: 14px 16px; }
.page-list .table-list td { padding: 14px 16px; vertical-align: middle; }
/* Boutons header : contraste sur fond bleu */
.page-list .card-header .btn-refresh,
.page-list .card-header a.btn-refresh {
    background: rgba(255,255,255,0.2) !important;
    color: #fff !important;
    border: 1px solid rgba(255,255,255,0.4) !important;
}
.page-list .card-header .btn-refresh:hover,
.page-list .card-header a.btn-refresh:hover {
    background: rgba(255,255,255,0.3) !important;
    color: #fff !important;
}
</style>
@endpush

@section('content')
<div class="page-list">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h4 class="card-title mb-0 d-flex align-items-center">
                        <i class="fas fa-utensils me-3" style="font-size: 1.4rem;"></i>
                        Rapport Subvention Popote — Dépenses alimentation
                    </h4>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if($report)
                            <button type="button"
                                    class="btn btn-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPrintPopote"
                                    data-print-url="{{ route('financial-reports.popote-print', [
                                        'paroisse_id' => $selectedParoisseId,
                                        'period_type' => $periodType,
                                        'month' => $selectedMonth,
                                        'year' => $selectedYear,
                                    ]) }}">
                                <i class="fas fa-print me-1"></i> Imprimer
                            </button>
                            <form action="{{ route('financial-reports.popote-pdf') }}" method="POST" class="d-inline-flex align-items-center">
                                @csrf
                                <input type="hidden" name="paroisse_id" value="{{ $selectedParoisseId }}">
                                <input type="hidden" name="period_type" value="{{ $periodType }}">
                                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                                <input type="hidden" name="year" value="{{ $selectedYear }}">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-download me-1"></i> Télécharger PDF
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('financial-reports.index') }}" class="btn btn-refresh">
                            <i class="fas fa-arrow-left me-1"></i> Retour rapports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">La subvention popote est réservée aux dépenses d'alimentation de la paroisse. Ce rapport compare la subvention reçue aux dépenses alimentation enregistrées.</p>

                    <div class="filters-card">
                        <form method="GET" action="{{ route('financial-reports.popote') }}">
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
                                    <div class="btn-group w-100">
                                        <button type="button" class="btn btn-outline-primary btn-sm period-btn {{ $periodType === 'month' ? 'active' : '' }}" data-period="month">Mensuel</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm period-btn {{ $periodType === 'year' ? 'active' : '' }}" data-period="year">Annuel</button>
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
                                    <button type="submit" class="btn btn-primary btn-filter w-100">
                                        <i class="fas fa-calculator me-1"></i> Voir le rapport
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if($report)
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card stat-card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5 class="mb-2"><i class="fas fa-coins me-2"></i>Subvention Popote reçue</h5>
                                        <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['subvention_recue']) }}</h3>
                                        <small class="opacity-75">Période : {{ $report['date_debut']->format('d/m/Y') }} — {{ $report['date_fin']->format('d/m/Y') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h5 class="mb-2"><i class="fas fa-shopping-cart me-2"></i>Dépenses alimentation</h5>
                                        <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_depenses_alimentation']) }}</h3>
                                        <small class="opacity-75">{{ $report['depenses_alimentation']->count() }} ligne(s)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card {{ $report['solde'] >= 0 ? 'bg-info' : 'bg-warning' }} text-white">
                                    <div class="card-body text-center">
                                        <h5 class="mb-2"><i class="fas fa-balance-scale me-2"></i>Solde</h5>
                                        <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['solde']) }}</h3>
                                        <small class="opacity-75">{{ $report['solde'] >= 0 ? 'Reste subvention' : 'Dépassement' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card detail-card">
                            <div class="card-header">
                                <i class="fas fa-list me-2"></i>Détail des dépenses alimentation (libellé, jour, montant)
                            </div>
                            <div class="card-body p-0">
                                @if($report['depenses_alimentation']->count() > 0)
                                    @php $joursLabels = ['lundi'=>'Lundi','mardi'=>'Mardi','mercredi'=>'Mercredi','jeudi'=>'Jeudi','vendredi'=>'Vendredi','samedi'=>'Samedi','dimanche'=>'Dimanche']; @endphp
                                    <div class="table-responsive">
                                        <table class="table table-list mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Jour</th>
                                                    <th>Libellé (alimentation achetée)</th>
                                                    <th class="text-end">Montant</th>
                                                    <th>Méthode</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report['depenses_alimentation'] as $dep)
                                                    <tr>
                                                        <td>{{ $dep->date_depense?->format('d/m/Y') }}</td>
                                                        <td>{{ $joursLabels[$dep->jour_semaine] ?? $dep->jour_semaine ?? '—' }}</td>
                                                        <td>{{ $dep->libelle ?? '—' }}</td>
                                                        <td class="text-end fw-semibold">{{ \App\Helpers\ParoisseConfig::formatMontant($dep->montant) }}</td>
                                                        <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $dep->methode_paiement)) }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-light fw-bold">
                                                    <td colspan="3" class="text-end">Total dépenses alimentation</td>
                                                    <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_depenses_alimentation']) }}</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted p-4 mb-0">Aucune dépense alimentation enregistrée pour cette période.</p>
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

@if($report ?? false)
{{-- Modal d'impression --}}
<div class="modal fade" id="modalPrintPopote" tabindex="-1" aria-labelledby="modalPrintPopoteLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPrintPopoteLabel">
                    <i class="fas fa-print me-2"></i>Rapport Subvention Popote — Dépenses alimentation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="printPopoteIframe" title="Document à imprimer" style="width:100%;height:75vh;border:none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="btnPrintPopoteFromModal">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>
        </div>
    </div>
</div>
@endif

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

// Modal d'impression Popote
var modalPrintPopote = document.getElementById('modalPrintPopote');
var printPopoteIframe = document.getElementById('printPopoteIframe');
var btnPrintPopoteFromModal = document.getElementById('btnPrintPopoteFromModal');
if (modalPrintPopote && printPopoteIframe) {
    modalPrintPopote.addEventListener('shown.bs.modal', function(event) {
        var trigger = event.relatedTarget;
        if (trigger && trigger.dataset.printUrl) {
            printPopoteIframe.src = trigger.dataset.printUrl;
        }
    });
    modalPrintPopote.addEventListener('hidden.bs.modal', function() {
        printPopoteIframe.src = 'about:blank';
    });
}
if (btnPrintPopoteFromModal && printPopoteIframe) {
    btnPrintPopoteFromModal.addEventListener('click', function() {
        try {
            if (printPopoteIframe.contentWindow && printPopoteIframe.contentWindow.print) {
                printPopoteIframe.contentWindow.print();
            }
        } catch (e) {
            console.error(e);
        }
    });
}
</script>
@endpush
@endsection
