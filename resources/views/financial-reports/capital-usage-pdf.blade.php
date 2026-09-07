<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Capital reçu → dépenses — {{ $paroisse?->nom ?? 'Paroisse' }}</title>
    @php
        $fmt = static fn (?float $n): string => \App\Helpers\ParoisseConfig::formatMontant($n, $paroisse?->id);
        $brandColor = $headerConfig['header_bg_color'] ?? '#003366';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; }
        .header { background-color: {{ $brandColor }}; color: #fff; padding: 12px 14px; margin-bottom: 14px; }
        .header h1 { font-size: 15px; font-weight: bold; margin-bottom: 4px; }
        .header p { font-size: 10px; margin: 2px 0; }
        .report-title { font-size: 13px; font-weight: bold; color: {{ $brandColor }}; margin-bottom: 10px; text-align: center; }
        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 14px; }
        .kpi-table td { width: 25%; text-align: center; padding: 10px 6px; border: 1px solid #e2e8f0; background: #f8fafc; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .kpi-value { font-size: 14px; font-weight: bold; }
        .section-title { font-size: 11px; font-weight: bold; margin: 12px 0 6px; color: {{ $brandColor }}; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; }
        table.data th { background: #f1f5f9; font-size: 10px; text-transform: uppercase; }
        table.data td.num { text-align: right; }
        .empty { text-align: center; color: #64748b; font-style: italic; padding: 10px; }
        @page { margin: 16mm; size: A4 portrait; }
    </style>
</head>
<body>
    <div class="header">
        @if (! empty($headerConfig['show_logo']) && ! empty($headerConfig['logo_path']))
            <img src="{{ public_path($headerConfig['logo_path']) }}" alt="" style="max-height: 50px; margin-bottom: 6px;">
        @endif
        <h1>{{ $headerConfig['title'] ?? $paroisse?->nom ?? 'Paroisse' }}</h1>
        @if (! empty($headerConfig['subtitle']))
            <p>{{ $headerConfig['subtitle'] }}</p>
        @endif
        @if (! empty($headerConfig['address']))
            <p>{{ $headerConfig['address'] }}</p>
        @endif
    </div>

    <div class="report-title">
        Capital reçu → dépenses<br>
        <span style="font-size: 10px; font-weight: normal; color: #64748b;">
            {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
        </span>
    </div>

    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-label">Capital Banque reçu</div>
                <div class="kpi-value" style="color: #047857;">{{ $fmt($report['total_capital']) }}</div>
            </td>
            <td>
                <div class="kpi-label">Alloué aux caisses</div>
                <div class="kpi-value" style="color: #0369a1;">{{ $fmt($report['total_virements']) }}</div>
            </td>
            <td>
                <div class="kpi-label">Dépensé</div>
                <div class="kpi-value" style="color: #be123c;">{{ $fmt($report['total_depenses']) }}</div>
            </td>
            <td>
                <div class="kpi-label">Reste alloué</div>
                <div class="kpi-value" style="color: #b45309;">{{ $fmt($report['reste_alloue']) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Synthèse par caisse</div>
    <table class="data">
        <thead>
            <tr>
                <th>Caisse</th>
                <th class="num">Alloué</th>
                <th class="num">Dépensé</th>
                <th class="num">Solde</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['by_caisse'] as $row)
                <tr>
                    <td>{{ $row['nom'] }}</td>
                    <td class="num">{{ $fmt($row['alloue']) }}</td>
                    <td class="num">{{ $fmt($row['depense']) }}</td>
                    <td class="num">{{ $fmt($row['solde']) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Aucun mouvement caisse sur cette période.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Capital Banque reçu</div>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Mois</th>
                <th class="num">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['capitals'] as $revenue)
                <tr>
                    <td>{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                    <td>{{ \App\Support\SubventionMensuelle::formatMoisCapital($revenue->mois_capital) }}</td>
                    <td class="num">{{ $fmt((float) $revenue->montant) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">Aucune recette Banque sur la période.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Virements trésorerie → caisses</div>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Destination</th>
                <th class="num">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['virements'] as $mouvement)
                <tr>
                    <td>{{ $mouvement->date_mouvement?->format('d/m/Y') }}</td>
                    <td>{{ $mouvement->contrepartieCaisse?->nom ?? '—' }}</td>
                    <td class="num">{{ $fmt((float) $mouvement->montant) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">Aucun virement sur la période.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Dépenses financées par les caisses</div>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Libellé</th>
                <th>Type</th>
                <th>Caisse</th>
                <th class="num">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['expenses'] as $expense)
                @php
                    $sourceLabels = $expense->fundingSources
                        ->map(fn ($s) => $s->caisse?->nom)
                        ->filter()
                        ->unique()
                        ->values();
                @endphp
                <tr>
                    <td>{{ $expense->date_depense?->format('d/m/Y') }}</td>
                    <td>{{ $expense->libelle }}</td>
                    <td>{{ $expense->expenseType?->nom ?? '—' }}</td>
                    <td>{{ $sourceLabels->isNotEmpty() ? $sourceLabels->join(', ') : '—' }}</td>
                    <td class="num">{{ $fmt((float) $expense->montant) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Aucune dépense financée par caisse sur la période.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('financial-reports.partials.pdf-signataires-table', ['signataires' => $signataires ?? []])
</body>
</html>
