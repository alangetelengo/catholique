@extends('layouts.app')

@section('title', 'Modifier catégorie - Catholique')
@section('page-title', 'Modifier catégorie')
@section('page-title-info', 'Mise à jour des paramètres de la catégorie de revenu.')

@section('header-back')
    <x-back-link :href="route('revenue-categories.index')" />
@endsection

@section('content')
    <div class="adventiste-card-pro-static p-6">
        <form method="post" action="{{ route('revenue-categories.update', $category) }}" class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @csrf
            @method('put')
            <div>
                <label class="block text-sm font-semibold mb-2">Code</label>
                <input type="text" value="{{ $category->code }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700 px-3 py-2.5 text-sm" disabled>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Nom</label>
                <input type="text" name="nom" value="{{ old('nom', $category->nom) }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                @error('nom')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">{{ old('description', $category->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Ordre</label>
                <input type="number" min="0" name="ordre" value="{{ old('ordre', $category->ordre) }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" name="actif" value="1" {{ old('actif', $category->actif) ? 'checked' : '' }}>
                    Actif
                </label>
            </div>
            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="adventiste-btn-primary">Mettre à jour</button>
                <a href="{{ route('revenue-categories.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
