<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Subvention Popote — Dépenses alimentation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.35;
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
        .summary-box.success { background-color: #d1e7dd; border-color: #badbcc; }
        .summary-box.danger { background-color: #f8d7da; border-color: #f5c2c7; }
        .summary-box.info { background-color: #d1ecf1; border-color: #bee5eb; }
        .summary-box.warning { background-color: #fff3cd; border-color: #ffecb5; }
        .summary-box h4 { font-size: 8px; margin-bottom: 2px; font-weight: bold; }
        .summary-box .amount { font-size: 11px; font-weight: bold; }
        .summary-box .label { font-size: 7px; color: #666; }
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
        <h3>Rapport Subvention Popote — Dépenses alimentation</h3>
        <p>Période : {{ $report['date_debut']->format('d/m/Y') }} au {{ $report['date_fin']->format('d/m/Y') }} — Généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-box success">
                <h4>Subvention Popote reçue</h4>
                <div class="amount">{{ \App\Helpers\ParoisseConfig::formatMontant($report['subvention_recue']) }}</div>
                <div class="label">Période sélectionnée</div>
            </div>
            <div class="summary-box danger">
                <h4>Dépenses alimentation</h4>
                <div class="amount">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_depenses_alimentation']) }}</div>
                <div class="label">{{ $report['depenses_alimentation']->count() }} ligne(s)</div>
            </div>
            <div class="summary-box {{ $report['solde'] >= 0 ? 'info' : 'warning' }}">
                <h4>Solde</h4>
                <div class="amount">{{ \App\Helpers\ParoisseConfig::formatMontant($report['solde']) }}</div>
                <div class="label">{{ $report['solde'] >= 0 ? 'Reste subvention' : 'Dépassement' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Détail des dépenses alimentation</div>
        @if($report['depenses_alimentation']->count() > 0)
            @php $joursLabels = ['lundi'=>'Lundi','mardi'=>'Mardi','mercredi'=>'Mercredi','jeudi'=>'Jeudi','vendredi'=>'Vendredi','samedi'=>'Samedi','dimanche'=>'Dimanche']; @endphp
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Jour</th>
                        <th>Libellé</th>
                        <th class="text-right">Montant</th>
                        <th>Méthode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['depenses_alimentation'] as $dep)
                        <tr>
                            <td>{{ $dep->date_depense?->format('d/m/Y') }}</td>
                            <td>{{ $joursLabels[$dep->jour_semaine] ?? $dep->jour_semaine ?? '—' }}</td>
                            <td>{{ $dep->libelle ?? '—' }}</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($dep->montant) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $dep->methode_paiement ?? '')) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="text-right">Total dépenses alimentation</td>
                        <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_depenses_alimentation']) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @else
            <p style="color: #666; padding: 8px 0;">Aucune dépense alimentation enregistrée pour cette période.</p>
        @endif
    </div>

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
        Rapport Subvention Popote — Dépenses alimentation. Généré le {{ now()->format('d/m/Y à H:i') }}.
    </div>
</body>
</html>
