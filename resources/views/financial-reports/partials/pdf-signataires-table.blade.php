@php
    $signatairesPdf = $signataires ?? \App\Support\FinancialReportSignatories::defaultPdfBlocks();
    $signCount = is_array($signatairesPdf) ? count($signatairesPdf) : 0;
    $colPct = $signCount > 0 ? round(100 / $signCount, 4) : 0;
@endphp
@if ($signCount > 0)
    {{-- Table HTML : Dompdf gère mal display:table / table-cell sur des divs --}}
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:28px;border-collapse:separate;border-spacing:12px 0;">
        <tr>
            @foreach ($signatairesPdf as $signataire)
                <td width="{{ $colPct }}%" style="vertical-align:top;text-align:center;padding:0 4px;">
                    <div style="border-top:1px solid #6b7280;margin-bottom:8px;width:100%;"></div>
                    <div style="font-weight:bold;font-size:12px;color:#111;">{{ $signataire['titre'] ?? '' }}</div>
                    <div style="font-size:10px;color:#555;margin-top:4px;">{{ $signataire['nom'] ?? '' }}</div>
                </td>
            @endforeach
        </tr>
    </table>
@endif
