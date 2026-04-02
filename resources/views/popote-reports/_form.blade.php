@php
    $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80';
    $details = $detailsRecettes ?? [];
    $periodKind = old('period_kind', $details['period_kind'] ?? 'mensuel');
    $month = old('month', $details['month'] ?? now()->month);
    $year = old('year', $details['year'] ?? now()->year);
@endphp

<div class="revenue-form-grid revenue-form-grid--three">
    <div>
        <label class="block text-sm font-semibold mb-1.5 text-slate-800 dark:text-slate-200">Paroisse <span class="text-red-600">*</span></label>
        <select name="paroisse_id" class="{{ $field }}" required>
            @foreach($paroisses as $paroisse)
                <option value="{{ $paroisse->id }}" {{ (int) old('paroisse_id', $selectedParoisseId ?? $popoteReport->paroisse_id ?? 0) === (int) $paroisse->id ? 'selected' : '' }}>{{ $paroisse->nom }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1.5 text-slate-800 dark:text-slate-200">Période <span class="text-red-600">*</span></label>
        <select name="period_kind" id="period_kind" class="{{ $field }}" required>
            <option value="mensuel" {{ $periodKind === 'mensuel' ? 'selected' : '' }}>Mensuel</option>
            <option value="annuel" {{ $periodKind === 'annuel' ? 'selected' : '' }}>Annuel</option>
        </select>
    </div>
    <div id="month_wrap">
        <label class="block text-sm font-semibold mb-1.5 text-slate-800 dark:text-slate-200">Mois <span class="text-red-600">*</span></label>
        <select name="month" class="{{ $field }}">
            @foreach([1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'] as $k=>$v)
                <option value="{{ $k }}" {{ (int)$month === (int)$k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1.5 text-slate-800 dark:text-slate-200">Année <span class="text-red-600">*</span></label>
        <input type="number" min="2000" max="2100" name="year" value="{{ $year }}" class="{{ $field }}" required>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const period = document.getElementById('period_kind');
        const monthWrap = document.getElementById('month_wrap');
        function sync() {
            if (!period || !monthWrap) return;
            monthWrap.style.display = period.value === 'mensuel' ? '' : 'none';
        }
        if (period) period.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush

