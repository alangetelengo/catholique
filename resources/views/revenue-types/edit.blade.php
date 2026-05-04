@extends('layouts.app')

@section('title', 'Modifier type de revenu - Catholique')
@section('page-title', 'Modifier type de revenu')
@section('page-title-info', 'Mise à jour des informations du type de recette.')

@section('header-back')
    <x-back-link :href="route('revenue-types.index')" />
@endsection

@section('content')
    <div class="adventiste-card-pro-static p-6">
        <form method="post" action="{{ route('revenue-types.update', $type) }}" class="grid grid-cols-1 md:grid-cols-2 gap-5" id="revenue-type-form-edit">
            @csrf
            @method('put')
            @if (auth()->user()?->hasRole('super_admin'))
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-2">Paroisse <span class="text-red-600">*</span></label>
                    <select name="paroisse_id" id="rt_paroisse" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                        @foreach ($paroisses as $p)
                            <option value="{{ $p->id }}" {{ (string) old('paroisse_id', $type->paroisse_id) === (string) $p->id ? 'selected' : '' }}>{{ $p->nom }}</option>
                        @endforeach
                    </select>
                    @error('paroisse_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">La catégorie doit appartenir à la même paroisse.</p>
                </div>
            @else
                <input type="hidden" name="paroisse_id" id="rt_paroisse_hidden" value="{{ $type->paroisse_id }}">
            @endif
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-2">Catégorie <span class="text-red-600">*</span></label>
                <select name="revenue_category_id" id="rt_category" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
                    <option value="">-- Choisir --</option>
                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            data-paroisse-id="{{ $category->paroisse_id ?? '' }}"
                            {{ (string) old('revenue_category_id', $type->revenue_category_id) === (string) $category->id ? 'selected' : '' }}
                        >
                            @if (auth()->user()?->hasRole('super_admin') && $category->paroisse)
                                {{ $category->paroisse->nom }} — {{ $category->nom }}
                            @else
                                {{ $category->nom }}
                            @endif
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
    @if (auth()->user()?->hasRole('super_admin'))
        @push('scripts')
            <script>
                (function () {
                    var paroisseEl = document.getElementById('rt_paroisse');
                    var catEl = document.getElementById('rt_category');
                    if (!paroisseEl || !catEl) return;

                    function syncCategoryOptions() {
                        var pid = paroisseEl.value;
                        var prevCat = catEl.value;
                        Array.from(catEl.options).forEach(function (opt) {
                            if (!opt.value) {
                                opt.hidden = false;
                                return;
                            }
                            var opid = opt.getAttribute('data-paroisse-id') || '';
                            var show = Boolean(pid) && opid === pid;
                            opt.hidden = !show;
                        });
                        if (prevCat) {
                            var selected = catEl.querySelector('option[value="' + prevCat + '"]');
                            if (!selected || selected.hidden) {
                                catEl.value = '';
                            }
                        }
                    }

                    paroisseEl.addEventListener('change', syncCategoryOptions);
                    syncCategoryOptions();
                })();
            </script>
        @endpush
    @endif
@endsection
