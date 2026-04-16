@php
    $signatairesBlocks = $signataires ?? \App\Support\FinancialReportSignatories::defaultPdfBlocks();
@endphp
@if (is_array($signatairesBlocks) && count($signatairesBlocks) > 0)
    <div class="mt-10 pt-6 border-t border-slate-200/90 dark:border-slate-600/80 print:mt-8">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-4">Signatures</p>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 sm:gap-4 text-center">
            @foreach ($signatairesBlocks as $signataire)
                <div class="px-2">
                    <div class="border-t border-slate-400 dark:border-slate-500 pt-2 mb-2 mx-auto max-w-[200px]"></div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $signataire['titre'] ?? '' }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $signataire['nom'] ?? '' }}</div>
                </div>
            @endforeach
        </div>
    </div>
@endif
