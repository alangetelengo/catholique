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
        $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm';
        $selectedCaisse = old('caisse_id', request('caisse_id'));
        $mode = old('mode', 'tresorerie');
    @endphp
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-4">
            Alimentez une caisse opérationnelle depuis la
            <strong class="font-semibold">trésorerie générale</strong>
            (capital Banque) ou depuis le solde disponible d’un autre type de recette.
            Solde trésorerie disponible :
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
                    <input type="number" step="0.01" min="0.01" name="montant" value="{{ old('montant') }}" class="{{ $field }}" required>
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
        const wrapper = document.getElementById('revenue_type_wrapper');
        if (!mode || !wrapper) return;
        mode.addEventListener('change', function () {
            wrapper.classList.toggle('hidden', mode.value !== 'recette');
        });
    })();
</script>
@endpush
