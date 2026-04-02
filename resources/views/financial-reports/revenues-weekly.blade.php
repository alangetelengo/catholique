@extends('layouts.app')

@section('title', 'Rapport des revenus - Semaine/Dimanche')
@section('page-title', 'Rapport des revenus - Semaine/Dimanche')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h4 class="card-title mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    Rapport des revenus - Quête ordinaire
                </h4>
                <div class="card-action d-flex align-items-center gap-2 flex-wrap">
                    @if($report)
                        <button type="button"
                                class="btn btn-secondary me-2"
                                data-bs-toggle="modal"
                                data-bs-target="#modalPrintReport"
                                data-print-url="{{ route('financial-reports.revenues-weekly-print', [
                                    'paroisse_id' => $selectedParoisseId,
                                    'period_type' => $periodType,
                                    'week_start' => $selectedWeekStart,
                                    'month' => $selectedMonth,
                                    'year' => $selectedYear,
                                ]) }}">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                        <form action="{{ route('financial-reports.revenues-weekly-pdf') }}" method="POST" class="d-inline-flex align-items-center">
                            @csrf
                            <input type="hidden" name="paroisse_id" value="{{ $selectedParoisseId }}">
                            <input type="hidden" name="period_type" value="{{ $periodType }}">
                            <input type="hidden" name="week_start" value="{{ $selectedWeekStart }}">
                            <input type="hidden" name="month" value="{{ $selectedMonth }}">
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                            <button type="submit" class="btn btn-danger me-2">
                                <i class="fas fa-download me-2"></i>Télécharger PDF
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('financial-reports.revenues-weekly') }}" class="mb-4">
                    <div class="row g-3 align-items-end">
                        @if(auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                            <div class="col-md-3">
                                <label class="form-label">Paroisse <span class="text-danger">*</span></label>
                                <select name="paroisse_id" class="form-control" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($paroisses as $paroisse)
                                        <option value="{{ $paroisse->id }}" @selected($selectedParoisseId == $paroisse->id)>
                                            {{ $paroisse->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="paroisse_id" value="{{ auth()->user()->paroisse_id }}">
                        @endif

                        <div class="col-md-3">
                            <label class="form-label">Période</label>
                            <select name="period_type" id="period-type" class="form-control" required>
                                <option value="week" @selected($periodType === 'week')>Semaine</option>
                                <option value="month" @selected($periodType === 'month')>Mois</option>
                            </select>
                        </div>

                        <div class="col-md-3" id="week-container">
                            <label class="form-label">Semaine <span class="text-danger">*</span></label>
                            <small class="text-muted">Date de début (lundi)</small>
                            <input type="date"
                                   name="week_start"
                                   id="week-start"
                                   class="form-control"
                                   value="{{ $selectedWeekStart ?? now()->startOfWeek()->format('Y-m-d') }}"
                                   required>

                        </div>

                        <div class="col-md-3" id="month-container" style="display: none;">
                            <label class="form-label">Mois <span class="text-danger">*</span></label>
                            <select name="month" class="form-control">
                                @foreach([
                                    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                                    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                                    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                                ] as $num => $nom)
                                    <option value="{{ $num }}" @selected($selectedMonth == $num)>{{ $nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3" id="year-container" style="display: none;">
                            <label class="form-label">Année <span class="text-danger">*</span></label>
                            <select name="year" class="form-control">
                                @for($y = now()->year; $y >= now()->year - 2; $y--)
                                    <option value="{{ $y }}" @selected($selectedYear == $y)>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Calculer le rapport</button>
                        </div>
                    </div>
                </form>

                @if($report)
                    @php
                        if ($periodType === 'week') {
                            $dateDebut = \Carbon\Carbon::parse($selectedWeekStart)->startOfWeek();
                            $dateFin = $dateDebut->copy()->endOfWeek();
                        } else {
                            $dateDebut = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
                            $dateFin = $dateDebut->copy()->endOfMonth();
                        }
                    @endphp

                    <div class="alert alert-info mb-4">
                        <strong>Période :</strong>
                        {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
                    </div>

                    {{-- Résumé --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>Total Semaine</h5>
                                    <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_semaine']) }}</h3>
                                    <small>Lundi - Samedi</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>Total Dimanche</h5>
                                    <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_dimanche']) }}</h3>
                                    <small>Dimanche</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>Total Général</h5>
                                    <h3>{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</h3>
                                    <small>Semaine + Dimanche</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Détails par jour de la semaine --}}
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Détails de la Semaine (Lundi - Samedi)</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Jour</th>
                                                <th class="text-end">Montant</th>
                                                <th class="text-center">Nb</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $joursLabels = [
                                                    'lundi' => 'Lundi',
                                                    'mardi' => 'Mardi',
                                                    'mercredi' => 'Mercredi',
                                                    'jeudi' => 'Jeudi',
                                                    'vendredi' => 'Vendredi',
                                                    'samedi' => 'Samedi',
                                                ];
                                            @endphp
                                            @foreach(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'] as $jour)
                                                <tr>
                                                    <td>{{ $joursLabels[$jour] }}</td>
                                                    <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_semaine'][$jour]['montant'] ?? 0) }}</td>
                                                    <td class="text-center">{{ $report['details_semaine'][$jour]['count'] ?? 0 }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="table-primary fw-bold">
                                                <td>TOTAL SEMAINE</td>
                                                <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_semaine']) }}</td>
                                                <td class="text-center">{{ $report['revenues_semaine']->count() }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Détails du Dimanche</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Jour</th>
                                                <th class="text-end">Montant</th>
                                                <th class="text-center">Nb</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Dimanche</td>
                                                <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_dimanche']) }}</td>
                                                <td class="text-center">{{ $report['details_dimanche']['count'] }}</td>
                                            </tr>
                                            <tr class="table-success fw-bold">
                                                <td>TOTAL DIMANCHE</td>
                                                <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_dimanche']) }}</td>
                                                <td class="text-center">{{ $report['details_dimanche']['count'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Liste détaillée --}}
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
                                            <th>Jour</th>
                                            <th>Période</th>
                                            <th>Méthode</th>
                                            <th>Référence</th>
                                            <th class="text-end">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['revenues_all'] as $revenue)
                                            <tr>
                                                <td>{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                                                <td>
                                                    @php
                                                        $joursLabels = [
                                                            'lundi' => 'Lundi',
                                                            'mardi' => 'Mardi',
                                                            'mercredi' => 'Mercredi',
                                                            'jeudi' => 'Jeudi',
                                                            'vendredi' => 'Vendredi',
                                                            'samedi' => 'Samedi',
                                                            'dimanche' => 'Dimanche',
                                                        ];
                                                    @endphp
                                                    {{ $joursLabels[$revenue->jour_semaine] ?? '—' }}
                                                </td>
                                                <td>
                                                    @if($revenue->periode_messe === 'semaine' || in_array($revenue->jour_semaine, ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi']))
                                                        <span class="badge bg-primary">Semaine</span>
                                                    @else
                                                        <span class="badge bg-success">Dimanche</span>
                                                    @endif
                                                </td>
                                                <td>{{ $revenue->methode_paiement ?? '—' }}</td>
                                                <td>{{ $revenue->reference_paiement ?? '—' }}</td>
                                                <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($revenue->montant) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info fw-bold">
                                            <td colspan="5">TOTAL GÉNÉRAL</td>
                                            <td class="text-end">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calculator" style="font-size:64px;color:#ccc;margin-bottom:20px;"></i>
                        <h5 class="text-muted">Sélectionnez une période pour générer le rapport</h5>
                        <p class="text-muted">Le rapport calculera automatiquement les revenus de quête ordinaire pour la période choisie.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($report ?? false)
{{-- Modal d'impression : contenu du document à imprimer --}}
<div class="modal fade" id="modalPrintReport" tabindex="-1" aria-labelledby="modalPrintReportLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPrintReportLabel">
                    <i class="fas fa-print me-2"></i>Rapport des revenus - Quête ordinaire
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="printReportIframe" title="Document à imprimer" style="width:100%;height:75vh;border:none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="btnPrintFromModal">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const periodType = document.getElementById('period-type');
        const weekContainer = document.getElementById('week-container');
        const monthContainer = document.getElementById('month-container');
        const yearContainer = document.getElementById('year-container');
        const weekInput = document.getElementById('week-start');
        const monthSelect = monthContainer.querySelector('select[name="month"]');
        const yearSelect = yearContainer.querySelector('select[name="year"]');

        function updatePeriodFields() {
            if (periodType.value === 'week') {
                weekContainer.style.display = '';
                monthContainer.style.display = 'none';
                yearContainer.style.display = 'none';
                weekInput.required = true;
                monthSelect.required = false;
                yearSelect.required = false;
            } else {
                weekContainer.style.display = 'none';
                monthContainer.style.display = '';
                yearContainer.style.display = '';
                weekInput.required = false;
                monthSelect.required = true;
                yearSelect.required = true;
            }
        }

        // Ajuster automatiquement la date au lundi de la semaine sélectionnée
        if (weekInput) {
            weekInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const dayOfWeek = selectedDate.getDay();
                const diff = selectedDate.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1); // Ajuster au lundi
                const monday = new Date(selectedDate.setDate(diff));
                this.value = monday.toISOString().split('T')[0];
            });
        }

        periodType.addEventListener('change', updatePeriodFields);
        updatePeriodFields();

        // Modal d'impression : charger l'iframe à l'ouverture, imprimer au clic
        const modalPrint = document.getElementById('modalPrintReport');
        const printIframe = document.getElementById('printReportIframe');
        const btnPrintFromModal = document.getElementById('btnPrintFromModal');
        if (modalPrint && printIframe) {
            modalPrint.addEventListener('shown.bs.modal', function(event) {
                var trigger = event.relatedTarget;
                if (trigger && trigger.dataset.printUrl) {
                    printIframe.src = trigger.dataset.printUrl;
                }
            });
            modalPrint.addEventListener('hidden.bs.modal', function() {
                printIframe.src = 'about:blank';
            });
        }
        if (btnPrintFromModal && printIframe) {
            btnPrintFromModal.addEventListener('click', function() {
                try {
                    if (printIframe.contentWindow && printIframe.contentWindow.print) {
                        printIframe.contentWindow.print();
                    }
                } catch (e) {
                    console.error(e);
                }
            });
        }
    });
</script>
@endpush
@endsection
