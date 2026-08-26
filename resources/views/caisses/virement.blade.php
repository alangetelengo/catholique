@extends('layouts.app')

@section('title', 'Alimenter une caisse - Catholique')
@section('page-title', 'Alimenter une caisse')
@section('page-title-info', 'Virement depuis la trésorerie (Banque) ou depuis une autre recette.')

@section('header-back')
    <x-back-link :href="route('caisses.index')" />
@endsection

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')
@section('main-class', 'pt-2 pb-6 sm:pt-3 sm:pb-8')

@section('content')
    @php
        use App\Support\SubventionMensuelle;
        $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm';
        $selectedCaisse = old('caisse_id', request('caisse_id'));
        $mode = old('mode', 'tresorerie');
        $selectedMois = old('mois_capital');
        $selectedAnnee = (int) old('annee_capital', now()->format('Y'));
        $moisOptions = SubventionMensuelle::moisOptions();
    @endphp
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-4">
            Alimentez une caisse opérationnelle depuis la
            <strong class="font-semibold">trésorerie générale</strong>
            (capital Banque, par mois) ou depuis le solde disponible d’un autre type de recette.
            Solde trésorerie total :
            <strong class="tabular-nums">{{ number_format($tresorerie->solde_disponible ?? 0, 0, ',', ' ') }} FCFA</strong>.
        </p>
        <form method="post" action="{{ route('caisses.virement.store') }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Source <span class="text-red-600">*</span></label>
                    <select name="mode" id="alimentation_mode" class="{{ $field }}" required>
                        <option value="tresorerie" @selected($mode === 'tresorerie')>Trésorerie générale (Banque)</option>
                        <option value="recette" @selected($mode === 'recette')>Autre recette (quête, location…)</option>
                    </select>
                </div>
                <div id="envelope_wrapper" class="{{ $mode === 'tresorerie' ? '' : 'hidden' }}">
                    <label class="block text-sm font-semibold mb-1.5">Mois du capital <span class="text-red-600">*</span></label>
                    @if (($envelopesCapital ?? collect())->isNotEmpty())
                        <select name="mois_capital" id="mois_capital" class="{{ $field }}">
                            <option value="">-- Choisir le mois --</option>
                            @foreach ($envelopesCapital as $envelope)
                                <option value="{{ $envelope['mois_capital'] }}"
                                    data-annee="{{ $envelope['annee_capital'] }}"
                                    data-disponible="{{ $envelope['disponible'] }}"
                                    @selected((string) $selectedMois === (string) $envelope['mois_capital'] && $selectedAnnee === (int) $envelope['annee_capital'])>
                                    {{ $envelope['label'] }} — {{ number_format($envelope['disponible'], 0, ',', ' ') }} FCFA dispo
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="annee_capital" id="annee_capital" value="{{ old('annee_capital') }}">
                        <p id="envelope_disponible_hint" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400"></p>
                    @else
                        <p class="text-sm text-amber-700 dark:text-amber-300">Aucune enveloppe de capital disponible. Enregistrez d&apos;abord une recette Banque.</p>
                    @endif
                    @error('mois_capital')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    @error('annee_capital')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div id="revenue_type_wrapper" class="{{ $mode === 'recette' ? '' : 'hidden' }}">
                    <label class="block text-sm font-semibold mb-1.5">Type de recette source</label>
                    <select name="revenue_type_id" class="{{ $field }}">
                        <option value="">-- Choisir --</option>
                        @foreach ($revenueTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) old('revenue_type_id') === (string) $type->id)>
                                {{ $type->category?->nom }} — {{ $type->nom }} ({{ number_format($type->solde_disponible ?? 0, 0, ',', ' ') }} FCFA)
                            </option>
                        @endforeach
                    </select>
                    @error('revenue_type_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Caisse destination <span class="text-red-600">*</span></label>
                    <select name="caisse_id" class="{{ $field }}" required>
                        <option value="">-- Choisir --</option>
                        @foreach ($caisses as $caisse)
                            <option value="{{ $caisse->id }}" @selected((string) $selectedCaisse === (string) $caisse->id)>
                                {{ $caisse->nom }} ({{ number_format($caisse->solde_disponible ?? 0, 0, ',', ' ') }} FCFA)
                            </option>
                        @endforeach
                    </select>
                    @error('caisse_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Montant (FCFA) <span class="text-red-600">*</span></label>
                    <input type="text" name="montant" id="montant_virement" value="{{ old('montant') }}" class="{{ $field }} js-montant-fcfa" placeholder="700 000 fcfa" required>
                    @error('montant')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Date <span class="text-red-600">*</span></label>
                    <input type="date" name="date_mouvement" value="{{ old('date_mouvement', now()->format('Y-m-d')) }}" class="{{ $field }}" required>
                    @error('date_mouvement')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Libellé <span class="text-red-600">*</span></label>
                    <input type="text" name="libelle" value="{{ old('libelle', 'Alimentation de caisse') }}" class="{{ $field }}" required>
                    @error('libelle')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2 xl:col-span-3">
                    <label class="block text-sm font-semibold mb-1.5">Notes</label>
                    <textarea name="notes" rows="3" class="{{ $field }}">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                <a href="{{ route('caisses.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const mode = document.getElementById('alimentation_mode');
        const revenueWrapper = document.getElementById('revenue_type_wrapper');
        const envelopeWrapper = document.getElementById('envelope_wrapper');
        const moisSelect = document.getElementById('mois_capital');
        const anneeInput = document.getElementById('annee_capital');
        const hint = document.getElementById('envelope_disponible_hint');
        const montantInput = document.getElementById('montant_virement');

        function formatFcfa(value) {
            return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
        }

        function parseMontantRaw(value) {
            const raw = (value || '').toString().replace(/\s/g, '').replace(/fcfa/ig, '').replace(',', '.');
            return parseFloat(raw) || 0;
        }

        function syncEnvelopeFields() {
            if (!moisSelect || !anneeInput) return;
            const option = moisSelect.options[moisSelect.selectedIndex];
            if (!option || !option.value) {
                anneeInput.value = '';
                if (hint) hint.textContent = '';
                if (montantInput) montantInput.removeAttribute('data-max-disponible');
                return;
            }
            anneeInput.value = option.dataset.annee || '';
            const dispo = parseFloat(option.dataset.disponible || '0');
            if (hint) {
                hint.textContent = 'Disponible en trésorerie pour ce mois : ' + formatFcfa(dispo);
            }
            if (montantInput && dispo > 0) {
                montantInput.setAttribute('data-max-disponible', String(dispo));
            }
        }

        const form = document.querySelector('form[action="{{ route('caisses.virement.store') }}"]');
        if (form && montantInput) {
            form.addEventListener('submit', function (e) {
                if (!mode || mode.value !== 'tresorerie' || !moisSelect || !moisSelect.value) {
                    return;
                }
                const maxDispo = parseFloat(montantInput.getAttribute('data-max-disponible') || '0');
                const montant = parseMontantRaw(montantInput.value);
                if (maxDispo > 0 && montant > maxDispo + 0.01) {
                    e.preventDefault();
                    alert('Montant supérieur au disponible pour ce mois : ' + formatFcfa(maxDispo));
                }
            });
        }

        if (mode) {
            mode.addEventListener('change', function () {
                const isTresorerie = mode.value === 'tresorerie';
                if (revenueWrapper) revenueWrapper.classList.toggle('hidden', isTresorerie);
                if (envelopeWrapper) envelopeWrapper.classList.toggle('hidden', !isTresorerie);
            });
        }

        if (moisSelect) {
            moisSelect.addEventListener('change', syncEnvelopeFields);
            syncEnvelopeFields();
        }
    })();
</script>
@endpush
