@php
    $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $details = $details ?? [];
    $reportTarget = old('report_target', $details['report_target'] ?? 'global');
    $periodKind = old('period_kind', $details['period_kind'] ?? 'mensuel');
    $selectedYear = old('year', $details['year'] ?? now()->year);
    $selectedMonth = old('month', $details['month'] ?? now()->month);
    $selectedCategory = old('revenue_category_id', $details['revenue_category_id'] ?? null);
@endphp

<div class="revenue-form-grid revenue-form-grid--three">
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Paroisse <span class="text-red-600">*</span></label>
        <select name="paroisse_id" class="{{ $field }}" required>
            @foreach ($paroisses as $paroisse)
                <option value="{{ $paroisse->id }}" {{ (int) old('paroisse_id', $selectedParoisseId ?? $revenueReport->paroisse_id ?? 0) === (int) $paroisse->id ? 'selected' : '' }}>
                    {{ $paroisse->nom }}
                </option>
            @endforeach
        </select>
        @error('paroisse_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Type de rapport <span class="text-red-600">*</span></label>
        <select name="report_target" id="report_target" class="{{ $field }}" required>
            <option value="global" {{ $reportTarget === 'global' ? 'selected' : '' }}>Global (toutes catégories)</option>
            <option value="categorie" {{ $reportTarget === 'categorie' ? 'selected' : '' }}>Par catégorie</option>
        </select>
        @error('report_target')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div id="category_wrapper">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Catégorie <span class="text-red-600">*</span></label>
        <select name="revenue_category_id" class="{{ $field }}">
            <option value="">-- Choisir --</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ (int) $selectedCategory === (int) $category->id ? 'selected' : '' }}>{{ $category->nom }}</option>
            @endforeach
        </select>
        @error('revenue_category_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Période <span class="text-red-600">*</span></label>
        <select name="period_kind" id="period_kind" class="{{ $field }}" required>
            <option value="mensuel" {{ $periodKind === 'mensuel' ? 'selected' : '' }}>Mensuel</option>
            <option value="annuel" {{ $periodKind === 'annuel' ? 'selected' : '' }}>Annuel</option>
        </select>
        @error('period_kind')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div id="month_wrapper">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Mois <span class="text-red-600">*</span></label>
        <select name="month" class="{{ $field }}">
            @foreach ([
                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
                7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
            ] as $monthValue => $monthLabel)
                <option value="{{ $monthValue }}" {{ (int) $selectedMonth === (int) $monthValue ? 'selected' : '' }}>{{ $monthLabel }}</option>
            @endforeach
        </select>
        @error('month')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Année <span class="text-red-600">*</span></label>
        <input type="number" min="2000" max="2100" name="year" value="{{ $selectedYear }}" class="{{ $field }}" required>
        @error('year')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const target = document.getElementById('report_target');
        const period = document.getElementById('period_kind');
        const categoryWrapper = document.getElementById('category_wrapper');
        const monthWrapper = document.getElementById('month_wrapper');

        function syncForm() {
            const byCategory = target && target.value === 'categorie';
            const monthly = period && period.value === 'mensuel';
            if (categoryWrapper) categoryWrapper.style.display = byCategory ? '' : 'none';
            if (monthWrapper) monthWrapper.style.display = monthly ? '' : 'none';
        }

        if (target) target.addEventListener('change', syncForm);
        if (period) period.addEventListener('change', syncForm);
        syncForm();
    })();
</script>
@endpush

