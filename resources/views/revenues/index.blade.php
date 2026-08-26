@extends('layouts.app')

@section('title', 'Revenus - Catholique')
@section('page-title', 'Revenus')
@section('page-title-info', 'Gestion des recettes de la paroisse avec filtres par période, catégorie et type.')

@section('btn-create')
    <a href="{{ route('revenues.create') }}" class="adventiste-btn-primary">+ Nouvelle recette</a>
@endsection

@section('content')
    @php
        $formatFcfa = static fn (float $value): string => number_format($value, 0, ',', ' ') . ' fcfa';
        $filterCategoryNom = request('categorie')
            ? $categories->firstWhere('code', request('categorie'))?->nom
            : null;
        $filterTypeNom = null;
        if (request('type')) {
            foreach ($categories as $cat) {
                $matchType = $cat->types->firstWhere('code', request('type'));
                if ($matchType) {
                    $filterTypeNom = $matchType->nom;
                    break;
                }
            }
        }
        $hasActiveFilters = request()->filled('q')
            || request()->filled('categorie')
            || request()->filled('type')
            || request()->filled('date_from')
            || request()->filled('date_to');
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total de recette</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $formatFcfa($totalMontantRecettes) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-rose-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total de dépensé</p>
            <p class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-400">{{ $formatFcfa($totalMontantDepenses) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 {{ $soldeRestant >= 0 ? 'border-t-sky-500' : 'border-t-amber-500' }}">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Solde restant</p>
            <p class="mt-1 text-2xl font-bold {{ $soldeRestant >= 0 ? 'text-sky-800 dark:text-sky-300' : 'text-amber-700 dark:text-amber-400' }}">{{ $formatFcfa($soldeRestant) }}</p>
        </div>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <div class="mb-4 flex flex-col gap-1 border-b border-slate-200/80 pb-3 dark:border-slate-600/60 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white m-0">Filtres</h2>
            @if ($hasActiveFilters)
                <p class="text-xs text-slate-500 dark:text-slate-400 m-0">{{ $revenues->total() }} recette(s) correspondant aux filtres</p>
            @endif
        </div>
        <form method="get" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 lg:items-end">
            <div class="lg:col-span-2">
                <label for="filter_q" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Recherche</label>
                <input
                    id="filter_q"
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Notes, référence, donateur…"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm"
                >
            </div>
            <div>
                <label for="filter_categorie" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Catégorie</label>
                <select
                    id="filter_categorie"
                    name="categorie"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm"
                >
                    <option value="">Toutes les catégories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->code }}" {{ request('categorie') === $category->code ? 'selected' : '' }}>{{ $category->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_type" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Type</label>
                <select
                    id="filter_type"
                    name="type"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm"
                >
                    <option value="">Tous les types</option>
                    @foreach ($categories as $category)
                        @if ($category->types->isNotEmpty())
                            <optgroup label="{{ $category->nom }}">
                                @foreach ($category->types as $type)
                                    <option
                                        value="{{ $type->code }}"
                                        data-category-code="{{ $category->code }}"
                                        {{ request('type') === $type->code ? 'selected' : '' }}
                                    >{{ $type->nom }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date début</label>
                <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label for="date_to" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date fin</label>
                <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:col-span-2 lg:col-span-6">
                <button class="adventiste-btn-primary" type="submit">Filtrer</button>
                <a href="{{ route('revenues.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            </div>
        </form>
        @if ($hasActiveFilters)
            <div class="mt-5 rounded-xl border border-slate-200/90 bg-slate-50/80 px-3 py-3 dark:border-slate-600/60 dark:bg-slate-800/40">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Filtres actifs</p>
                <div class="flex flex-wrap gap-2">
                    @if (request('q'))
                        <a
                            href="{{ route('revenues.index', request()->except(['q', 'page'])) }}"
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700/80"
                            title="Retirer ce filtre"
                        >
                            <span>Recherche : <strong class="font-semibold">{{ request('q') }}</strong></span>
                            <span class="text-slate-400 dark:text-slate-500" aria-hidden="true">×</span>
                        </a>
                    @endif
                    @if (request('categorie'))
                        <a
                            href="{{ route('revenues.index', request()->except(['categorie', 'page'])) }}"
                            class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50/90 px-2.5 py-1 text-xs font-medium text-emerald-800 shadow-sm transition hover:bg-emerald-100 dark:border-emerald-800/50 dark:bg-emerald-950/50 dark:text-emerald-200 dark:hover:bg-emerald-900/40"
                            title="Retirer ce filtre"
                        >
                            <span>Catégorie : <strong class="font-semibold">{{ $filterCategoryNom ?? request('categorie') }}</strong></span>
                            <span class="text-emerald-600/80 dark:text-emerald-400/80" aria-hidden="true">×</span>
                        </a>
                    @endif
                    @if (request('type'))
                        <a
                            href="{{ route('revenues.index', request()->except(['type', 'page'])) }}"
                            class="inline-flex items-center gap-1.5 rounded-full border border-violet-200 bg-violet-50/90 px-2.5 py-1 text-xs font-medium text-violet-800 shadow-sm transition hover:bg-violet-100 dark:border-violet-800/50 dark:bg-violet-950/40 dark:text-violet-200 dark:hover:bg-violet-900/40"
                            title="Retirer ce filtre"
                        >
                            <span>Type : <strong class="font-semibold">{{ $filterTypeNom ?? request('type') }}</strong></span>
                            <span class="text-violet-600/80 dark:text-violet-400/80" aria-hidden="true">×</span>
                        </a>
                    @endif
                    @if (request('date_from'))
                        <a
                            href="{{ route('revenues.index', request()->except(['date_from', 'page'])) }}"
                            class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-sky-50/90 px-2.5 py-1 text-xs font-medium text-sky-800 shadow-sm transition hover:bg-sky-100 dark:border-sky-800/50 dark:bg-sky-950/40 dark:text-sky-200 dark:hover:bg-sky-900/40"
                            title="Retirer ce filtre"
                        >
                            <span>À partir du <strong class="font-semibold">{{ \Illuminate\Support\Carbon::parse(request('date_from'))->format('d/m/Y') }}</strong></span>
                            <span class="text-sky-600/80 dark:text-sky-400/80" aria-hidden="true">×</span>
                        </a>
                    @endif
                    @if (request('date_to'))
                        <a
                            href="{{ route('revenues.index', request()->except(['date_to', 'page'])) }}"
                            class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-sky-50/90 px-2.5 py-1 text-xs font-medium text-sky-800 shadow-sm transition hover:bg-sky-100 dark:border-sky-800/50 dark:bg-sky-950/40 dark:text-sky-200 dark:hover:bg-sky-900/40"
                            title="Retirer ce filtre"
                        >
                            <span>Jusqu’au <strong class="font-semibold">{{ \Illuminate\Support\Carbon::parse(request('date_to'))->format('d/m/Y') }}</strong></span>
                            <span class="text-sky-600/80 dark:text-sky-400/80" aria-hidden="true">×</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            (function () {
                const cat = document.getElementById('filter_categorie');
                const typ = document.getElementById('filter_type');
                if (!cat || !typ) return;

                function syncTypeOptionsVisibility() {
                    const code = cat.value;
                    const selectedType = typ.value;
                    let selectedStillVisible = !selectedType;

                    typ.querySelectorAll('option[value]').forEach(function (opt) {
                        const oc = opt.getAttribute('data-category-code');
                        if (!oc) return;
                        const visible = !code || oc === code;
                        opt.hidden = !visible;
                        opt.disabled = !visible;
                        if (visible && opt.value === selectedType) {
                            selectedStillVisible = true;
                        }
                    });

                    typ.querySelectorAll('optgroup').forEach(function (og) {
                        const hasVisible = Array.from(og.querySelectorAll('option')).some(function (o) {
                            return !o.hidden && o.value;
                        });
                        og.hidden = !hasVisible;
                    });

                    if (!selectedStillVisible) {
                        typ.value = '';
                    }
                }

                cat.addEventListener('change', syncTypeOptionsVisibility);
                syncTypeOptionsVisibility();
            })();
        </script>
    @endpush

    <div class="adventiste-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200">
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Catégorie</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Mois concerné</th>
                        <th class="px-4 py-3 font-semibold">Montant</th>
                        <th class="px-4 py-3 font-semibold">Paiement</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse ($revenues as $revenue)
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-4 py-3">{{ optional($revenue->date_recette)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $revenue->category?->nom ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $revenue->type?->nom ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($revenue->mois_capital)
                                    {{ \App\Support\SubventionMensuelle::formatMoisCapital($revenue->mois_capital) }}
                                @elseif ($revenue->mois_location)
                                    {{ \App\Support\SubventionMensuelle::formatMoisCapital($revenue->mois_location) }}
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ $formatFcfa((float) $revenue->montant) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                                    {{ [
                                        'especes' => 'Espèces',
                                        'cheque' => 'Chèque',
                                        'virement' => 'Virement',
                                        'carte' => 'Carte',
                                        'mobile_money' => 'Mobile money',
                                    ][$revenue->methode_paiement] ?? ucfirst(str_replace('_', ' ', (string) $revenue->methode_paiement)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-action-button
                                        variant="edit"
                                        href="{{ route('revenues.edit', $revenue) }}"
                                        custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30"
                                    />
                                    <x-action-button
                                        variant="delete"
                                        action="{{ route('revenues.destroy', $revenue) }}"
                                        method="DELETE"
                                        confirm-message="Supprimer cette recette ?"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Aucune recette trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        @include('partials.pagination-fr', ['paginator' => $revenues, 'itemLabel' => 'recettes'])
    </div>
@endsection
