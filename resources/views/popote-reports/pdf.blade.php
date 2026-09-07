<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Caisse Popote</title>
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
        .cards { display: table; width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 8px; }
        .card { display: table-cell; border: 1px solid #cbd5e1; padding: 10px; text-align: center; }
        .c1 { background: #dcfce7; } .c2 { background: #fee2e2; } .c3 { background: #e0f2fe; }
        .label { font-size: 12px; font-weight: 700; } .value { font-size: 24px; font-weight: 800; margin-top: 5px; }
        .signataires { margin-top: 30px; display: table; width: 100%; table-layout: fixed; border-spacing: 16px 0; }
        .s { display: table-cell; text-align: center; } .line { border-top: 1px solid #6b7280; margin-bottom: 7px; }
        .t { font-weight: 700; font-size: 14px; } .n { font-size: 12px; color: #6b7280; }
        @page { margin: 16mm; size: A4 portrait; }
    </style>
</head>
<body>
@php
    $fcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' FCFA';
    $period = ($detailsRecettes['period_kind'] ?? 'mensuel') === 'annuel'
        ? 'Année ' . ($detailsRecettes['year'] ?? optional($report->date_debut)->format('Y'))
        : sprintf('%02d/%s', (int) ($detailsRecettes['month'] ?? optional($report->date_debut)->format('m')), $detailsRecettes['year'] ?? optional($report->date_debut)->format('Y'));
@endphp
    <div class="header">
        <h1>{{ $paroisse?->nom ?? 'PAROISSE' }}</h1>
        <p>{{ $paroisse?->adresse ?? 'Adresse non renseignée' }}@if(!empty($paroisse?->ville)), {{ $paroisse->ville }}@endif</p>
    </div>
    <div class="wrap">
        <div class="title">
            <h2>Rapport Caisse Popote</h2>
            <p>Période : {{ $period }} — Généré le {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <div class="cards">
            <div class="card c1"><div class="label">Crédits caisse</div><div class="value">{{ $fcfa((float) $report->total_recettes) }}</div></div>
            <div class="card c2"><div class="label">Dépenses alimentation</div><div class="value">{{ $fcfa((float) $report->total_depenses) }}</div></div>
            <div class="card c3"><div class="label">Solde popote</div><div class="value">{{ $fcfa((float) $report->solde) }}</div></div>
        </div>

        <table>
            <thead><tr><th>Date recette</th><th>Référence</th><th class="right">Montant subvention</th></tr></thead>
            <tbody>
            @forelse($rowsRecettes as $row)
                <tr><td>{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td><td>{{ $row['reference'] ?? '-' }}</td><td class="right">{{ $fcfa((float) ($row['montant'] ?? 0)) }}</td></tr>
            @empty
                <tr><td colspan="3" style="text-align:center;">Aucune subvention reçue.</td></tr>
            @endforelse
            </tbody>
        </table>

        <table>
            <thead><tr><th>Date dépense</th><th>Libellé</th><th>Fournisseur</th><th>Référence</th><th class="right">Montant dépense</th></tr></thead>
            <tbody>
            @forelse($rowsDepenses as $row)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                    <td>{{ $row['libelle'] ?? '-' }}</td>
                    <td>{{ $row['fournisseur'] ?? '-' }}</td>
                    <td>{{ $row['reference'] ?? '-' }}</td>
                    <td class="right">{{ $fcfa((float) ($row['montant'] ?? 0)) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">Aucune dépense alimentation.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="signataires">
            <div class="s"><div class="line"></div><div class="t">Le Curé</div><div class="n">Nom et signature</div></div>
            <div class="s"><div class="line"></div><div class="t">Le Gestionnaire</div><div class="n">Nom et signature</div></div>
            <div class="s"><div class="line"></div><div class="t">Le Vicaire Économe</div><div class="n">Nom et signature</div></div>
        </div>
    </div>
</body>
</html>

