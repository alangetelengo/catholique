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
                        <th class="px-4 py-3 font-semibold">Documents</th>
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
                                <div class="flex items-center gap-1.5 text-xs">
                                    @if($expense->piece_facture_path)
                                        <a href="{{ Storage::url($expense->piece_facture_path) }}" 
                                           target="_blank" 
                                           title="Facture"
                                           class="inline-flex items-center gap-0.5 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                            </svg>
                                        </a>
                                    @endif
                                    @if($expense->piece_recu_path)
                                        <a href="{{ Storage::url($expense->piece_recu_path) }}" 
                                           target="_blank" 
                                           title="Reçu"
                                           class="inline-flex items-center gap-0.5 text-sky-600 hover:text-sky-700 dark:text-sky-400">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"></path>
                                                <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                    @if($expense->piece_autre_path)
                                        <a href="{{ Storage::url($expense->piece_autre_path) }}" 
                                           target="_blank" 
                                           title="Autre"
                                           class="inline-flex items-center gap-0.5 text-amber-600 hover:text-amber-700 dark:text-amber-400">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </a>
                                    @endif
                                    @if(!$expense->piece_facture_path && !$expense->piece_recu_path && !$expense->piece_autre_path)
                                        <span class="text-slate-400 dark:text-slate-500">—</span>
                                    @endif
                                </div>
                            </td>
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

