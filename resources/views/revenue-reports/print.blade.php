<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de recettes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; color: #1f2937; }
        .header {
            background: #0b3d6e;
            color: #fff;
            text-align: center;
            padding: 14px 18px 12px;
            border-bottom: 3px solid #0a2c4d;
        }
        .header h1 { margin: 0; font-size: 28px; letter-spacing: .5px; text-transform: uppercase; }
        .header p { margin: 6px 0 0; font-size: 14px; opacity: .95; }
        .container { padding: 18px 24px; }
        .meta { margin-bottom: 14px; font-size: 14px; }
        .meta b { color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 8px 10px; font-size: 13px; }
        th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
        .total { font-weight: 700; background: #ecfdf5; }
        .signataires { margin-top: 46px; display: flex; gap: 22px; }
        .bloc { flex: 1; text-align: center; }
        .ligne { border-top: 1px solid #6b7280; margin-bottom: 8px; }
        .titre { font-weight: 700; font-size: 14px; }
        .nom { font-size: 12px; color: #4b5563; margin-top: 4px; }
        .footer { text-align: center; margin-top: 24px; color: #6b7280; font-size: 12px; }
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $paroisse?->nom ?? 'SAINT ESPRIT DE MOUNGALI' }}</h1>
        <p>
            {{ $paroisse?->adresse ?? 'Avenue de la Paix, Moungali, Brazzaville' }}
            @if(!empty($paroisse?->ville)) , {{ $paroisse->ville }} @endif
        </p>
    </div>

    <div class="container">
        <div class="no-print" style="margin-bottom:14px;">
            <button onclick="window.print()">Imprimer</button>
        </div>

        @php
            $periodLabel = ($details['period_kind'] ?? 'mensuel') === 'annuel'
                ? 'Année ' . ($details['year'] ?? optional($report->date_debut)->format('Y'))
                : sprintf('%02d/%s', (int) ($details['month'] ?? optional($report->date_debut)->format('m')), $details['year'] ?? optional($report->date_debut)->format('Y'));
            $targetLabel = ($details['report_target'] ?? 'global') === 'categorie' ? 'Par catégorie' : 'Global';
        @endphp

        <div class="meta">
            <div><b>Type de rapport :</b> {{ $targetLabel }}</div>
            <div><b>Période :</b> {{ $periodLabel }}</div>
            @if(!empty($details['revenue_category_nom']))
                <div><b>Catégorie :</b> {{ $details['revenue_category_nom'] }}</div>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Catégorie</th>
                    <th>Type</th>
                    <th class="right">Montant</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                        <td>{{ $row['category'] ?? '-' }}</td>
                        <td>{{ $row['type'] ?? '-' }}</td>
                        <td class="right">{{ number_format((float) ($row['montant'] ?? 0), 0, ',', ' ') }} fcfa</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;">Aucune recette sur la période.</td></tr>
                @endforelse
                <tr class="total">
                    <td colspan="3">TOTAL RECETTES</td>
                    <td class="right">{{ number_format((float) $report->total_recettes, 0, ',', ' ') }} fcfa</td>
                </tr>
            </tbody>
        </table>

        <div class="signataires">
            @foreach($signataires as $signataire)
                <div class="bloc">
                    <div class="ligne"></div>
                    <div class="titre">{{ $signataire['titre'] }}</div>
                    <div class="nom">{{ $signataire['nom'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="footer">
            Rapport des revenus - Généré le {{ now()->format('d/m/Y à H:i') }}.
        </div>
    </div>
</body>
</html>

