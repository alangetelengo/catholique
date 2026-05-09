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
