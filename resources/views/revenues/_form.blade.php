@php
    $selectedCategory = old('revenue_category_id', $revenue->revenue_category_id);
    $selectedType = old('revenue_type_id', $revenue->revenue_type_id);
    $gridColumns = (int) ($formColumns ?? 2);
    $gridClass = $gridColumns === 3 ? 'revenue-form-grid revenue-form-grid--three' : 'revenue-form-grid';
    $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $selectedMoisLocation = old('mois_location');
    if ($selectedMoisLocation === null) {
        $selectedMoisLocation = $revenue->mois_location;
    }
    if (is_string($selectedMoisLocation) && preg_match('/^\d{4}-(\d{2})$/', $selectedMoisLocation, $matches)) {
        $selectedMoisLocation = $matches[1];
    }
    if ($selectedMoisLocation === null) {
        $selectedMoisLocation = now()->format('m');
    }
    $selectedMoisSubvention = old('mois_subvention', $revenue->mois_subvention ?? now()->format('Y-m'));
    $moisOptions = [
        '01' => 'Janvier',
        '02' => 'Fevrier',
        '03' => 'Mars',
        '04' => 'Avril',
        '05' => 'Mai',
        '06' => 'Juin',
        '07' => 'Juillet',
        '08' => 'Aout',
        '09' => 'Septembre',
        '10' => 'Octobre',
        '11' => 'Novembre',
        '12' => 'Decembre',
    ];
@endphp

