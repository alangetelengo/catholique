<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $pdfTitle = 'Rapport dépenses par catégorie';
        if ($paroisse) {
            $pdfTitle .= ' — '.$paroisse->nom;
        }
        $pdfTitle .= ' — '.$dateDebut->format('d/m/Y').' au '.$dateFin->format('d/m/Y');
        $selectedCategory = null;
        if (!empty($selectedRevenueCategoryId)) {
            $selectedCategory = \App\Models\RevenueCategory::find($selectedRevenueCategoryId);
        }
        $selectedType = null;
        if (!empty($selectedRevenueTypeId)) {
            $selectedType = \App\Models\RevenueType::find($selectedRevenueTypeId);
        }
    @endphp
    <title>{{ $pdfTitle }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background-color: {{ $headerConfig['header_bg_color'] ?? '#003366' }};
            color: {{ $headerConfig['header_text_color'] ?? '#FFFFFF' }};
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .header-content { display: table; width: 100%; }
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: {{ ($headerConfig['show_logo'] ?? false) && ($headerConfig['logo_path'] ?? null) ? '20%' : '0%' }};
        }
        .header-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            width: {{ ($headerConfig['show_logo'] ?? false) && ($headerConfig['logo_path'] ?? null) ? '60%' : '100%' }};
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: {{ ($headerConfig['show_logo'] ?? false) && ($headerConfig['logo_path'] ?? null) ? '20%' : '0%' }};
        }
        .header img {
            max-width: {{ $headerConfig['logo_width'] ?? '80' }}px;
            max-height: 80px;
        }
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            color: {{ $headerConfig['header_text_color'] ?? '#FFFFFF' }};
        }
        .header h2 {
            font-size: 16px;
            font-weight: normal;
            margin-bottom: 5px;
            color: {{ $headerConfig['header_text_color'] ?? '#FFFFFF' }};
        }
        .header p {
            font-size: 10px;
            margin: 2px 0;
            color: {{ $headerConfig['header_text_color'] ?? '#FFFFFF' }};
        }
        .report-title {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-left: 4px solid {{ $headerConfig['header_bg_color'] ?? '#003366' }};
        }
        .report-title h3 {
            font-size: 18px;
            color: {{ $headerConfig['header_bg_color'] ?? '#003366' }};
            margin-bottom: 5px;
        }
        .report-title p {
            font-size: 11px;
            color: #666;
        }
        .summary { margin: 20px 0; }
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-box {
            display: table-cell;
            width: 100%;
            padding: 15px;
            text-align: center;
            vertical-align: top;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .summary-box.danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .summary-box h4 {
            font-size: 12px;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .summary-box .amount {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-box .label {
            font-size: 9px;
            color: #666;
        }
        .section {
            margin: 12px 0;
            page-break-inside: auto;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid {{ $headerConfig['header_bg_color'] ?? '#003366' }};
            color: {{ $headerConfig['header_bg_color'] ?? '#003366' }};
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        table th {
            background-color: {{ $headerConfig['header_bg_color'] ?? '#003366' }};
            color: {{ $headerConfig['header_text_color'] ?? '#FFFFFF' }};
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }
        table tr:nth-child(even) { background-color: #f9f9f9; }
        table .text-right { text-align: right; }
        table .text-center { text-align: center; }
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0 !important;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        @page { margin: 20mm; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            @if(($headerConfig['show_logo'] ?? false) && ($headerConfig['logo_path'] ?? null))
                <div class="header-left">
                    @php
                        $logoPath = $headerConfig['logo_path'];
                        $logoBase64 = null;
                        if (! str_starts_with($logoPath, 'http') && ! str_starts_with($logoPath, 'data:')) {
                            if (str_starts_with($logoPath, '/')) {
                                $logoPath = public_path($logoPath);
                            } else {
                                $logoPath = public_path('/'.ltrim($logoPath, '/'));
                            }
                            if (file_exists($logoPath) && is_file($logoPath)) {
                                try {
                                    $imageData = base64_encode(file_get_contents($logoPath));
                                    $imageInfo = @getimagesize($logoPath);
                                    if ($imageInfo !== false) {
                                        $mimeType = $imageInfo['mime'];
                                        $logoBase64 = 'data:'.$mimeType.';base64,'.$imageData;
                                    }
                                } catch (\Exception $e) {
                                }
                            }
                        } elseif (str_starts_with($logoPath, 'data:')) {
                            $logoBase64 = $logoPath;
                        }
                    @endphp
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: {{ $headerConfig['logo_width'] ?? '80' }}px;">
                    @endif
                </div>
            @endif
            <div class="header-center">
                @if($headerConfig['title'] ?? null)
                    <h1>{{ $headerConfig['title'] }}</h1>
                @elseif($paroisse)
                    <h1>{{ $paroisse->nom }}</h1>
                @endif
                @if($headerConfig['subtitle'] ?? null)
                    <h2>{{ $headerConfig['subtitle'] }}</h2>
                @endif
                @if($headerConfig['address'] ?? null)
                    <p>{{ $headerConfig['address'] }}</p>
                @elseif($paroisse && $paroisse->adresse)
                    <p>{{ $paroisse->adresse }}, {{ $paroisse->ville ?? '' }}</p>
                @endif
                @if($headerConfig['phone'] ?? null)
                    <p>Tél: {{ $headerConfig['phone'] }}</p>
                @elseif($paroisse && $paroisse->telephone)
                    <p>Tél: {{ $paroisse->telephone }}</p>
                @endif
                @if($headerConfig['email'] ?? null)
                    <p>Email: {{ $headerConfig['email'] }}</p>
                @elseif($paroisse && $paroisse->email)
                    <p>Email: {{ $paroisse->email }}</p>
                @endif
                @if($headerConfig['custom_text'] ?? null)
                    <p style="margin-top: 10px; font-style: italic;">{{ $headerConfig['custom_text'] }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="report-title">
        <h3>Rapport par catégories de dépenses</h3>
        <p>
            Période : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }}
            <br>
            Généré le : {{ now()->format('d/m/Y à H:i') }}
        </p>
        @if ($selectedCategory)
            <p style="margin-top:8px;">
                Filtre catégorie : <strong>{{ $selectedCategory->nom }}</strong>
                @if ($selectedType)
                    — type : <strong>{{ $selectedType->nom }}</strong>
                @endif
            </p>
        @endif
    </div>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-box danger">
                <h4>Total dépenses</h4>
                <div class="amount" style="color:#721c24;">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</div>
                <div class="label">{{ $report['expenses']->count() }} ligne(s) au statut « validé »</div>
            </div>
        </div>
    </div>

    @if (! $selectedRevenueCategoryId && count($report['by_category']) > 0)
        <div class="section">
            <div class="section-title">Répartition par catégorie (source des fonds)</div>
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

    @if ($selectedRevenueCategoryId && count($report['by_type']) > 0)
        <div class="section">
            <div class="section-title">Répartition par type (précision de la source)</div>
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
            <div class="section-title">Liste détaillée ({{ $report['expenses']->count() }} @if ($report['expenses']->count() > 80) — affichage des 80 premières lignes @endif)</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Catégorie</th>
                        <th>Type</th>
                        <th>Libellé</th>
                        <th>Fournisseur</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rowsPdf as $ex)
                        <tr>
                            <td>{{ $ex->date_depense?->format('d/m/Y') }}</td>
                            <td>{{ $ex->revenueCategory?->nom ?? '—' }}</td>
                            <td>
                                @php
                                    $sourceLabels = $ex->fundingSources
                                        ? $ex->fundingSources
                                            ->map(fn ($source) => $source->revenueType?->nom)
                                            ->filter()
                                            ->values()
                                        : collect();
                                @endphp
                                {{ $sourceLabels->isNotEmpty() ? $sourceLabels->join(', ') : ($ex->revenueType?->nom ?? '—') }}
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($ex->libelle ?? '—', 36) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($ex->fournisseur ?? '—', 28) }}</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($ex->montant) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5">TOTAL</td>
                        <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    @include('financial-reports.partials.pdf-signataires-table')

    <div class="footer">
        <p><strong>Note :</strong> ce document ne retient que les dépenses au statut « validé » sur la période affichée.</p>
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
