@php
    $fmt = static fn ($n) => \App\Helpers\ParoisseConfig::formatMontant($n);
    $fundingSourceLabel = static function ($source): string {
        return $source->caisse?->nom ?? '—';
    };
@endphp

@if ($report['expenses']->count() > 0)
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">
            Détail des opérations
            <span class="font-normal text-slate-500 dark:text-slate-400">({{ $report['expenses']->count() }})</span>
        </h3>

        <div class="space-y-3">
            @foreach ($report['expenses'] as $ex)
                <div class="adventiste-card-pro-static p-4">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 whitespace-nowrap">
                            {{ $ex->date_depense?->format('d/m/Y') }}
                        </span>
                        <span class="text-base font-bold text-rose-700 dark:text-rose-400 tabular-nums whitespace-nowrap">
                            {{ $fmt((float) $ex->montant) }}
                        </span>
                    </div>
                    @if ($ex->fundingSources->isNotEmpty())
                        @foreach ($ex->fundingSources as $source)
                            <p class="text-xs text-emerald-700 dark:text-emerald-400 mb-0.5">{{ $fundingSourceLabel($source) }}</p>
                        @endforeach
                    @endif
                    @if ($ex->expenseType)
                        <p class="text-xs text-amber-700 dark:text-amber-400 mb-0.5">{{ $ex->expenseType->nom }}</p>
                    @endif
                    <p class="text-sm text-slate-700 dark:text-slate-200 mt-1">{{ $ex->libelle ?: '—' }}</p>
                    @if ($ex->fournisseur)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Fournisseur : {{ $ex->fournisseur }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="adventiste-card-pro-static p-4 mt-4 bg-slate-50/90 dark:bg-slate-800/50">
            <div class="flex items-center justify-between font-bold text-slate-900 dark:text-white">
                <span>Total des dépenses</span>
                <span class="text-rose-700 dark:text-rose-400 tabular-nums">{{ $fmt($report['total_general']) }}</span>
            </div>
        </div>
    </div>
@else
    <div class="adventiste-card-pro-static p-8 text-center text-sm text-slate-500 dark:text-slate-400 mb-6">
        Aucune dépense validée pour ces critères.
    </div>
@endif
