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
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">D'où proviennent les fonds pour cette dépense ?</p>
        @error('revenue_category_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Type de recette (source précise) <span class="text-red-600">*</span></label>
        <select name="revenue_type_id" id="revenue_type" class="{{ $field }}" required>
            <option value="">-- Choisir le type --</option>
            @foreach ($revenueTypes as $type)
                <option value="{{ $type->id }}" 
                    data-category-id="{{ $type->revenue_category_id }}"
                    {{ (string) old('revenue_type_id', $expense->revenue_type_id) === (string) $type->id ? 'selected' : '' }}>
                    {{ $type->nom }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Type précis de la recette source</p>
        @error('revenue_type_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date de dépense <span class="text-red-600">*</span></label>
        <input type="date" name="date_depense" id="date_depense" value="{{ old('date_depense', optional($expense->date_depense)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="{{ $field }}" required>
        @error('date_depense')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Montant (FCFA) <span class="text-red-600">*</span></label>
        <input type="text" name="montant" value="{{ old('montant', $expense->montant) }}" class="{{ $field }} js-montant-fcfa" placeholder="10 000 fcfa" required>
        @error('montant')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="revenue-form-grid__full">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Libellé (description de la dépense) <span class="text-red-600">*</span></label>
        <input type="text" name="libelle" id="libelle" value="{{ old('libelle', $expense->libelle) }}" class="{{ $field }}" placeholder="Ex: Achat de riz pour popote" required>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Décrivez précisément l'achat effectué</p>
        @error('libelle')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
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
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Notes complémentaires</label>
        <textarea name="notes" rows="4" class="{{ $field }}" placeholder="Informations supplémentaires sur cette dépense...">{{ old('notes', $expense->notes) }}</textarea>
        @error('notes')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const categorySelect = document.getElementById('revenue_category');
        const typeSelect = document.getElementById('revenue_type');

        function filterTypesByCategory() {
            if (!categorySelect || !typeSelect) return;
            
            const selectedCategoryId = categorySelect.value;
            const allOptions = Array.from(typeSelect.options);

            allOptions.forEach(opt => {
                if (!opt.value) {
                    opt.hidden = false;
                    return;
                }
                const optCategoryId = opt.getAttribute('data-category-id');
                opt.hidden = optCategoryId !== selectedCategoryId;
            });

            // Réinitialiser la sélection si l'option n'est plus visible
            const selectedOption = typeSelect.querySelector(`option[value="${typeSelect.value}"]`);
            if (selectedOption && selectedOption.hidden) {
                typeSelect.value = '';
            }
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', filterTypesByCategory);
            filterTypesByCategory(); // Initial filter
        }
    })();
</script>
@endpush
