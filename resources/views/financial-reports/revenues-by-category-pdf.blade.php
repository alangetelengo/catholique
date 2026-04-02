<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport par catégories de recettes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; color: #333; line-height: 1.3; }
        .header {
            background-color: {{ $headerConfig['header_bg_color'] ?? '#6A1B9A' }};
            color: {{ $headerConfig['header_text_color'] ?? '#FFFFFF' }};
            padding: 8px 12px;
            margin-bottom: 8px;
        }
        .header h1 { font-size: 12px; font-weight: bold; }
        .header p { font-size: 8px; margin: 2px 0 0; }
        .report-title {
            text-align: center;
            margin: 8px 0;
            padding: 8px;
            background: #f5f5f5;
            border-left: 4px solid {{ $headerConfig['header_bg_color'] ?? '#6A1B9A' }};
        }
        .report-title h3 { font-size: 11px; color: #333; }
        .report-title p { font-size: 8px; color: #666; margin-top: 4px; }
        .summary { margin: 8px 0; }
        .summary-row { display: table; width: 100%; }
        .summary-box {
            display: table-cell;
            padding: 8px 12px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .summary-box.primary { background: rgba(106, 27, 154, 0.1); }
        .summary-box h4 { font-size: 8px; margin-bottom: 4px; }
        .summary-box .amount { font-size: 12px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 10px; }
        table th {
            background: {{ $headerConfig['header_bg_color'] ?? '#6A1B9A' }};
            color: #fff;
            padding: 6px 8px;
            text-align: left;
        }
        table td { padding: 4px 8px; border: 1px solid #ddd; }
        table tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background: #f0f0f0 !important; }
        .section-title { font-size: 10px; font-weight: bold; margin: 12px 0 6px; padding-bottom: 4px; border-bottom: 1px solid #999; }
        .footer { margin-top: 12px; padding-top: 6px; border-top: 1px solid #ddd; font-size: 7px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        @if($headerConfig['title'] ?? null)
            <h1>{{ $headerConfig['title'] }}</h1>
        @elseif($paroisse)
            <h1>{{ $paroisse->nom }}</h1>
        @endif
        @if($headerConfig['subtitle'] ?? null)
            <p>{{ $headerConfig['subtitle'] }}</p>
        @endif
    </div>

    <div class="report-title">
        <h3>Rapport par catégories de recettes</h3>
        <p>Période : {{ $dateDebut->format('d/m/Y') }} au {{ $dateFin->format('d/m/Y') }} — Généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-box primary" style="width: 100%;">
                <h4>Total recettes</h4>
                <div class="amount">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</div>
                <div>{{ $report['revenues']->count() }} recette(s) — {{ count($report['by_category']) }} catégorie(s)</div>
            </div>
        </div>
    </div>

    @if(count($report['by_category']) > 0)
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
            @foreach($report['by_category'] as $cat)
            <tr>
                <td>{{ $cat['nom'] }}</td>
                <td class="text-center">{{ $cat['count'] }}</td>
                <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($cat['montant']) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-center">{{ $report['revenues']->count() }}</td>
                <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="section-title">Liste des recettes</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Catégorie</th>
                <th>Type</th>
                <th>Méthode</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['revenues'] as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->date_recette?->format('d/m/Y') }}</td>
                <td>{{ $r->category?->nom ?? '—' }}</td>
                <td>{{ $r->type?->nom ?? '—' }}</td>
                <td>{{ $r->methode_paiement ?? '—' }}</td>
                <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($r->montant) }}</td>
            </tr>
            @endforeach
        </tbody>
        @if($report['revenues']->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL</td>
                <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_general']) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Rapport par catégories de recettes — {{ $paroisse->nom ?? '' }} — Généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
