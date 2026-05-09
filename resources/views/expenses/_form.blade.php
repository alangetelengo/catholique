@php
    $gridColumns = (int) ($formColumns ?? 2);
    $gridClass = $gridColumns === 3 ? 'revenue-form-grid revenue-form-grid--three' : 'revenue-form-grid';
    $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
@endphp

<div class="{{ $gridClass }}">
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Catégorie de recette (source) <span class="text-red-600">*</span></label>
        <select name="revenue_category_id" id="revenue_category" class="{{ $field }}" required>
            <option value="">-- Choisir la source --</option>
            @foreach ($revenueCategories as $category)
                <option value="{{ $category->id }}"
                    {{ (string) old('revenue_category_id', $expense->revenue_category_id) === (string) $category->id ? 'selected' : '' }}>
                    {{ $category->nom }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Catégorie générale de la dépense</p>
        @error('revenue_category_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date de dépense <span class="text-red-600">*</span></label>
        <input type="date" name="date_depense" id="date_depense" value="{{ old('date_depense', optional($expense->date_depense)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="{{ $field }}" required>
        @error('date_depense')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Montant total (FCFA) <span class="text-red-600">*</span></label>
        <input type="text" name="montant" id="montant_total" value="{{ old('montant', $expense->montant) }}" class="{{ $field }} js-montant-fcfa" placeholder="10 000 fcfa" required>
        @error('montant')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Libellé (description de la dépense) <span class="text-red-600">*</span></label>
        <input type="text" name="libelle" id="libelle" value="{{ old('libelle', $expense->libelle) }}" class="{{ $field }}" placeholder="Ex: Achat de riz pour popote" required>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Décrivez précisément l'achat effectué</p>
        @error('libelle')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    {{-- Section Sources de financement --}}
    <div class="revenue-form-grid__full">
        <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-2">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Sources de financement <span class="text-red-600">*</span>
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mb-4">
                Indiquez d'où proviennent les fonds pour cette dépense. Vous pouvez utiliser plusieurs sources si nécessaire.
            </p>

            @error('funding_sources')
                <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    @if(is_array($message))
                        @foreach($message as $error)
                            <p class="text-sm text-red-600 dark:text-red-400">• {{ $error }}</p>
                        @endforeach
                    @else
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @endif
                </div>
            @enderror

            <div id="funding-sources-container">
                @php
                    $existingSources = old('funding_sources', $expense->fundingSources ?? collect());
                    if (!is_array($existingSources) && !($existingSources instanceof \Illuminate\Support\Collection)) {
                        $existingSources = [];
                    }
                @endphp

                @forelse($existingSources as $index => $source)
                    <div class="funding-source-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg" data-index="{{ $index }}">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Type de recette (source précise)</label>
                            <select name="funding_sources[{{ $index }}][revenue_type_id]" class="funding-source-type w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm" required>
                                <option value="">-- Choisir une source --</option>
                                @foreach ($revenueTypes as $type)
                                    <option value="{{ $type->id }}" 
                                        data-solde="{{ $type->solde_disponible ?? 0 }}"
                                        data-category-id="{{ $type->revenue_category_id }}"
                                        {{ (is_array($source) ? ($source['revenue_type_id'] ?? '') : ($source->revenue_type_id ?? '')) == $type->id ? 'selected' : '' }}>
                                        {{ $type->nom }} ({{ number_format($type->solde_disponible ?? 0, 0, ',', ' ') }} FCFA disponible)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Montant alloué (FCFA)</label>
                            <input type="number" 
                                name="funding_sources[{{ $index }}][montant_alloue]" 
                                class="funding-source-amount w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm" 
                                step="0.01" 
                                min="0"
                                value="{{ is_array($source) ? ($source['montant_alloue'] ?? '') : ($source->montant_alloue ?? '') }}"
                                placeholder="0" 
                                required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Action</label>
                            <button type="button" class="remove-funding-source w-full px-3 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40">
                                Retirer
                            </button>
                        </div>
                    </div>
                @empty
                    {{-- Première source par défaut --}}
                    <div class="funding-source-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg" data-index="0">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Type de recette (source précise)</label>
                            <select name="funding_sources[0][revenue_type_id]" class="funding-source-type w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm" required>
                                <option value="">-- Choisir une source --</option>
                                @foreach ($revenueTypes as $type)
                                    <option value="{{ $type->id }}" 
                                        data-solde="{{ $type->solde_disponible ?? 0 }}"
                                        data-category-id="{{ $type->revenue_category_id }}">
                                        {{ $type->nom }} ({{ number_format($type->solde_disponible ?? 0, 0, ',', ' ') }} FCFA disponible)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Montant alloué (FCFA)</label>
                            <input type="number" 
                                name="funding_sources[0][montant_alloue]" 
                                class="funding-source-amount w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm" 
                                step="0.01" 
                                min="0"
                                placeholder="0" 
                                required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Action</label>
                            <button type="button" class="remove-funding-source w-full px-3 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40">
                                Retirer
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" id="add-funding-source" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Ajouter une source
                </button>

                <div class="text-sm">
                    <span class="text-slate-600 dark:text-slate-400">Total alloué:</span>
                    <span id="total-alloue" class="ml-2 font-bold text-lg text-slate-900 dark:text-slate-100">0 FCFA</span>
                </div>
            </div>
        </div>
    </div>

    <div class="hidden">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Méthode de paiement <span class="text-red-600">*</span></label>
        <select name="methode_paiement" class="{{ $field }}" required>
            @foreach (['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'carte' => 'Carte', 'mobile_money' => 'Mobile Money'] as $key => $label)
                <option value="{{ $key }}" {{ old('methode_paiement', $expense->methode_paiement) === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('methode_paiement')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="hidden">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Référence facture</label>
        <input type="text" name="facture_reference" value="{{ old('facture_reference', $expense->facture_reference) }}" class="{{ $field }}">
        @error('facture_reference')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Fournisseur</label>
        <input type="text" name="fournisseur" value="{{ old('fournisseur', $expense->fournisseur) }}" class="{{ $field }}" placeholder="Nom du fournisseur ou du vendeur">
        @error('fournisseur')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="revenue-form-grid__full">
        <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-2">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                </svg>
                Documents justificatifs
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">📄 Facture</label>
                    <input type="file" 
                           name="piece_facture" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="{{ $field }} file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-900/20 dark:file:text-emerald-400">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">PDF, JPG, PNG (max 5 Mo)</p>
                    @if($expense->piece_facture_path)
                        <p class="mt-1.5 text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <a href="{{ Storage::url($expense->piece_facture_path) }}" target="_blank" class="underline hover:text-emerald-700">Voir le fichier</a>
                        </p>
                    @endif
                    @error('piece_facture')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">🧾 Reçu de paiement</label>
                    <input type="file" 
                           name="piece_recu" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="{{ $field }} file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-900/20 dark:file:text-emerald-400">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">PDF, JPG, PNG (max 5 Mo)</p>
                    @if($expense->piece_recu_path)
                        <p class="mt-1.5 text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <a href="{{ Storage::url($expense->piece_recu_path) }}" target="_blank" class="underline hover:text-emerald-700">Voir le fichier</a>
                        </p>
                    @endif
                    @error('piece_recu')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">📎 Autre document</label>
                    <input type="file" 
                           name="piece_autre" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="{{ $field }} file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-900/20 dark:file:text-emerald-400">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Bon de livraison, devis, etc.</p>
                    @if($expense->piece_autre_path)
                        <p class="mt-1.5 text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <a href="{{ Storage::url($expense->piece_autre_path) }}" target="_blank" class="underline hover:text-emerald-700">Voir le fichier</a>
                        </p>
                    @endif
                    @error('piece_autre')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="revenue-form-grid__full">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Notes complémentaires</label>
        <textarea name="notes" rows="4" class="{{ $field }}" placeholder="Informations supplémentaires sur cette dépense...">{{ old('notes', $expense->notes) }}</textarea>
        @error('notes')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        let fundingSourceIndex = document.querySelectorAll('.funding-source-row').length;
        const container = document.getElementById('funding-sources-container');
        const addButton = document.getElementById('add-funding-source');
        const totalAlloueSpan = document.getElementById('total-alloue');
        const montantTotalInput = document.getElementById('montant_total');
        const revenueTypesData = @json($revenueTypes->values());
        const categorySelect = document.getElementById('revenue_category');
        let selectedCategoryId = categorySelect ? categorySelect.value : null;

        function createFundingSourceRow(index) {
            const row = document.createElement('div');
            row.className = 'funding-source-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg';
            row.dataset.index = index;

            let optionsHTML = '<option value="">-- Choisir une source --</option>';
            revenueTypesData.forEach(type => {
                // Filtrer par catégorie si une catégorie est sélectionnée
                if (selectedCategoryId && type.revenue_category_id != selectedCategoryId) {
                    return;
                }
                
                const formatted = new Intl.NumberFormat('fr-FR').format(type.solde_disponible);
                optionsHTML += `<option value="${type.id}" data-solde="${type.solde_disponible}" data-category-id="${type.revenue_category_id}">${type.nom} (${formatted} FCFA disponible)</option>`;
            });

            row.innerHTML = `
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Type de recette (source précise)</label>
                    <select name="funding_sources[${index}][revenue_type_id]" class="funding-source-type w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm" required>
                        ${optionsHTML}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Montant alloué (FCFA)</label>
                    <input type="number" 
                        name="funding_sources[${index}][montant_alloue]" 
                        class="funding-source-amount w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm" 
                        step="0.01" 
                        min="0"
                        placeholder="0" 
                        required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Action</label>
                    <button type="button" class="remove-funding-source w-full px-3 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40">
                        Retirer
                    </button>
                </div>
            `;

            return row;
        }

        function updateTotalAlloue() {
            let total = 0;
            document.querySelectorAll('.funding-source-amount').forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });

            const formatted = new Intl.NumberFormat('fr-FR').format(total);
            totalAlloueSpan.textContent = `${formatted} FCFA`;

            // Vérifier si le total correspond au montant
            const montantTotal = parseFloat(montantTotalInput.value.replace(/\s/g, '').replace(',', '.')) || 0;
            if (Math.abs(total - montantTotal) > 0.01 && total > 0) {
                totalAlloueSpan.classList.add('text-red-600', 'dark:text-red-400');
                totalAlloueSpan.classList.remove('text-emerald-600', 'dark:text-emerald-400', 'text-slate-900', 'dark:text-slate-100');
            } else if (Math.abs(total - montantTotal) <= 0.01 && total > 0) {
                totalAlloueSpan.classList.add('text-emerald-600', 'dark:text-emerald-400');
                totalAlloueSpan.classList.remove('text-red-600', 'dark:text-red-400', 'text-slate-900', 'dark:text-slate-100');
            } else {
                totalAlloueSpan.classList.add('text-slate-900', 'dark:text-slate-100');
                totalAlloueSpan.classList.remove('text-red-600', 'dark:text-red-400', 'text-emerald-600', 'dark:text-emerald-400');
            }
        }

        function filterUsedSources() {
            const usedTypes = new Set();
            document.querySelectorAll('.funding-source-type').forEach(select => {
                if (select.value) {
                    usedTypes.add(select.value);
                }
            });

            document.querySelectorAll('.funding-source-type').forEach(select => {
                const currentValue = select.value;
                Array.from(select.options).forEach(option => {
                    if (option.value && option.value !== currentValue && usedTypes.has(option.value)) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });
            });
        }

        function filterTypesByCategory() {
            selectedCategoryId = categorySelect ? categorySelect.value : null;
            
            // Filtrer toutes les sources de financement existantes
            document.querySelectorAll('.funding-source-type').forEach(select => {
                const currentValue = select.value;
                
                Array.from(select.options).forEach(option => {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }
                    
                    const optCategoryId = option.getAttribute('data-category-id');
                    if (selectedCategoryId && optCategoryId != selectedCategoryId) {
                        option.hidden = true;
                        if (option.value === currentValue) {
                            select.value = '';
                        }
                    } else {
                        option.hidden = false;
                    }
                });
            });

            updateTotalAlloue();
            filterUsedSources();
        }

        // Event listener pour le changement de catégorie
        if (categorySelect) {
            categorySelect.addEventListener('change', filterTypesByCategory);
            // Filtrer initialement
            filterTypesByCategory();
        }

        // Ajouter une source
        if (addButton) {
            addButton.addEventListener('click', () => {
                const newRow = createFundingSourceRow(fundingSourceIndex);
                container.appendChild(newRow);
                fundingSourceIndex++;
                updateTotalAlloue();
                filterUsedSources();
            });
        }

        // Délégation d'événements pour retirer une source
        if (container) {
            container.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-funding-source')) {
                    const row = e.target.closest('.funding-source-row');
                    if (container.querySelectorAll('.funding-source-row').length > 1) {
                        row.remove();
                        updateTotalAlloue();
                        filterUsedSources();
                    } else {
                        alert('Vous devez avoir au moins une source de financement.');
                    }
                }
            });

            // Écouter les changements de montant
            container.addEventListener('input', (e) => {
                if (e.target.classList.contains('funding-source-amount')) {
                    updateTotalAlloue();
                }
            });

            // Écouter les changements de type
            container.addEventListener('change', (e) => {
                if (e.target.classList.contains('funding-source-type')) {
                    filterUsedSources();
                }
            });
        }

        // Écouter les changements du montant total
        if (montantTotalInput) {
            montantTotalInput.addEventListener('input', updateTotalAlloue);
        }

        // Initialiser
        updateTotalAlloue();
        filterUsedSources();
    })();
</script>
@endpush
