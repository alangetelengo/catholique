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
        
        // Récupérer les catégories de recettes (sources de fonds pour dépenses)
        $revenueCategories = \App\Models\RevenueCategory::query()
            ->where('actif', 1)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();
            
        // Récupérer les types de recettes
        $revenueTypes = \App\Models\RevenueType::query()
            ->where('actif', 1)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();
            
        // Grouper les types par catégorie pour le filtrage dynamique
        $typesByCategory = $revenueTypes->groupBy('revenue_category_id')->map(function ($types) {
            return $types->pluck('nom', 'id')->toArray();
        })->toArray();
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
            <select name="revenue_category_id" id="expenses_index_category" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                <option value="">Toutes catégories</option>
                @foreach ($revenueCategories as $category)
                    <option value="{{ $category->id }}" {{ request('revenue_category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->nom }}
                    </option>
                @endforeach
            </select>
            <select name="revenue_type_id" id="expenses_index_type" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                <option value="">Tous types</option>
                @foreach ($revenueTypes as $type)
                    <option 
                        value="{{ $type->id }}" 
                        data-category-id="{{ $type->revenue_category_id }}"
                        {{ request('revenue_type_id') == $type->id ? 'selected' : '' }}
                    >
                        {{ $type->nom }}
                    </option>
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

        @if (request()->filled('q') || request()->filled('revenue_category_id') || request()->filled('revenue_type_id') || request()->filled('date_from') || request()->filled('date_to'))
            <div class="mt-4 flex flex-wrap gap-2">
                @if (request('q'))
                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-3 py-1 text-xs font-medium text-slate-700 dark:text-slate-200">Recherche: {{ request('q') }}</span>
                @endif
                @if (request('revenue_category_id'))
                    @php
                        $selectedCategory = $revenueCategories->firstWhere('id', request('revenue_category_id'));
                    @endphp
                    @if ($selectedCategory)
                        <span class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-900/30 px-3 py-1 text-xs font-medium text-rose-700 dark:text-rose-300">Catégorie: {{ $selectedCategory->nom }}</span>
                    @endif
                @endif
                @if (request('revenue_type_id'))
                    @php
                        $selectedType = $revenueTypes->firstWhere('id', request('revenue_type_id'));
                    @endphp
                    @if ($selectedType)
                        <span class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-900/30 px-3 py-1 text-xs font-medium text-rose-700 dark:text-rose-300">Type: {{ $selectedType->nom }}</span>
                    @endif
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
                            <td class="px-4 py-3">
                                {{ $expense->revenueCategory?->nom ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $expense->revenueType?->nom ?? '-' }}
                            </td>
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
                var typesByCategory = @json($typesByCategory);
                var catEl = document.getElementById('expenses_index_category');
                var typeEl = document.getElementById('expenses_index_type');
                if (!catEl || !typeEl) return;

                // Sauvegarder la valeur initiale sélectionnée
                var initialTypeValue = typeEl.value;

                function filterTypes() {
                    var selectedCategoryId = catEl.value;
                    var allOptions = typeEl.querySelectorAll('option[data-category-id]');
                    
                    allOptions.forEach(function(option) {
                        if (!selectedCategoryId) {
                            // Afficher tous les types si aucune catégorie n'est sélectionnée
                            option.style.display = '';
                        } else {
                            // Afficher uniquement les types de la catégorie sélectionnée
                            if (option.getAttribute('data-category-id') === selectedCategoryId) {
                                option.style.display = '';
                            } else {
                                option.style.display = 'none';
                            }
                        }
                    });
                    
                    // Réinitialiser la sélection du type si le type sélectionné n'est plus visible
                    var currentTypeOption = typeEl.querySelector('option[value="' + typeEl.value + '"]');
                    if (currentTypeOption && currentTypeOption.style.display === 'none') {
                        typeEl.value = '';
                    }
                }

                // Appliquer le filtre au chargement
                filterTypes();

                // Écouter les changements de catégorie
                catEl.addEventListener('change', filterTypes);
            })();
        </script>
    @endpush
@endsection