<div class="{{ $gridClass }}">

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Categorie <span class="text-red-600">*</span></label>
        <select
            name="revenue_category_id"
            id="revenue_category_id"
            class="{{ $field }}"
            required
        >
            <option value="">-- Choisir --</option>
            @foreach ($categories as $category)
                <option
                    value="{{ $category->id }}"
                    data-category-code="{{ $category->code }}"
                    {{ (string) $selectedCategory === (string) $category->id ? 'selected' : '' }}
                >
                    {{ $category->nom }}
                </option>
            @endforeach
        </select>
        @error('revenue_category_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Type <span class="text-red-600">*</span></label>
        <select
            name="revenue_type_id"
            id="revenue_type_id"
            class="{{ $field }}"
            required
        >
            <option value="">-- Choisir --</option>
            @foreach ($categories as $category)
                @foreach ($category->types as $type)
                    <option
                        value="{{ $type->id }}"
                        data-category="{{ $category->id }}"
                        data-type-code="{{ $type->code }}"
                        {{ (string) $selectedType === (string) $type->id ? 'selected' : '' }}
                    >
                        {{ $type->nom }}
                    </option>
                @endforeach
            @endforeach
        </select>
        @error('revenue_type_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date de recette <span class="text-red-600">*</span></label>
        <input
            type="date"
            id="date_recette"
            name="date_recette"
            value="{{ old('date_recette', optional($revenue->date_recette)->format('Y-m-d') ?? $revenue->date_recette) }}"
            class="{{ $field }}"
            required
        >
        @error('date_recette')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div id="jourSemaineWrapper">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5" for="jour_semaine_auto">Jour de la semaine (auto)</label>
        <input
            type="text"
            id="jour_semaine_auto"
            class="{{ $field }} bg-slate-50 dark:bg-slate-800/70"
            value=""
            readonly
            tabindex="-1"
            aria-live="polite"
            autocomplete="off"
        >
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Déduit de la date de recette ; la même valeur est enregistrée en base pour les rapports.</p>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Montant (FCFA) <span class="text-red-600">*</span></label>
        <input
            type="text"
            name="montant"
            value="{{ old('montant', $revenue->montant) }}"
            class="{{ $field }} js-montant-fcfa"
            placeholder="10 000 fcfa"
            required
        >
        @error('montant')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="hidden">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Methode de paiement <span class="text-red-600">*</span></label>
        <select
            name="methode_paiement"
            class="{{ $field }}"
            required
        >
            @foreach (['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'carte' => 'Carte', 'mobile_money' => 'Mobile Money'] as $key => $label)
                <option value="{{ $key }}" {{ old('methode_paiement', $revenue->methode_paiement) === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('methode_paiement')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div id="moisSubventionWrapper">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Mois concerné (subvention) <span class="text-red-600">*</span></label>
        <input
            type="month"
            name="mois_subvention"
            id="mois_subvention"
            value="{{ $selectedMoisSubvention }}"
            class="{{ $field }}"
        >
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Mois auquel cette subvention s&apos;applique (carburant, eau, popote, etc.), indépendamment de la date de réception.</p>
        @error('mois_subvention')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div id="moisLocationWrapper">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Mois de location (loyer boutique)</label>
        <select name="mois_location" class="{{ $field }}">
            <option value="">-- Choisir un mois --</option>
            @foreach ($moisOptions as $value => $label)
                <option value="{{ $value }}" {{ (string) $selectedMoisLocation === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('mois_location')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div id="donateurNomWrapper">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Nom du donateur (procure)</label>
        <input
            type="text"
            name="donateur_nom"
            value="{{ old('donateur_nom', $revenue->donateur_nom) }}"
            class="{{ $field }}"
        >
        @error('donateur_nom')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div id="donateurTelephoneWrapper">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Telephone du donateur</label>
        <input
            type="text"
            name="donateur_telephone"
            value="{{ old('donateur_telephone', $revenue->donateur_telephone) }}"
            class="{{ $field }}"
        >
        @error('donateur_telephone')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="revenue-form-grid__full">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Notes</label>
        <textarea
            name="notes"
            rows="4"
            class="{{ $field }}"
        >{{ old('notes', $revenue->notes) }}</textarea>
        @error('notes')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const categorySelect = document.getElementById('revenue_category_id');
            const typeSelect = document.getElementById('revenue_type_id');
            const dateInput = document.getElementById('date_recette');
            const dayAutoInput = document.getElementById('jour_semaine_auto');
            const monthWrapper = document.getElementById('moisLocationWrapper');
            const moisSubventionWrapper = document.getElementById('moisSubventionWrapper');
            const moisSubventionInput = document.getElementById('mois_subvention');
            const donorNomWrapper = document.getElementById('donateurNomWrapper');
            const donorTelephoneWrapper = document.getElementById('donateurTelephoneWrapper');

            if (!categorySelect || !typeSelect) {
                return;
            }

            function updateTypeOptions() {
                const categoryId = categorySelect.value;
                let hasSelectedVisibleOption = false;

                Array.from(typeSelect.options).forEach((option) => {
                    const optionCategory = option.getAttribute('data-category');
                    if (!optionCategory) {
                        option.hidden = false;
                        return;
                    }

                    const visible = optionCategory === categoryId;
                    option.hidden = !visible;
                    if (visible && option.selected) {
                        hasSelectedVisibleOption = true;
                    }
                });

                if (!hasSelectedVisibleOption) {
                    typeSelect.value = '';
                }
            }

            function computeWeekdayLabel(dateValue) {
                if (!dateValue) {
                    return '';
                }
                const date = new Date(dateValue + 'T00:00:00');
                if (Number.isNaN(date.getTime())) {
                    return '';
                }
                const names = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
                return names[date.getDay()] || '';
            }

            function syncAutoWeekday() {
                if (!dayAutoInput) {
                    return;
                }
                const day = computeWeekdayLabel(dateInput ? dateInput.value : '');
                dayAutoInput.value = day ? day.charAt(0).toUpperCase() + day.slice(1) : '';
            }

            function toggleConditionalFields() {
                const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
                const categoryCode = selectedCategory ? (selectedCategory.getAttribute('data-category-code') || '') : '';
                const selectedType = typeSelect.options[typeSelect.selectedIndex];
                const typeCode = selectedType ? selectedType.getAttribute('data-type-code') : '';

                const isLocationBoutique = categoryCode === 'location' && (typeCode === 'loyer-boutique' || typeCode === 'loyer_boutique');
                const isProcure = categoryCode === 'procure';
                const isSubvention = categoryCode === 'subvention';

                if (monthWrapper) monthWrapper.style.display = isLocationBoutique ? '' : 'none';
                if (moisSubventionWrapper) moisSubventionWrapper.style.display = isSubvention ? '' : 'none';
                if (moisSubventionInput) moisSubventionInput.required = isSubvention;
                if (donorNomWrapper) donorNomWrapper.style.display = isProcure ? '' : 'none';
                if (donorTelephoneWrapper) donorTelephoneWrapper.style.display = isProcure ? '' : 'none';
            }

            categorySelect.addEventListener('change', function () {
                updateTypeOptions();
                toggleConditionalFields();
            });
            typeSelect.addEventListener('change', toggleConditionalFields);
            if (dateInput) {
                dateInput.addEventListener('change', syncAutoWeekday);
                dateInput.addEventListener('input', syncAutoWeekday);
            }

            updateTypeOptions();
            syncAutoWeekday();
            toggleConditionalFields();
        })();
    </script>
@endpush
