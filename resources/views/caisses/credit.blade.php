@extends('layouts.app')

@section('title', 'Crédit direct caisse - Catholique')
@section('page-title', 'Crédit direct de caisse')
@section('page-title-info', 'Versement diocésain affecté directement à une caisse opérationnelle.')

@section('header-back')
    <x-back-link :href="route('caisses.index')" />
@endsection

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')
@section('main-class', 'pt-2 pb-6 sm:pt-3 sm:pb-8')

@section('content')
    @php
        $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm';
        $selectedCaisse = old('caisse_id', request('caisse_id'));
    @endphp
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-4">
            Utilisez ce formulaire pour un crédit <strong class="font-semibold">directement</strong> sur une caisse
            (hors capital Banque). Le capital de l’économat diocésain doit être enregistré en
            <strong class="font-semibold">Recette → Banque → Revenu principal</strong>, puis alimenter les caisses via un virement.
        </p>
        <form method="post" action="{{ route('caisses.credit.store') }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Caisse <span class="text-red-600">*</span></label>
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
                    <input type="text" name="montant" value="{{ old('montant') }}" class="{{ $field }} js-montant-fcfa" placeholder="700 000 fcfa" required>
                    @error('montant')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Date <span class="text-red-600">*</span></label>
                    <input type="date" name="date_mouvement" value="{{ old('date_mouvement', now()->format('Y-m-d')) }}" class="{{ $field }}" required>
                    @error('date_mouvement')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2 xl:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5">Libellé <span class="text-red-600">*</span></label>
                    <input type="text" name="libelle" value="{{ old('libelle', 'Crédit diocésain') }}" class="{{ $field }}" required>
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
