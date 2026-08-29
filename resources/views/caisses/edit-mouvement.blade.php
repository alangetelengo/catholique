@extends('layouts.app')

@section('title', 'Modifier une alimentation - Catholique')
@section('page-title', 'Modifier une alimentation')
@section('page-title-info', 'Corrigez le montant, la date ou le mois du capital. La trésorerie est recalculée si besoin.')

@section('header-back')
    <x-back-link :href="route('caisses.show', $destination)" />
@endsection

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')
@section('main-class', 'pt-2 pb-6 sm:pt-3 sm:pb-8')

@section('content')
    @php
        use App\Models\CaisseMouvement;
        $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm';
        $isVirement = $credit->type === CaisseMouvement::TYPE_VIREMENT;
        $selectedMois = old('mois_capital', $credit->mois_capital);
        $selectedAnnee = (int) old('annee_capital', $credit->annee_capital ?? now()->format('Y'));
    @endphp
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-4">
            Caisse : <strong class="font-semibold">{{ $destination->nom }}</strong>
            — type :
            <strong class="font-semibold">{{ str_replace('_', ' ', $credit->type) }}</strong>.
            @if ($isVirement)
                La modification met à jour aussi le débit en trésorerie pour le mois du capital.
            @endif
        </p>
        <form method="post" action="{{ route('caisses.mouvements.update', $credit) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                @if ($isVirement)
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Mois du capital <span class="text-red-600">*</span></label>
                        @if (($envelopesCapital ?? collect())->isNotEmpty())
                            <select name="mois_capital" id="mois_capital" class="{{ $field }}" required>
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
                            <input type="hidden" name="annee_capital" id="annee_capital" value="{{ old('annee_capital', $selectedAnnee) }}">
                            <p id="envelope_disponible_hint" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400"></p>
                        @endif
                        @error('mois_capital')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        @error('annee_capital')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Montant (FCFA) <span class="text-red-600">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="montant" id="montant_edit" value="{{ old('montant', $credit->montant) }}" class="{{ $field }}" required>
                    @error('montant')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Date <span class="text-red-600">*</span></label>
                    <input type="date" name="date_mouvement" value="{{ old('date_mouvement', optional($credit->date_mouvement)->format('Y-m-d')) }}" class="{{ $field }}" required>
                    @error('date_mouvement')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5">Libellé <span class="text-red-600">*</span></label>
                    <input type="text" name="libelle" value="{{ old('libelle', $credit->libelle) }}" class="{{ $field }}" required>
                    @error('libelle')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2 xl:col-span-3">
                    <label class="block text-sm font-semibold mb-1.5">Notes</label>
                    <textarea name="notes" rows="3" class="{{ $field }}">{{ old('notes', $credit->notes) }}</textarea>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                <a href="{{ route('caisses.show', $destination) }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection

@if ($isVirement)
@push('scripts')
<script>
    (function () {
        const moisSelect = document.getElementById('mois_capital');
        const anneeInput = document.getElementById('annee_capital');
        const hint = document.getElementById('envelope_disponible_hint');
        const montantInput = document.getElementById('montant_edit');

        function formatFcfa(value) {
            return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
        }

        function syncEnvelopeFields() {
            if (!moisSelect || !anneeInput) return;
            const option = moisSelect.options[moisSelect.selectedIndex];
            if (!option || !option.value) {
                anneeInput.value = '';
                if (hint) hint.textContent = '';
                return;
            }
            anneeInput.value = option.dataset.annee || '';
            const dispo = parseFloat(option.dataset.disponible || '0');
            if (hint) {
                hint.textContent = 'Disponible en trésorerie pour ce mois (hors ce mouvement) : ' + formatFcfa(dispo);
            }
            if (montantInput && dispo > 0) {
                montantInput.setAttribute('max', String(dispo));
            }
        }

        if (moisSelect) {
            moisSelect.addEventListener('change', syncEnvelopeFields);
            syncEnvelopeFields();
        }
    })();
</script>
@endpush
@endif
