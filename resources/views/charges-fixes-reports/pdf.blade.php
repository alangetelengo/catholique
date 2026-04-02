<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport charges fixes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; color: #1f2937; }
        .header { background: #0b3d6e; color: #fff; text-align: center; padding: 14px 12px; }
        .header h1 { margin: 0; font-size: 28px; text-transform: uppercase; }
        .header p { margin: 6px 0 0; font-size: 13px; }
        .wrap { padding: 12px 14px 16px; }
        .title { text-align: center; margin-bottom: 12px; }
        .title h2 { margin: 0; color: #0b3d6e; }
        .title p { margin: 4px 0 0; font-size: 12px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; }
        th { background: #0b3d6e; color: #fff; text-align: left; }
        .right { text-align: right; }
        .card { border: 1px solid #d1d5db; background: #fff1f2; padding: 10px; text-align: center; margin-bottom: 8px; }
        .card b { font-size: 26px; color: #be123c; }
        .signataires { margin-top: 30px; display: table; width: 100%; table-layout: fixed; border-spacing: 16px 0; }
        .s { display: table-cell; text-align: center; }
        .line { border-top: 1px solid #6b7280; margin-bottom: 7px; }
        .t { font-weight: 700; font-size: 14px; }
        .n { font-size: 12px; color: #6b7280; }
        .footer { text-align: center; color: #6b7280; margin-top: 16px; font-size: 12px; }
    </style>
</head>
<body>
@php
    $fcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' FCFA';
    $period = ($details['period_kind'] ?? 'mensuel') === 'annuel'
        ? 'Année ' . ($details['year'] ?? optional($report->date_debut)->format('Y'))
        : sprintf('%02d/%s', (int) ($details['month'] ?? optional($report->date_debut)->format('m')), $details['year'] ?? optional($report->date_debut)->format('Y'));
@endphp
    <div class="header">
        <h1>{{ $paroisse?->nom ?? 'PAROISSE' }}</h1>
        <p>{{ $paroisse?->adresse ?? 'Adresse non renseignée' }}@if(!empty($paroisse?->ville)), {{ $paroisse->ville }}@endif</p>
    </div>
    <div class="wrap">
        <div class="title">
            <h2>Rapport des charges fixes</h2>
            <p>Période : {{ $period }} — Généré le {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <div class="card">TOTAL CHARGES FIXES<br><b>{{ $fcfa((float) $report->total_depenses) }}</b></div>

        <table>
            <thead><tr><th>Date</th><th>Type</th><th>Fournisseur</th><th>Référence</th><th class="right">Montant</th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($row['type'] ?? '-')) }}</td>
                    <td>{{ $row['fournisseur'] ?? '-' }}</td>
                    <td>{{ $row['reference'] ?? '-' }}</td>
                    <td class="right">{{ $fcfa((float) ($row['montant'] ?? 0)) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">Aucune charge fixe sur la période.</td></tr>
            @endforelse
                <tr><td colspan="4"><b>TOTAL</b></td><td class="right"><b>{{ $fcfa((float) $report->total_depenses) }}</b></td></tr>
            </tbody>
        </table>

        <div class="signataires">
            <div class="s"><div class="line"></div><div class="t">Le Curé</div><div class="n">Nom et signature</div></div>
            <div class="s"><div class="line"></div><div class="t">Le Gestionnaire</div><div class="n">Nom et signature</div></div>
            <div class="s"><div class="line"></div><div class="t">Le Vicaire Économe</div><div class="n">Nom et signature</div></div>
        </div>
        <div class="footer">Rapport des charges fixes (non déduit des recettes).</div>
    </div>
</body>
</html>

