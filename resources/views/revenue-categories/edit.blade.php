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
            @if (auth()->user()?->hasRole('super_admin'))
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-2">Paroisse <span class="text-red-600">*</span></label>
                    <select name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                        @foreach ($paroisses as $p)
                            <option value="{{ $p->id }}" {{ (string) old('paroisse_id', $category->paroisse_id) === (string) $p->id ? 'selected' : '' }}>{{ $p->nom }}</option>
                        @endforeach
                    </select>
                    @error('paroisse_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Si vous changez la paroisse, les types de cette catégorie héritent automatiquement de la même paroisse.</p>
                </div>
            @else
                <div class="md:col-span-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-3 py-2.5 text-sm text-slate-700 dark:text-slate-300">
                    <span class="font-semibold">Paroisse :</span> {{ $category->paroisse?->nom ?? '—' }}
                </div>
            @endif
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
