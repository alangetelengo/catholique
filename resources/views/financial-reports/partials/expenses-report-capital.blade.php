@php
    $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
@endphp

@if ($capitalReport)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Capital Banque reçu</p>
            <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ $fmt($capitalReport['total_capital']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-sky-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Alloué aux caisses</p>
            <p class="mt-1 text-xl font-bold text-sky-700 dark:text-sky-300 tabular-nums">{{ $fmt($capitalReport['total_virements']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Dépensé</p>
            <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($capitalReport['total_depenses']) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-amber-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Reste alloué</p>
            <p class="mt-1 text-xl font-bold text-amber-700 dark:text-amber-400 tabular-nums">{{ $fmt($capitalReport['reste_alloue']) }}</p>
        </div>
    </div>

    @include('financial-reports.partials.expenses-report-capital-tables', ['report' => $capitalReport])
@else
    <div class="adventiste-card-pro-static p-10 text-center text-slate-500 dark:text-slate-400">
        Sélectionnez une paroisse et une période, puis cliquez sur <strong class="font-medium">Calculer</strong>.
    </div>
@endif
