@php
    $gridColumns = (int) ($formColumns ?? 2);
    $gridClass = $gridColumns === 3 ? 'revenue-form-grid revenue-form-grid--three' : 'revenue-form-grid';
    $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $selectedCategory = old('categorie_charge', $expense->categorie_charge ?? 'charge_fixe');
    $selectedType = old('type_charge', $expense->type_charge ?? \App\Support\ExpenseChargeCatalog::defaultTypeForCategory('charge_fixe'));
    $typesForSelect = $selectedCategory === 'alimentation_popote'
        ? []
        : \App\Support\ExpenseChargeCatalog::labeledOptionsForCategory($selectedCategory);
    if ($selectedCategory !== 'alimentation_popote' && $selectedType !== '' && ! isset($typesForSelect[$selectedType])) {
        $allTypeLabels = trans('expenses.types');
        $allTypeLabels = is_array($allTypeLabels) ? $allTypeLabels : [];
        $typesForSelect[$selectedType] = $allTypeLabels[$selectedType] ?? $selectedType;
    }
    $typesByCategoryForJs = \App\Support\ExpenseChargeCatalog::labeledOptionsByCategoryExcludingPopote();
@endphp

<div class="{{ $gridClass }}">
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Catégorie <span class="text-red-600">*</span></label>
        <select name="categorie_charge" id="categorie_charge" class="{{ $field }}" required>
            <option value="charge_fixe" {{ $selectedCategory === 'charge_fixe' ? 'selected' : '' }}>Charge fixe</option>
            <option value="charge_variable" {{ $selectedCategory === 'charge_variable' ? 'selected' : '' }}>Charge variable</option>
            <option value="charge_exceptionnelle" {{ $selectedCategory === 'charge_exceptionnelle' ? 'selected' : '' }}>Charge exceptionnelle</option>
            <option value="alimentation_popote" {{ $selectedCategory === 'alimentation_popote' ? 'selected' : '' }}>Alimentation popote</option>
        </select>
        @error('categorie_charge')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div id="typeChargeWrapper">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Type de charge <span class="text-red-600">*</span></label>
        <select name="type_charge" id="type_charge" class="{{ $field }}">
            @foreach ($typesForSelect as $value => $label)
                <option value="{{ $value }}" {{ $selectedType === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('type_charge')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
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

    <!-- cacher la méthode de paiement dans le formulaire-->
    <div class="hidden">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Méthode de paiement <span class="text-red-600">*</span></label>
        <select name="methode_paiement" class="{{ $field }}" required>
            @foreach (['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'carte' => 'Carte', 'mobile_money' => 'Mobile Money'] as $key => $label)
                <option value="{{ $key }}" {{ old('methode_paiement', $expense->methode_paiement) === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('methode_paiement')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div id="jourDepenseWrapper">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Jour de la semaine (auto)</label>
        <input type="hidden" name="jour_semaine" id="jour_semaine_hidden_expense" value="{{ old('jour_semaine', $expense->jour_semaine) }}">
        <input type="text" id="jour_semaine_auto_expense" class="{{ $field }} bg-slate-50 dark:bg-slate-800/70" readonly>
        @error('jour_semaine')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div id="libelleWrapper">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Libellé alimentation <span class="text-red-600">*</span></label>
        <input type="text" name="libelle" id="libelle" value="{{ old('libelle', $expense->libelle) }}" class="{{ $field }}" placeholder="Ex: Provision vivres frais">
        @error('libelle')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="hidden">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Référence facture</label>
        <input type="text" name="facture_reference" value="{{ old('facture_reference', $expense->facture_reference) }}" class="{{ $field }}">
        @error('facture_reference')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Fournisseur</label>
        <input type="text" name="fournisseur" value="{{ old('fournisseur', $expense->fournisseur) }}" class="{{ $field }}">
        @error('fournisseur')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="revenue-form-grid__full">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Notes</label>
        <textarea name="notes" rows="4" class="{{ $field }}">{{ old('notes', $expense->notes) }}</textarea>
        @error('notes')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const typesByCategory = @json($typesByCategoryForJs);
        const popoteTypeLabel = @json(__('expenses.types.alimentation'));
        const categorySelect = document.getElementById('categorie_charge');
        const typeWrapper = document.getElementById('typeChargeWrapper');
        const typeSelect = document.getElementById('type_charge');
        const dayWrapper = document.getElementById('jourDepenseWrapper');
        const dayHidden = document.getElementById('jour_semaine_hidden_expense');
        const dayAuto = document.getElementById('jour_semaine_auto_expense');
        const libelleWrapper = document.getElementById('libelleWrapper');
        const libelleInput = document.getElementById('libelle');
        const dateInput = document.getElementById('date_depense');
        const dayNames = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

        function syncDay() {
            if (!dateInput || !dayHidden || !dayAuto) return;
            const value = dateInput.value;
            if (!value) {
                dayHidden.value = '';
                dayAuto.value = '';
                return;
            }
            const date = new Date(value + 'T00:00:00');
            if (Number.isNaN(date.getTime())) return;
            const day = dayNames[date.getDay()] || '';
            dayHidden.value = day;
            dayAuto.value = day ? day.charAt(0).toUpperCase() + day.slice(1) : '';
        }

        function rebuildTypeOptions() {
            if (!categorySelect || !typeSelect) return;
            const cat = categorySelect.value;
            if (cat === 'alimentation_popote') return;
            const allowed = typesByCategory[cat] || {};
            const keys = Object.keys(allowed);
            const prev = typeSelect.value;
            typeSelect.innerHTML = '';
            keys.forEach(function (k) {
                const o = document.createElement('option');
                o.value = k;
                o.textContent = allowed[k];
                typeSelect.appendChild(o);
            });
            if (keys.indexOf(prev) !== -1) {
                typeSelect.value = prev;
            } else if (keys.indexOf('autre') !== -1) {
                typeSelect.value = 'autre';
            } else if (keys.length) {
                typeSelect.value = keys[0];
            }
        }

        function toggleFields() {
            if (!categorySelect) return;
            const isPopote = categorySelect.value === 'alimentation_popote';
            if (typeWrapper) typeWrapper.style.display = isPopote ? 'none' : '';
            if (dayWrapper) dayWrapper.style.display = isPopote ? '' : 'none';
            if (libelleWrapper) libelleWrapper.style.display = isPopote ? '' : 'none';

            if (isPopote) {
                if (typeSelect) {
                    typeSelect.innerHTML = '';
                    const o = document.createElement('option');
                    o.value = 'alimentation';
                    o.textContent = popoteTypeLabel || 'Alimentation';
                    typeSelect.appendChild(o);
                    typeSelect.value = 'alimentation';
                }
                if (libelleInput) libelleInput.setAttribute('required', 'required');
                syncDay();
            } else {
                if (dayHidden) dayHidden.value = '';
                if (dayAuto) dayAuto.value = '';
                if (libelleInput) libelleInput.removeAttribute('required');
            }
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', function () {
                if (categorySelect.value !== 'alimentation_popote') {
                    rebuildTypeOptions();
                }
                toggleFields();
            });
        }
        if (dateInput) {
            dateInput.addEventListener('change', syncDay);
            dateInput.addEventListener('input', syncDay);
        }
        toggleFields();
        syncDay();
    })();
</script>
@endpush

