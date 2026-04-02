<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des revenus - Quête ordinaire (impression)</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.35;
            padding: 16px;
            max-width: 210mm;
            margin: 0 auto;
        }
        .no-print {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .btn-print {
            background: #6A1B9A;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print:hover { background: #5a1590; }
        .btn-print:focus { outline: 2px solid #6A1B9A; outline-offset: 2px; }
        .print-page {
            font-size: 10px;
            line-height: 1.3;
        }
        .header {
            background-color: {{ $headerConfig['header_bg_color'] ?? '#003366' }};
            color: {{ $headerConfig['header_text_color'] ?? '#FFFFFF' }};
            padding: 10px 14px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .header-content { display: flex; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap; }
        .header img { max-width: 60px; max-height: 50px; }
        .header h1 { font-size: 14px; font-weight: bold; }
        .header h2 { font-size: 12px; font-weight: normal; }
        .header p { font-size: 10px; margin-top: 2px; }
        .report-title {
            text-align: center;
            margin: 8px 0;
            padding: 8px 12px;
            background-color: #f5f5f5;
            border-left: 4px solid {{ $headerConfig['header_bg_color'] ?? '#003366' }};
        }
        .report-title h3 { font-size: 13px; color: {{ $headerConfig['header_bg_color'] ?? '#003366' }}; margin-bottom: 4px; }
        .report-title p { font-size: 11px; color: #666; }
        .summary { margin: 10px 0; display: flex; gap: 10px; flex-wrap: wrap; }
        .summary-box {
            flex: 1;
            min-width: 120px;
            padding: 10px 12px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .summary-box.primary { background-color: #cfe2ff; border-color: #b6d4fe; }
        .summary-box.success { background-color: #d1e7dd; border-color: #badbcc; }
        .summary-box.info { background-color: #d1ecf1; border-color: #bee5eb; }
        .summary-box h4 { font-size: 10px; margin-bottom: 4px; font-weight: bold; }
        .summary-box .amount { font-size: 14px; font-weight: bold; }
        .summary-box .label { font-size: 9px; color: #666; }
        .details-grid { display: flex; gap: 16px; margin-bottom: 12px; flex-wrap: wrap; }
        .details-col { flex: 1; min-width: 200px; }
        .section { margin: 10px 0; }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 2px solid {{ $headerConfig['header_bg_color'] ?? '#003366' }};
            color: {{ $headerConfig['header_bg_color'] ?? '#003366' }};
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 10px; }
        table th {
            background-color: {{ $headerConfig['header_bg_color'] ?? '#003366' }};
            color: {{ $headerConfig['header_text_color'] ?? '#FFFFFF' }};
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        table td { padding: 4px 8px; border: 1px solid #ddd; }
        table tr:nth-child(even) { background-color: #f9f9f9; }
        table .text-right { text-align: right; }
        table .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #f0f0f0 !important; }
        .footer {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        @media print {
            body { padding: 0; max-width: none; font-size: 9px; }
            .no-print { display: none !important; }
            .print-page { font-size: 8px; }
            .header { padding: 6px 10px; }
            .header h1 { font-size: 11px; }
            .header h2 { font-size: 9px; }
            .header p { font-size: 8px; }
            .report-title { margin: 6px 0; padding: 6px 10px; }
            .report-title h3 { font-size: 10px; }
            .report-title p { font-size: 8px; }
            .summary { margin: 6px 0; }
            .summary-box { padding: 6px 8px; }
            .summary-box h4 { font-size: 8px; }
            .summary-box .amount { font-size: 10px; }
            .summary-box .label { font-size: 7px; }
            .details-grid { margin-bottom: 8px; }
            .section { margin: 6px 0; page-break-inside: avoid; }
            .section-title { font-size: 9px; margin-bottom: 4px; }
            table { font-size: 7px; margin-bottom: 6px; }
            table th, table td { padding: 3px 4px; }
            .footer { margin-top: 8px; font-size: 7px; }
            @page { margin: 12mm; size: A4 portrait; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <strong>Rapport des revenus - Quête ordinaire</strong>
        <button type="button" class="btn-print" onclick="window.print();">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path></svg>
            Imprimer
        </button>
    </div>

    <div class="print-page">
        <div class="header">
            <div class="header-content">
                @if($headerConfig['show_logo'] && $headerConfig['logo_path'])
                    @php
                        $logoPath = $headerConfig['logo_path'];
                        $logoUrl = null;
                        if (!str_starts_with($logoPath, 'http') && !str_starts_with($logoPath, 'data:')) {
                            $logoUrl = str_starts_with($logoPath, '/') ? asset($logoPath) : asset('/' . ltrim($logoPath, '/'));
                        } elseif (str_starts_with($logoPath, 'data:')) {
                            $logoUrl = $logoPath;
                        }
                    @endphp
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo" style="max-width: {{ $headerConfig['logo_width'] ?? '60' }}px;">
                    @endif
                @endif
                <div>
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
                            @php $joursLabels = ['lundi'=>'Lundi','mardi'=>'Mardi','mercredi'=>'Mercredi','jeudi'=>'Jeudi','vendredi'=>'Vendredi','samedi'=>'Samedi']; @endphp
                            @foreach(['lundi','mardi','mercredi','jeudi','vendredi','samedi'] as $jour)
                                <tr>
                                    <td>{{ $joursLabels[$jour] }}</td>
                                    <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_semaine'][$jour]['montant'] ?? 0) }}</td>
                                    <td class="text-center">{{ $report['details_semaine'][$jour]['count'] ?? 0 }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td>TOTAL SEMAINE</td>
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
                                <td>TOTAL DIMANCHE</td>
                                <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_dimanche']) }}</td>
                                <td class="text-center">{{ $report['details_dimanche']['count'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($report['revenues_all']->count() > 0)
            <div class="section">
                <div class="section-title">Liste détaillée des recettes ({{ $report['revenues_all']->count() }})</div>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Jour</th>
                            <th>Période</th>
                            <th>Méthode</th>
                            <th class="text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $joursLabels = ['lundi'=>'Lun','mardi'=>'Mar','mercredi'=>'Mer','jeudi'=>'Jeu','vendredi'=>'Ven','samedi'=>'Sam','dimanche'=>'Dim']; @endphp
                        @foreach($report['revenues_all']->take(20) as $revenue)
                            <tr>
                                <td>{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                                <td>{{ $joursLabels[$revenue->jour_semaine] ?? '—' }}</td>
                                <td>{{ ($revenue->periode_messe === 'semaine' || in_array($revenue->jour_semaine, ['lundi','mardi','mercredi','jeudi','vendredi','samedi'])) ? 'Semaine' : 'Dimanche' }}</td>
                                <td>{{ $revenue->methode_paiement ?? '—' }}</td>
                                <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($revenue->montant) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="4">@if($report['revenues_all']->count() > 20) … ({{ $report['revenues_all']->count() }} recettes) — @endif TOTAL GÉNÉRAL</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- Signataires --}}
        <div class="signatures" style="margin-top: 30px; page-break-inside: avoid;">
            <div class="section-title">Signatures</div>
            <table style="width: 100%; border: none; margin-top: 15px;">
                <tr>
                    <td style="width: 33%; text-align: center; border: none; padding: 10px;">
                        <div style="border-bottom: 1px solid #333; margin-bottom: 8px; height: 50px;"></div>
                        <strong>Le Curé</strong>
                        <p style="font-size: 9px; color: #666; margin-top: 4px;">Nom et signature</p>
                    </td>
                    <td style="width: 33%; text-align: center; border: none; padding: 10px;">
                        <div style="border-bottom: 1px solid #333; margin-bottom: 8px; height: 50px;"></div>
                        <strong>Le Gestionnaire</strong>
                        <p style="font-size: 9px; color: #666; margin-top: 4px;">Nom et signature</p>
                    </td>
                    <td style="width: 33%; text-align: center; border: none; padding: 10px;">
                        <div style="border-bottom: 1px solid #333; margin-bottom: 8px; height: 50px;"></div>
                        <strong>Le Vicaire Économe</strong>
                        <p style="font-size: 9px; color: #666; margin-top: 4px;">Nom et signature</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Rapport des revenus - Quête ordinaire. Généré le {{ now()->format('d/m/Y à H:i') }}.
        </div>
    </div>

    <script>
        document.querySelector('.btn-print').focus({ preventScroll: true });
    </script>
</body>
</html>
