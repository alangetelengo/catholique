<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport dépenses — {{ $paroisse?->nom ?? 'Paroisse' }}</title>
    @php
        $fmt = static fn (?float $n): string => \App\Helpers\ParoisseConfig::formatMontant($n, $paroisse?->id);
        $selectedCategory = ! empty($selectedRevenueCategoryId)
            ? \App\Models\RevenueCategory::find($selectedRevenueCategoryId)
            : null;
        $selectedType = ! empty($selectedRevenueTypeId)
            ? \App\Models\RevenueType::find($selectedRevenueTypeId)
            : null;
        $isSubventionCategory = $selectedCategory && $selectedCategory->code === \App\Support\SubventionMensuelle::CATEGORY_CODE;
        $envelopes = collect($report['subvention_envelopes'] ?? []);
        $totalSubventionRecue = (float) $envelopes->sum('subvention_recue');
        $totalSubventionDepenses = (float) $envelopes->sum('depenses');
        $totalSubventionSolde = (float) $envelopes->sum('solde');
        $fundingSourceLabel = static function ($source): string {
            $type = $source->revenueType;
            if (! $type) {
                return '—';
            }
            if ($source->revenue?->mois_subvention) {
                return \App\Support\SubventionMensuelle::envelopeLabel($type, $source->revenue->mois_subvention);
            }

            return $type->nom;
        };
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
        .report-heading {
            font-size: 14px;
            font-weight: bold;
            color: {{ $brandColor }};
            margin-bottom: 12px;
        }
        .kpi-row {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 18px;
        }
        .kpi-row td {
            width: 33.33%;
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
        .kpi-spent .kpi-value { color: #be123c; }
        .kpi-balance .kpi-value { color: #0369a1; }
        .kpi-total-only td {
            width: 100%;
            background: #fff1f2;
            border-color: #fecdd3;
        }
        .kpi-total-only .kpi-value { color: #be123c; font-size: 22px; }
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
        .expense-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .expense-card-top {
            width: 100%;
            margin-bottom: 4px;
        }
        .expense-card-top td { vertical-align: top; padding: 0; }
        .expense-date {
            font-size: 12px;
            font-weight: bold;
            color: #334155;
        }
        .expense-amount {
            font-size: 14px;
            font-weight: bold;
            color: #be123c;
            text-align: right;
        }
        .expense-source {
            font-size: 11px;
            color: #047857;
            margin-bottom: 2px;
        }
        .expense-libelle {
            font-size: 11px;
            color: #475569;
        }
        .expense-fournisseur {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 2px;
        }
        .total-bar {
            margin-top: 10px;
            padding: 10px 12px;
            background: #f1f5f9;
            border-radius: 6px;
            font-weight: bold;
            font-size: 13px;
        }
        .total-bar table { width: 100%; }
        .total-bar .amount { text-align: right; color: #be123c; }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
        @page { margin: 14mm; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $headerConfig['title'] ?? $paroisse?->nom ?? 'Paroisse' }}</h1>
        <div class="meta">
            <p><strong>Rapport des dépenses</strong>
                @if ($selectedCategory)
                    — {{ $selectedCategory->nom }}
                    @if ($selectedType)
                        · {{ $selectedType->nom }}
                    @endif
                @endif
            </p>
            <p>Période : {{ $dateDebut->format('d/m/Y') }} → {{ $dateFin->format('d/m/Y') }}</p>
            <p>Édité le {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>

    @if ($isSubventionCategory && $envelopes->isNotEmpty())
        <div class="report-heading">Synthèse subvention</div>
        <table class="kpi-row">
            <tr>
                <td class="kpi-received">
                    <div class="kpi-label">Subvention reçue</div>
                    <div class="kpi-value">{{ $fmt($totalSubventionRecue) }}</div>
                    <div class="kpi-sub">{{ $envelopes->count() }} enveloppe(s)</div>
                </td>
                <td class="kpi-spent">
                    <div class="kpi-label">Dépensé (période)</div>
                    <div class="kpi-value">{{ $fmt($totalSubventionDepenses) }}</div>
                    <div class="kpi-sub">{{ $report['expenses']->count() }} opération(s)</div>
                </td>
                <td class="kpi-balance">
                    <div class="kpi-label">Solde restant</div>
                    <div class="kpi-value">{{ $fmt($totalSubventionSolde) }}</div>
                    <div class="kpi-sub">recettes − dépenses</div>
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
                        <th class="text-right">Reçu</th>
                        <th class="text-right">Dépensé</th>
                        <th class="text-right">Solde</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($envelopes as $row)
                        <tr>
                            <td>{{ $row['type_nom'] }}</td>
                            <td>{{ $row['mois_label'] }}</td>
                            <td class="text-right">{{ $fmt($row['subvention_recue']) }}</td>
                            <td class="text-right">{{ $fmt($row['depenses']) }}</td>
                            <td class="text-right">{{ $fmt($row['solde']) }}</td>
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
    @else
        <table class="kpi-row kpi-total-only">
            <tr>
                <td>
                    <div class="kpi-label">Total des dépenses</div>
                    <div class="kpi-value">{{ $fmt($report['total_general']) }}</div>
                    <div class="kpi-sub">{{ $report['expenses']->count() }} ligne(s) validée(s)</div>
                </td>
            </tr>
        </table>

        @if (! $selectedRevenueCategoryId && count($report['by_category']) > 0)
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

        @if ($selectedRevenueCategoryId && count($report['by_type']) > 0)
            <div class="section-title">Par type de source</div>
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

    @php $rowsPdf = $report['expenses']->take(80); @endphp
    @if ($rowsPdf->count() > 0)
        <div class="section-title">
            Détail des opérations ({{ $report['expenses']->count() }}
            @if ($report['expenses']->count() > 80) — 80 premières affichées @endif)
        </div>

        @foreach ($rowsPdf as $ex)
            <div class="expense-card">
                <table class="expense-card-top">
                    <tr>
                        <td class="expense-date">{{ $ex->date_depense?->format('d/m/Y') }}</td>
                        <td class="expense-amount">{{ $fmt((float) $ex->montant) }}</td>
                    </tr>
                </table>
                @if ($ex->fundingSources->isNotEmpty())
                    @foreach ($ex->fundingSources as $source)
                        <div class="expense-source">{{ $fundingSourceLabel($source) }}</div>
                    @endforeach
                @elseif ($ex->revenueType)
                    <div class="expense-source">{{ $ex->revenueType->nom }}</div>
                @endif
                <div class="expense-libelle">{{ $ex->libelle ?: '—' }}</div>
                @if ($ex->fournisseur)
                    <div class="expense-fournisseur">Fournisseur : {{ $ex->fournisseur }}</div>
                @endif
            </div>
        @endforeach

        <div class="total-bar">
            <table>
                <tr>
                    <td>Total des dépenses</td>
                    <td class="amount">{{ $fmt($report['total_general']) }}</td>
                </tr>
            </table>
        </div>
    @else
        <p style="text-align:center;color:#64748b;padding:24px 0;">Aucune dépense validée pour cette période.</p>
    @endif

    @include('financial-reports.partials.pdf-signataires-table')

    <div class="footer">
        Dépenses au statut « validé » uniquement — {{ $paroisse?->nom ?? '' }}
    </div>
</body>
</html>
