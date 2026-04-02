<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des revenus - Quête ordinaire</title>
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
        .header-left { display: table-cell; vertical-align: middle; width: {{ $headerConfig['show_logo'] ? '15%' : '0%' }}; }
        .header-center { display: table-cell; vertical-align: middle; text-align: center; width: {{ $headerConfig['show_logo'] ? '85%' : '100%' }}; }
        .header img { max-width: {{ $headerConfig['logo_width'] ?? '50' }}px; max-height: 40px; }
        .header h1 { font-size: 12px; font-weight: bold; margin-bottom: 2px; }
        .header h2 { font-size: 10px; font-weight: normal; margin-bottom: 0; }
        .header p { font-size: 7px; margin: 0; }
        .report-title {
            text-align: center;
            margin: 6px 0;
            padding: 6px 10px;
            background-color: #f5f5f5;
            border-left: 3px solid {{ $headerConfig['header_bg_color'] ?? '#003366' }};
        }
        .report-title h3 { font-size: 11px; color: {{ $headerConfig['header_bg_color'] ?? '#003366' }}; margin-bottom: 2px; }
        .report-title p { font-size: 8px; color: #666; }
        .summary { margin: 6px 0; }
        .summary-row { display: table; width: 100%; }
        .summary-box {
            display: table-cell;
            width: 33.33%;
            padding: 6px 8px;
            text-align: center;
            vertical-align: top;
            border: 1px solid #ddd;
            border-radius: 2px;
        }
        .summary-box.primary { background-color: #cfe2ff; border-color: #b6d4fe; }
        .summary-box.success { background-color: #d1e7dd; border-color: #badbcc; }
        .summary-box.info { background-color: #d1ecf1; border-color: #bee5eb; }
        .summary-box h4 { font-size: 8px; margin-bottom: 2px; font-weight: bold; }
        .summary-box .amount { font-size: 11px; font-weight: bold; }
        .summary-box .label { font-size: 7px; color: #666; }
        .details-grid { display: table; width: 100%; margin-bottom: 8px; }
        .details-col { display: table-cell; width: 50%; padding: 0 6px; vertical-align: top; }
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
        table tr:nth-child(even) { background-color: #f9f9f9; }
        table .text-right { text-align: right; }
        table .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #f0f0f0 !important; }
        .footer {
            margin-top: 8px;
            padding-top: 4px;
            border-top: 1px solid #ddd;
            font-size: 7px;
            color: #666;
            text-align: center;
        }
        @page { margin: 12mm; size: A4 portrait; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            @if($headerConfig['show_logo'] && $headerConfig['logo_path'])
                <div class="header-left">
                    @php
                        $logoPath = $headerConfig['logo_path'];
                        $logoBase64 = null;
                        if (!str_starts_with($logoPath, 'http') && !str_starts_with($logoPath, 'data:')) {
                            $fullPath = str_starts_with($logoPath, '/') ? public_path($logoPath) : public_path('/' . ltrim($logoPath, '/'));
                            if (file_exists($fullPath) && is_file($fullPath)) {
                                try {
                                    $imageData = base64_encode(file_get_contents($fullPath));
                                    $imageInfo = @getimagesize($fullPath);
                                    if ($imageInfo !== false) {
                                        $logoBase64 = 'data:' . $imageInfo['mime'] . ';base64,' . $imageData;
                                    }
                                } catch (\Exception $e) {}
                            }
                        } elseif (str_starts_with($logoPath, 'data:')) {
                            $logoBase64 = $logoPath;
                        }
                    @endphp
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: {{ $headerConfig['logo_width'] ?? '50' }}px;">
                    @endif
                </div>
            @endif
            <div class="header-center">
                @if($headerConfig['title'])
                    <h1>{{ $headerConfig['title'] }}</h1>
                @elseif($paroisse)
                    <h1>{{ $paroisse->nom }}</h1>
                @endif
                @if($headerConfig['subtitle'])
                    <h2>{{ $headerConfig['subtitle'] }}</h2>
                @endif
                @if($headerConfig['address'])
                    <p>{{ $headerConfig['address'] }}</p>
                @elseif($paroisse && $paroisse->adresse)
                    <p>{{ $paroisse->adresse }}, {{ $paroisse->ville ?? '' }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="report-title">
        <h3>Rapport des revenus - Quête ordinaire</h3>
        <p>Période : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }} — Généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-box primary">
                <h4>Total Semaine</h4>
                <div class="amount">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_semaine']) }}</div>
                <div class="label">Lundi - Samedi</div>
            </div>
            <div class="summary-box success">
                <h4>Total Dimanche</h4>
                <div class="amount">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_dimanche']) }}</div>
                <div class="label">Dimanche</div>
            </div>
            <div class="summary-box info">
                <h4>Total Général</h4>
                <div class="amount">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</div>
                <div class="label">Semaine + Dimanche</div>
            </div>
        </div>
    </div>

    <div class="details-grid">
        <div class="details-col">
            <div class="section">
                <div class="section-title">Semaine (Lundi - Samedi)</div>
                <table>
                    <thead>
                        <tr>
                            <th>Jour</th>
                            <th class="text-right">Montant</th>
                            <th class="text-center">Nb</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $joursLabels = ['lundi'=>'Lun','mardi'=>'Mar','mercredi'=>'Mer','jeudi'=>'Jeu','vendredi'=>'Ven','samedi'=>'Sam'];
                        @endphp
                        @foreach(['lundi','mardi','mercredi','jeudi','vendredi','samedi'] as $jour)
                            <tr>
                                <td>{{ $joursLabels[$jour] }}</td>
                                <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_semaine'][$jour]['montant'] ?? 0) }}</td>
                                <td class="text-center">{{ $report['details_semaine'][$jour]['count'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td>TOTAL</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_semaine']) }}</td>
                            <td class="text-center">{{ $report['revenues_semaine']->count() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="details-col">
            <div class="section">
                <div class="section-title">Dimanche</div>
                <table>
                    <thead>
                        <tr>
                            <th>Jour</th>
                            <th class="text-right">Montant</th>
                            <th class="text-center">Nb</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Dimanche</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_dimanche']) }}</td>
                            <td class="text-center">{{ $report['details_dimanche']['count'] }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>TOTAL</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_dimanche']) }}</td>
                            <td class="text-center">{{ $report['details_dimanche']['count'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @php
        $revenuesForPdf = $report['revenues_all']->take(12);
        $totalRevenues = $report['revenues_all']->count();
    @endphp
    @if($totalRevenues > 0)
        <div class="section">
            <div class="section-title">Liste des recettes ({{ $totalRevenues > 12 ? '12 premières sur ' . $totalRevenues : $totalRevenues }})</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Jour</th>
                        <th>Pér.</th>
                        <th>Méth.</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revenuesForPdf as $revenue)
                        <tr>
                            <td>{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                            <td>{{ ['lundi'=>'Lun','mardi'=>'Mar','mercredi'=>'Mer','jeudi'=>'Jeu','vendredi'=>'Ven','samedi'=>'Sam','dimanche'=>'Dim'][$revenue->jour_semaine] ?? '—' }}</td>
                            <td>{{ ($revenue->periode_messe === 'semaine' || in_array($revenue->jour_semaine, ['lundi','mardi','mercredi','jeudi','vendredi','samedi'])) ? 'Sem.' : 'Dim.' }}</td>
                            <td>{{ $revenue->methode_paiement ?? '—' }}</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($revenue->montant) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @if($totalRevenues > 12)
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="4">… ({{ $totalRevenues }} recettes) — TOTAL</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                        </tr>
                    </tfoot>
                @else
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="4">TOTAL GÉNÉRAL</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif

    {{-- Signataires --}}
    <div class="signatures" style="margin-top: 20px; page-break-inside: avoid;">
        <div class="section-title">Signatures</div>
        <table style="width: 100%; border: none; margin-top: 12px;">
            <tr>
                <td style="width: 33%; text-align: center; border: none; padding: 8px;">
                    <div style="border-bottom: 1px solid #333; margin-bottom: 6px; height: 35px;"></div>
                    <strong style="font-size: 8px;">Le Curé</strong>
                    <p style="font-size: 6px; color: #666; margin-top: 2px;">Nom et signature</p>
                </td>
                <td style="width: 33%; text-align: center; border: none; padding: 8px;">
                    <div style="border-bottom: 1px solid #333; margin-bottom: 6px; height: 35px;"></div>
                    <strong style="font-size: 8px;">Le Gestionnaire</strong>
                    <p style="font-size: 6px; color: #666; margin-top: 2px;">Nom et signature</p>
                </td>
                <td style="width: 33%; text-align: center; border: none; padding: 8px;">
                    <div style="border-bottom: 1px solid #333; margin-bottom: 6px; height: 35px;"></div>
                    <strong style="font-size: 8px;">Le Vicaire Économe</strong>
                    <p style="font-size: 6px; color: #666; margin-top: 2px;">Nom et signature</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Rapport des revenus - Quête ordinaire. Généré le {{ now()->format('d/m/Y à H:i') }}.
    </div>
</body>
</html>
