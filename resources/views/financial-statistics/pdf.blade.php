<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques financières</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1f2937; margin: 0; }
        .header { background: #0f766e; color: #fff; text-align: center; padding: 12px 10px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 4px 0 0; font-size: 11px; }
        .wrap { padding: 12px 14px 20px; }
        .rule { background: #ecfdf5; border: 1px solid #6ee7b7; padding: 8px 10px; margin-bottom: 12px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; }
        th { background: #0f766e; color: #fff; text-align: left; }
        .right { text-align: right; }
        .kpi { display: table; width: 100%; margin-bottom: 10px; }
        .kpi-row { display: table-row; }
        .kpi-cell { display: table-cell; border: 1px solid #d1d5db; padding: 8px; width: 25%; vertical-align: top; }
        .kpi-label { font-size: 9px; color: #6b7280; text-transform: uppercase; }
        .kpi-val { font-size: 14px; font-weight: bold; margin-top: 4px; }
        h2 { font-size: 13px; color: #0f766e; margin: 14px 0 6px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        .footer { text-align: center; color: #6b7280; margin-top: 16px; font-size: 9px; }
        @page { margin: 16mm; size: A4 portrait; }
    </style>
</head>
<body>
@php
    $fcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' FCFA';
    $c = $snapshot['current'];
    $prev = $snapshot['previous'];
@endphp
    <div class="header">
        <h1>Statistiques financières</h1>
        <p>{{ $snapshot['paroisse']?->nom ?? 'Toutes paroisses' }} — {{ $snapshot['period_label'] }}</p>
    </div>
    <div class="wrap">
        <div class="rule">
            <strong>Règle de solde :</strong> recettes moins dépenses « Alimentation popote » uniquement.
            Charges fixes, variables et exceptionnelles : montants informatifs, non déduits du solde.
        </div>

        <div class="kpi">
            <div class="kpi-row">
                <div class="kpi-cell">
                    <div class="kpi-label">Total recettes</div>
                    <div class="kpi-val">{{ $fcfa($c['total_revenues']) }}</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-label">Popote (déductible)</div>
                    <div class="kpi-val">{{ $fcfa($c['total_expenses_popote']) }}</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-label">Solde</div>
                    <div class="kpi-val">{{ $fcfa($c['solde']) }}</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-label">Toutes dépenses (info)</div>
                    <div class="kpi-val">{{ $fcfa($c['total_expenses_all']) }}</div>
                </div>
            </div>
        </div>

        @if($prev)
            <h2>Période N−1</h2>
            <table>
                <tr><th>Indicateur</th><th class="right">Montant</th></tr>
                <tr><td>Recettes</td><td class="right">{{ $fcfa($prev['total_revenues']) }}</td></tr>
                <tr><td>Dépenses popote</td><td class="right">{{ $fcfa($prev['total_expenses_popote']) }}</td></tr>
                <tr><td>Solde</td><td class="right">{{ $fcfa($prev['solde']) }}</td></tr>
            </table>
        @endif

        <h2>Projection indicative (365 j.)</h2>
        <p>Moyenne journalière recettes × 365 : <strong>{{ $fcfa($snapshot['forecast']['projected_annual_revenue']) }}</strong>
            (sur {{ $snapshot['forecast']['days_in_period'] }} jour(s)).</p>

        <h2>Recettes par catégorie</h2>
        <table>
            <thead><tr><th>Catégorie</th><th class="right">Montant</th></tr></thead>
            <tbody>
            @forelse ($c['revenue_by_category'] as $row)
                <tr><td>{{ $row['label'] }}</td><td class="right">{{ $fcfa($row['total']) }}</td></tr>
            @empty
                <tr><td colspan="2">Aucune recette.</td></tr>
            @endforelse
            </tbody>
        </table>

        <h2>Dépenses par catégorie</h2>
        <table>
            <thead><tr><th>Catégorie</th><th class="right">Montant</th><th>Solde</th></tr></thead>
            <tbody>
            @forelse ($c['expense_by_category'] as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="right">{{ $fcfa($row['total']) }}</td>
                    <td>{{ $row['deductible'] ? 'Déduit' : 'Info' }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Aucune dépense.</td></tr>
            @endforelse
            </tbody>
        </table>

        <h2>Série mensuelle</h2>
        <table>
            <thead>
                <tr>
                    <th>Mois</th>
                    <th class="right">Recettes</th>
                    <th class="right">Popote</th>
                    <th class="right">Autres dép. (info)</th>
                </tr>
            </thead>
            <tbody>
            @foreach (array_keys($c['monthly_revenues']) as $ym)
                <tr>
                    <td>{{ $ym }}</td>
                    <td class="right">{{ $fcfa($c['monthly_revenues'][$ym] ?? 0) }}</td>
                    <td class="right">{{ $fcfa($c['monthly_popote'][$ym] ?? 0) }}</td>
                    <td class="right">{{ $fcfa($c['monthly_other_expenses'][$ym] ?? 0) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="footer">Document généré le {{ now()->format('d/m/Y H:i') }} — Catholique.</div>
    </div>
</body>
</html>
