<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    @php
        $pdfTitle = 'Rapport dépenses par catégorie';
        if ($paroisse) {
            $pdfTitle .= ' — '.$paroisse->nom;
        }
        $pdfTitle .= ' — '.$dateDebut->format('d/m/Y').' au '.$dateFin->format('d/m/Y');
        $labelsCat = trans('expenses.categories');
        $labelsType = trans('expenses.types');
        if (! is_array($labelsCat)) {
            $labelsCat = [];
        }
        if (! is_array($labelsType)) {
            $labelsType = [];
        }
    @endphp
    <title>{{ $pdfTitle }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            color: #333;
            line-height: 1.25;
        }
        .header {
            background-color: {{ $headerConfig['header_bg_color'] ?? '#003366' }};
            color: {{ $headerConfig['header_text_color'] ?? '#FFFFFF' }};
            padding: 8px 12px;
            margin-bottom: 8px;
            border-radius: 2px;
        }
        .header-content { display: table; width: 100%; }
        .header-center { display: table-cell; vertical-align: middle; text-align: center; width: 100%; }
        .header h1 { font-size: 12px; font-weight: bold; margin-bottom: 2px; }
        .header h2 { font-size: 10px; font-weight: normal; margin: 0; }
        .report-title {
            text-align: center;
            margin: 6px 0;
            padding: 6px 10px;
            background-color: #f5f5f5;
            border-left: 3px solid {{ $headerConfig['header_bg_color'] ?? '#003366' }};
        }
        .report-title h3 { font-size: 11px; color: {{ $headerConfig['header_bg_color'] ?? '#003366' }}; margin-bottom: 2px; }
        .report-title p { font-size: 8px; color: #666; }
        .section { margin: 6px 0; page-break-inside: avoid; }
        .section-title {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 4px;
            padding-bottom: 2px;
            border-bottom: 1px solid {{ $headerConfig['header_bg_color'] ?? '#003366' }};
            color: {{ $headerConfig['header_bg_color'] ?? '#003366' }};
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 7px; }
        table th {
            background-color: {{ $headerConfig['header_bg_color'] ?? '#003366' }};
            color: {{ $headerConfig['header_text_color'] ?? '#FFFFFF' }};
            padding: 3px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        table td { padding: 2px 4px; border: 1px solid #ddd; }
        table .text-right { text-align: right; }
        table .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #f0f0f0 !important; }
        .summary-box {
            display: table-cell;
            padding: 8px 10px;
            text-align: center;
            border: 1px solid #ddd;
            width: 100%;
        }
        .summary-row { display: table; width: 100%; margin-bottom: 8px; }
        .footer { margin-top: 8px; font-size: 7px; color: #666; text-align: center; }
        @page { margin: 12mm; size: A4 portrait; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="header-center">
                @if ($headerConfig['title'] ?? null)
                    <h1>{{ $headerConfig['title'] }}</h1>
                @elseif ($paroisse)
                    <h1>{{ $paroisse->nom }}</h1>
                @endif
                @if ($headerConfig['subtitle'] ?? null)
                    <h2>{{ $headerConfig['subtitle'] }}</h2>
                @endif
            </div>
        </div>
    </div>

    <div class="report-title">
        <h3>Rapport par catégories de dépenses</h3>
        <p>Période : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }} — Généré le {{ now()->format('d/m/Y H:i') }}</p>
        @if ($selectedCategorieCharge)
            <p style="margin-top:4px;">
                Filtre catégorie : <strong>{{ $labelsCat[$selectedCategorieCharge] ?? $selectedCategorieCharge }}</strong>
                @if ($selectedTypeCharge)
                    — type : <strong>{{ $labelsType[$selectedTypeCharge] ?? $selectedTypeCharge }}</strong>
                @endif
            </p>
        @endif
    </div>

    <div class="summary-row">
        <div class="summary-box" style="background:#fde8e8;">
            <strong>Total dépenses</strong>
            <div style="font-size:12px;font-weight:bold;margin-top:4px;">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</div>
            <div style="font-size:7px;color:#666;">{{ $report['expenses']->count() }} ligne(s) validée(s)</div>
        </div>
    </div>

    @if (! $selectedCategorieCharge && count($report['by_category']) > 0)
        <div class="section">
            <div class="section-title">Répartition par catégorie</div>
            <table>
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th class="text-center">Nb</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['by_category'] as $row)
                        <tr>
                            <td>{{ $row['nom'] }}</td>
                            <td class="text-center">{{ $row['count'] }}</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($row['montant']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td>TOTAL</td>
                        <td class="text-center">{{ $report['expenses']->count() }}</td>
                        <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    @if ($selectedCategorieCharge && count($report['by_type']) > 0)
        <div class="section">
            <div class="section-title">Répartition par type</div>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th class="text-center">Nb</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['by_type'] as $row)
                        <tr>
                            <td>{{ $row['nom'] }}</td>
                            <td class="text-center">{{ $row['count'] }}</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($row['montant']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @php
        $rowsPdf = $report['expenses']->take(80);
    @endphp
    @if ($rowsPdf->count() > 0)
        <div class="section">
            <div class="section-title">Liste ({{ $report['expenses']->count() }} @if ($report['expenses']->count() > 80) — affichage des 80 premières @endif)</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Cat.</th>
                        <th>Type</th>
                        <th>Libellé</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rowsPdf as $ex)
                        <tr>
                            <td>{{ $ex->date_depense?->format('d/m/Y') }}</td>
                            <td>{{ $labelsCat[$ex->categorie_charge] ?? $ex->categorie_charge }}</td>
                            <td>{{ $labelsType[$ex->type_charge] ?? $ex->type_charge }}</td>
                            <td>{{ Str::limit($ex->libelle ?? '—', 40) }}</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($ex->montant) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="4">TOTAL</td>
                        <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <div class="footer">
        Document généré automatiquement — dépenses au statut « validé » uniquement.
    </div>
</body>
</html>
