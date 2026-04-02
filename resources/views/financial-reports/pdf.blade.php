<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport financier - {{ $financialReport->date_debut->format('F Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
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
        .header-content {
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: {{ $headerConfig['show_logo'] ? '20%' : '0%' }};
        }
        .header-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            width: {{ $headerConfig['show_logo'] ? '60%' : '100%' }};
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: {{ $headerConfig['show_logo'] ? '20%' : '0%' }};
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
        .summary {
            margin: 20px 0;
        }
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-box {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            text-align: center;
            vertical-align: top;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .summary-box.success {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .summary-box.danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .summary-box.info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
        .summary-box.warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
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
            margin: 25px 0;
            page-break-inside: avoid;
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
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table .text-right {
            text-align: right;
        }
        table .text-center {
            text-align: center;
        }
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
        .details-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .details-col {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }
        @page {
            margin: 20mm;
        }
    </style>
</head>
<body>
    {{-- En-tête paramétrable --}}
    <div class="header">
        <div class="header-content">
            @if($headerConfig['show_logo'] && $headerConfig['logo_path'])
                <div class="header-left">
                    @php
                        $logoPath = $headerConfig['logo_path'];
                        $logoBase64 = null;

                        // Si c'est un chemin relatif, le convertir en chemin absolu
                        if (!str_starts_with($logoPath, 'http') && !str_starts_with($logoPath, 'data:')) {
                            if (str_starts_with($logoPath, '/')) {
                                $logoPath = public_path($logoPath);
                            } else {
                                $logoPath = public_path('/' . ltrim($logoPath, '/'));
                            }

                            // Vérifier si le fichier existe
                            if (file_exists($logoPath) && is_file($logoPath)) {
                                try {
                                    $imageData = base64_encode(file_get_contents($logoPath));
                                    $imageInfo = @getimagesize($logoPath);
                                    if ($imageInfo !== false) {
                                        $mimeType = $imageInfo['mime'];
                                        $logoBase64 = 'data:' . $mimeType . ';base64,' . $imageData;
                                    }
                                } catch (\Exception $e) {
                                    // Ignorer les erreurs de lecture d'image
                                }
                            }
                        } elseif (str_starts_with($logoPath, 'data:')) {
                            // Déjà en base64
                            $logoBase64 = $logoPath;
                        }
                    @endphp
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: {{ $headerConfig['logo_width'] ?? '80' }}px;">
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
                @if($headerConfig['phone'])
                    <p>Tél: {{ $headerConfig['phone'] }}</p>
                @elseif($paroisse && $paroisse->telephone)
                    <p>Tél: {{ $paroisse->telephone }}</p>
                @endif
                @if($headerConfig['email'])
                    <p>Email: {{ $headerConfig['email'] }}</p>
                @elseif($paroisse && $paroisse->email)
                    <p>Email: {{ $paroisse->email }}</p>
                @endif
                @if($headerConfig['custom_text'])
                    <p style="margin-top: 10px; font-style: italic;">{{ $headerConfig['custom_text'] }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Titre du rapport --}}
    <div class="report-title">
        <h3>Rapport financier mensuel</h3>
        <p>
            Période : {{ $financialReport->date_debut->format('d/m/Y') }} au {{ $financialReport->date_fin->format('d/m/Y') }}
            <br>
            Généré le : {{ $financialReport->created_at->format('d/m/Y à H:i') }}
            @if($financialReport->createdBy)
                par {{ $financialReport->createdBy->name }}
            @endif
        </p>
    </div>

    {{-- Résumé --}}
    <div class="summary">
        <div class="summary-row">
            <div class="summary-box success">
                <h4>Total Recettes</h4>
                <div class="amount">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_recettes']) }}</div>
                <div class="label">Popote / Subvention</div>
            </div>
            <div class="summary-box danger">
                <h4>Total Dépenses</h4>
                <div class="amount">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_depenses']) }}</div>
                <div class="label">Toutes catégories</div>
            </div>
            <div class="summary-box {{ $report['solde'] >= 0 ? 'info' : 'warning' }}">
                <h4>Solde</h4>
                <div class="amount">{{ \App\Helpers\ParoisseConfig::formatMontant($report['solde']) }}</div>
                <div class="label">{{ $report['solde'] >= 0 ? 'Excédent' : 'Déficit' }}</div>
            </div>
        </div>
    </div>

    {{-- Détails des recettes et dépenses --}}
    <div class="details-grid">
        <div class="details-col">
            <div class="section">
                <div class="section-title">Détails des Recettes (Popote/Subvention)</div>
                @if(count($report['details_recettes']) > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th class="text-right">Montant</th>
                                <th class="text-center">Nb</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['details_recettes'] as $detail)
                                <tr>
                                    <td>{{ $detail['nom'] }}</td>
                                    <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($detail['montant']) }}</td>
                                    <td class="text-center">{{ $detail['count'] }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td>TOTAL</td>
                                <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_recettes']) }}</td>
                                <td class="text-center">{{ $report['revenues']->count() }}</td>
                            </tr>
                        </tbody>
                    </table>
                @else
                    <p style="color: #666; font-style: italic;">Aucune recette popote/subvention pour cette période.</p>
                @endif
            </div>
        </div>
        <div class="details-col">
            <div class="section">
                <div class="section-title">Détails des Dépenses par Catégorie</div>
                <table>
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th class="text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Charges fixes</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_depenses']['charge_fixe']) }}</td>
                        </tr>
                        <tr>
                            <td>Charges variables</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_depenses']['charge_variable']) }}</td>
                        </tr>
                        <tr>
                            <td>Charges exceptionnelles</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['details_depenses']['charge_exceptionnelle']) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>TOTAL</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($report['total_depenses']) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Liste détaillée des recettes --}}
    @if($report['revenues']->count() > 0)
        <div class="section">
            <div class="section-title">Liste détaillée des Recettes</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Méthode</th>
                        <th>Référence</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['revenues'] as $revenue)
                        <tr>
                            <td>{{ $revenue->date_recette?->format('d/m/Y') }}</td>
                            <td>{{ $revenue->type->nom ?? '—' }}</td>
                            <td>{{ $revenue->methode_paiement ?? '—' }}</td>
                            <td>{{ $revenue->reference_paiement ?? '—' }}</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($revenue->montant) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Liste détaillée des dépenses --}}
    @if($report['expenses']->count() > 0)
        <div class="section">
            <div class="section-title">Liste détaillée des Dépenses</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Catégorie</th>
                        <th>Type</th>
                        <th>Fournisseur</th>
                        <th>Réf. Facture</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['expenses'] as $expense)
                        <tr>
                            <td>{{ $expense->date_depense?->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $cats = [
                                        'charge_fixe' => 'Charge fixe',
                                        'charge_variable' => 'Charge variable',
                                        'charge_exceptionnelle' => 'Charge exceptionnelle',
                                    ];
                                @endphp
                                {{ $cats[$expense->categorie_charge] ?? $expense->categorie_charge }}
                            </td>
                            <td>
                                @php
                                    $types = [
                                        'carburant' => 'Carburant',
                                        'hosties' => 'Hosties',
                                        'internet' => 'Internet',
                                        'maintenance_materiel' => 'Maintenance matériel',
                                        'gaz' => 'Gaz',
                                        'eau' => 'Eau',
                                        'electricite' => 'Électricité',
                                        'jardinage' => 'Jardinage',
                                        'salaire_ouvrier' => 'Salaire ouvrier',
                                        'autre' => 'Autre',
                                    ];
                                @endphp
                                {{ $types[$expense->type_charge] ?? $expense->type_charge }}
                            </td>
                            <td>{{ $expense->fournisseur ?? '—' }}</td>
                            <td>{{ $expense->facture_reference ?? '—' }}</td>
                            <td class="text-right">{{ \App\Helpers\ParoisseConfig::formatMontant($expense->montant) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Pied de page --}}
    <div class="footer">
        <p><strong>Note :</strong> Ce rapport justifie les dépenses effectuées contre les recettes popote/subvention reçues pour la période indiquée.</p>
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
