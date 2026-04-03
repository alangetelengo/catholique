@extends('layouts.app')

@section('title', 'Modifier type de revenu - Catholique')
@section('page-title', 'Modifier type de revenu')
@section('page-title-info', 'Mise à jour des informations du type de recette.')

@section('header-back')
    <x-back-link :href="route('revenue-types.index')" />
@endsection

@section('content')
    <div class="adventiste-card-pro-static p-6">
        <form method="post" action="{{ route('revenue-types.update', $type) }}" class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @csrf
            @method('put')
            <div>
                <label class="block text-sm font-semibold mb-2">Catégorie</label>
                <select name="revenue_category_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                    <option value="">-- Choisir --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) old('revenue_category_id', $type->revenue_category_id) === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->nom }}
                        </option>
                    @endforeach
                </select>
                @error('revenue_category_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Code</label>
                <input type="text" name="code" value="{{ old('code', $type->code) }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                @error('code')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Nom</label>
                <input type="text" name="nom" value="{{ old('nom', $type->nom) }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                @error('nom')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Ordre</label>
                <input type="number" min="0" name="ordre" value="{{ old('ordre', $type->ordre) }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">{{ old('description', $type->description) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" name="actif" value="1" {{ old('actif', $type->actif) ? 'checked' : '' }}>
                    Actif
                </label>
            </div>
            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="adventiste-btn-primary">Mettre à jour</button>
                <a href="{{ route('revenue-types.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
