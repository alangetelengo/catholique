@extends('layouts.app')

@section('title', 'Dépenses - Catholique')
@section('page-title', 'Dépenses')
@section('page-title-info', 'Gestion des charges de la paroisse avec filtres par période, catégorie et type.')

@section('btn-create')
    <a href="{{ route('expenses.create') }}" class="adventiste-btn-primary">+ Nouvelle dépense</a>
@endsection

@section('content')
    @php
        $formatFcfa = static fn (float $value): string => number_format($value, 0, ',', ' ') . ' fcfa';
        $categories = [
            'charge_fixe' => 'Charge fixe',
            'charge_variable' => 'Charge variable',
            'charge_exceptionnelle' => 'Charge exceptionnelle',
            'alimentation_popote' => 'Alimentation popote',
        ];
        $types = trans('expenses.types');
        $types = is_array($types) ? $types : [];
        $typesByCategoryFilter = \App\Support\ExpenseChargeCatalog::labeledOptionsByCategoryExcludingPopote();
        $typesByCategoryFilter['alimentation_popote'] = \App\Support\ExpenseChargeCatalog::labeledOptionsForCategory('alimentation_popote');
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total des dépenses</p>
            <p class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-400">{{ $formatFcfa($totalMontantDepenses) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Dernière dépense</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">
                @if ($montantDerniereDepense !== null)
                    {{ $formatFcfa($montantDerniereDepense) }}
                @else
                    <span class="text-slate-400 dark:text-slate-500">—</span>
                @endif
            </p>
        </div>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Recherche (notes, fournisseur, facture)"
                class="md:col-span-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm"
            >
            <select name="categorie_charge" id="expenses_index_categorie_charge" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                <option value="">Toutes catégories</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" {{ request('categorie_charge') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="type_charge" id="expenses_index_type_charge" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                <option value="">Tous types</option>
                @foreach ($types as $key => $label)
                    <option value="{{ $key }}" {{ request('type_charge') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div>
                <label for="date_from" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date début</label>
                <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label for="date_to" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date fin</label>
                <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div class="md:col-span-6 flex items-center gap-2">
                <button class="adventiste-btn-primary" type="submit">Filtrer</button>
                <a href="{{ route('expenses.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            </div>
        </form>

        @if (request()->filled('q') || request()->filled('categorie_charge') || request()->filled('type_charge') || request()->filled('date_from') || request()->filled('date_to'))
            <div class="mt-4 flex flex-wrap gap-2">
                @if (request('q'))
                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-3 py-1 text-xs font-medium text-slate-700 dark:text-slate-200">Recherche: {{ request('q') }}</span>
                @endif
                @if (request('categorie_charge'))
                    <span class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-900/30 px-3 py-1 text-xs font-medium text-rose-700 dark:text-rose-300">Catégorie: {{ $categories[request('categorie_charge')] ?? request('categorie_charge') }}</span>
                @endif
                @if (request('type_charge'))
                    <span class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-900/30 px-3 py-1 text-xs font-medium text-rose-700 dark:text-rose-300">Type: {{ $types[request('type_charge')] ?? request('type_charge') }}</span>
                @endif
                @if (request('date_from'))
                    <span class="inline-flex items-center rounded-full bg-sky-50 dark:bg-sky-900/30 px-3 py-1 text-xs font-medium text-sky-700 dark:text-sky-300">De: {{ request('date_from') }}</span>
                @endif
                @if (request('date_to'))
                    <span class="inline-flex items-center rounded-full bg-sky-50 dark:bg-sky-900/30 px-3 py-1 text-xs font-medium text-sky-700 dark:text-sky-300">À: {{ request('date_to') }}</span>
                @endif
            </div>
        @endif
    </div>

    <div class="adventiste-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200">
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Catégorie</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Montant</th>
                        <th class="px-4 py-3 font-semibold">Paiement</th>
                        <th class="px-4 py-3 font-semibold">Fournisseur</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse ($expenses as $expense)
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-4 py-3">{{ optional($expense->date_depense)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $categories[$expense->categorie_charge] ?? $expense->categorie_charge }}</td>
                            <td class="px-4 py-3">{{ $types[$expense->type_charge] ?? $expense->type_charge }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $formatFcfa((float) $expense->montant) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                                    {{ str_replace('_', ' ', ucfirst($expense->methode_paiement)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $expense->fournisseur ?: ($expense->libelle ?: '-') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-action-button
                                        variant="edit"
                                        href="{{ route('expenses.edit', $expense) }}"
                                        custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30"
                                    />
                                    <x-action-button
                                        variant="delete"
                                        action="{{ route('expenses.destroy', $expense) }}"
                                        method="DELETE"
                                        confirm-message="Supprimer cette dépense ?"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">Aucune dépense trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        @include('partials.pagination-fr', ['paginator' => $expenses, 'itemLabel' => 'dépenses'])
    </div>

    @push('scripts')
        <script>
            (function () {
                var byCat = @json($typesByCategoryFilter);
                var allTypes = @json($types);
                var catEl = document.getElementById('expenses_index_categorie_charge');
                var typeEl = document.getElementById('expenses_index_type_charge');
                if (!catEl || !typeEl) return;

                function fillTypeSelect(allowedMap, preserveValue) {
                    var prev = preserveValue !== undefined && preserveValue !== null ? preserveValue : typeEl.value;
                    typeEl.innerHTML = '';
                    var o0 = document.createElement('option');
                    o0.value = '';
                    o0.textContent = 'Tous types';
                    typeEl.appendChild(o0);
                    var keys = Object.keys(allowedMap || {});
                    keys.sort();
                    keys.forEach(function (k) {
                        var o = document.createElement('option');
                        o.value = k;
                        o.textContent = allowedMap[k];
                        typeEl.appendChild(o);
                    });
                    if (prev && allowedMap && Object.prototype.hasOwnProperty.call(allowedMap, prev)) {
                        typeEl.value = prev;
                    } else {
                        typeEl.value = '';
                    }
                }

                function syncTypeFilterOptions() {
                    var cid = catEl.value;
                    if (!cid) {
                        fillTypeSelect(allTypes, typeEl.getAttribute('data-request-type') || '');
                        return;
                    }
                    fillTypeSelect(byCat[cid] || {}, typeEl.getAttribute('data-request-type') || '');
                }

                typeEl.setAttribute('data-request-type', @json(request('type_charge', '')));
                syncTypeFilterOptions();
                catEl.addEventListener('change', function () {
                    typeEl.removeAttribute('data-request-type');
                    syncTypeFilterOptions();
                });
            })();
        </script>
    @endpush
@endsection

