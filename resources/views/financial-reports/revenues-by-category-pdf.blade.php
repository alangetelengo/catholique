<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport recettes — {{ $paroisse?->nom ?? 'Paroisse' }}</title>
    @php
        $fmt = static fn (?float $n): string => \App\Helpers\ParoisseConfig::formatMontant($n, $paroisse?->id);
        $selectedCategory = ! empty($selectedCategoryId)
            ? \App\Models\RevenueCategory::find($selectedCategoryId)
            : null;
        $selectedType = ! empty($selectedTypeId)
            ? \App\Models\RevenueType::find($selectedTypeId)
            : null;
        $isSubventionCategory = false;
        $envelopes = collect();
        $w = $report['weekly'] ?? null;
        $showWeeklyBreakdown = ($showWeeklyBreakdown ?? false) && $w;
        $showRptSemaine = $showRptSemaine ?? true;
        $showRptDimanche = $showRptDimanche ?? true;
        $joursLabels = [
            'lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi',
            'vendredi' => 'Vendredi', 'samedi' => 'Samedi', 'dimanche' => 'Dimanche',
        ];
        $brandColor = $headerConfig['header_bg_color'] ?? '#003366';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.5;
        }
        .header {
            background-color: {{ $brandColor }};
            color: #fff;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .header .meta {
            font-size: 11px;
            opacity: 0.95;
        }
        .header .meta p { margin: 2px 0; }
        .kpi-row {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 18px;
        }
        .kpi-row td {
            vertical-align: top;
            text-align: center;
            padding: 12px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
        }
        .kpi-row .kpi-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            margin-bottom: 6px;
        }
        .kpi-row .kpi-value {
            font-size: 18px;
            font-weight: bold;
            line-height: 1.2;
        }
        .kpi-row .kpi-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
        }
        .kpi-received .kpi-value { color: #047857; }
        .kpi-week .kpi-value { color: #0369a1; }
        .kpi-sunday .kpi-value { color: #047857; }
        .kpi-total-only td {
            width: 100%;
            background: #ecfdf5;
            border-color: #a7f3d0;
        }
        .kpi-total-only .kpi-value { color: #047857; font-size: 22px; }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: {{ $brandColor }};
            margin: 16px 0 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid {{ $brandColor }};
        }
        .simple-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 11px;
        }
        .simple-table th {
            background: {{ $brandColor }};
            color: #fff;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        .simple-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .simple-table .text-right { text-align: right; }
        .simple-table .total-row td {
            font-weight: bold;
            background: #f1f5f9;
            border-top: 2px solid #cbd5e1;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
        @page { margin: 16mm; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $headerConfig['title'] ?? $paroisse?->nom ?? 'Paroisse' }}</h1>
        <div class="meta">
            <p><strong>Rapport des recettes</strong>
                @if ($selectedCategory)
                    — {{ $selectedCategory->nom }}
                    @if ($selectedType)
                        · {{ $selectedType->nom }}
                    @endif
                @elseif ($pdfCategoryNom ?? null)
                    — {{ $pdfCategoryNom }}
                    @if ($pdfTypeNom ?? null)
                        · {{ $pdfTypeNom }}
                    @endif
                @endif
            </p>
            <p>Période : {{ $dateDebut->format('d/m/Y') }} → {{ $dateFin->format('d/m/Y') }}</p>
            <p>Édité le {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>

    @if ($isSubventionCategory && $envelopes->isNotEmpty())
        <div class="section-title">Synthèse subvention</div>
        <table class="kpi-row">
            <tr>
                <td class="kpi-received" style="width:33.33%;">
                    <div class="kpi-label">Subvention reçue</div>
                    <div class="kpi-value">{{ $fmt((float) $envelopes->sum('montant')) }}</div>
                    <div class="kpi-sub">{{ $envelopes->count() }} enveloppe(s)</div>
                </td>
                <td class="kpi-week" style="width:33.33%;">
                    <div class="kpi-label">Recettes (période)</div>
                    <div class="kpi-value">{{ $fmt($report['total_general']) }}</div>
                    <div class="kpi-sub">{{ $report['revenues']->count() }} ligne(s)</div>
                </td>
                <td style="width:33.33%;">
                    <div class="kpi-label">Types distincts</div>
                    <div class="kpi-value">{{ $envelopes->pluck('type_id')->unique()->count() }}</div>
                    <div class="kpi-sub">ventilation mensuelle</div>
                </td>
            </tr>
        </table>

        @if ($envelopes->count() > 1)
            <div class="section-title">Détail par mois concerné</div>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Mois</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($envelopes as $row)
                        <tr>
                            <td>{{ $row['type_nom'] }}</td>
                            <td>{{ $row['mois_label'] }}</td>
                            <td class="text-right">{{ $fmt($row['montant']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif ($envelopes->count() === 1)
            @php $env = $envelopes->first(); @endphp
            <p style="font-size:11px;color:#64748b;margin-bottom:12px;">
                Enveloppe : <strong>{{ $env['label'] }}</strong>
            </p>
        @endif
    @elseif ($showWeeklyBreakdown)
        @php
            $colCount = 1 + (int) $showRptSemaine + (int) $showRptDimanche;
            $colWidth = $colCount === 3 ? '33.33%' : '50%';
        @endphp
        <table class="kpi-row">
            <tr>
                @if ($showRptSemaine)
                    <td class="kpi-week" style="width:{{ $colWidth }};">
                        <div class="kpi-label">Total semaine</div>
                        <div class="kpi-value">{{ $fmt($w['total_semaine']) }}</div>
                        <div class="kpi-sub">Lundi – samedi</div>
                    </td>
                @endif
                @if ($showRptDimanche)
                    <td class="kpi-sunday" style="width:{{ $colWidth }};">
                        <div class="kpi-label">Total dimanche</div>
                        <div class="kpi-value">{{ $fmt($w['total_dimanche']) }}</div>
                        <div class="kpi-sub">Messe du dimanche</div>
                    </td>
                @endif
                <td style="width:{{ $colWidth }};">
                    <div class="kpi-label">Total recettes</div>
                    <div class="kpi-value">{{ $fmt($report['total_general']) }}</div>
                    <div class="kpi-sub">{{ $rptTotalSubtitle ?? 'Période' }}</div>
                </td>
            </tr>
        </table>

        @if ($showRptSemaine)
            <div class="section-title">Semaine (lundi – samedi)</div>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'] as $jour)
                        <tr>
                            <td>{{ $joursLabels[$jour] }}</td>
                            <td class="text-right">{{ $fmt($w['details_semaine'][$jour]['montant'] ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @else
        <table class="kpi-row kpi-total-only">
            <tr>
                <td>
                    <div class="kpi-label">Total des recettes</div>
                    <div class="kpi-value">{{ $fmt($report['total_general']) }}</div>
                    <div class="kpi-sub">{{ $report['revenues']->count() }} ligne(s) validée(s)</div>
                </td>
            </tr>
        </table>

        @if (! $selectedCategoryId && count($report['by_category']) > 0)
            <div class="section-title">Par catégorie</div>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['by_category'] as $row)
                        <tr>
                            <td>{{ $row['nom'] }}</td>
                            <td class="text-right">{{ $fmt($row['montant']) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Total</td>
                        <td class="text-right">{{ $fmt($report['total_general']) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        @if ($selectedCategoryId && count($report['by_type']) > 0)
            <div class="section-title">Par type</div>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['by_type'] as $row)
                        <tr>
                            <td>{{ $row['nom'] }}</td>
                            <td class="text-right">{{ $fmt($row['montant']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    @if ($report['revenues']->count() === 0)
        <p style="text-align:center;color:#64748b;padding:24px 0;">Aucune recette validée pour cette période.</p>
    @endif

    @include('financial-reports.partials.pdf-signataires-table')

    <div class="footer">
        Recettes au statut « validé » uniquement — {{ $paroisse?->nom ?? '' }}
    </div>
</body>
</html>
