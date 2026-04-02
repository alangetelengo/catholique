@php
    $typeOptions = [
        'chorale' => 'Chorale',
        'catéchisme' => 'Catéchisme',
        'mouvement' => 'Mouvement',
        'autre' => 'Autre',
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="group_nom" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nom du groupe <span class="text-rose-500">*</span></label>
        <input type="text" name="nom" id="group_nom" required maxlength="255"
            class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 @error('nom') border-rose-500 @enderror"
            value="{{ old('nom', $group->nom) }}" placeholder="Ex. Chorale Sainte-Marie">
        @error('nom')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="group_type" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Type <span class="text-rose-500">*</span></label>
        <select name="type" id="group_type" required
            class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 @error('type') border-rose-500 @enderror">
            @foreach($typeOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $group->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
    </div>

    @if(isset($paroisses) && $paroisses->count() > 1)
    <div>
        <label for="group_paroisse" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Paroisse</label>
        <select name="paroisse_id" id="group_paroisse"
            class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 @error('paroisse_id') border-rose-500 @enderror">
            <option value="">— Choisir —</option>
            @foreach($paroisses as $paroisse)
                <option value="{{ $paroisse->id }}" @selected((string) old('paroisse_id', $group->paroisse_id) === (string) $paroisse->id)>{{ $paroisse->nom }}</option>
            @endforeach
        </select>
        @error('paroisse_id')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
    </div>
    @elseif(isset($paroisses) && $paroisses->count() === 1)
        <input type="hidden" name="paroisse_id" value="{{ $paroisses->first()->id }}">
    @endif

    <div class="md:col-span-2">
        <label for="group_responsable" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Responsable (membre)</label>
        <select name="responsable_id" id="group_responsable"
            class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 @error('responsable_id') border-rose-500 @enderror">
            <option value="">— Aucun —</option>
            @foreach($responsables as $membre)
                <option value="{{ $membre->id }}" @selected((string) old('responsable_id', $group->responsable_id) === (string) $membre->id)>
                    {{ $membre->prenom }} {{ $membre->nom }}
                </option>
            @endforeach
        </select>
        @error('responsable_id')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label for="group_description" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Description</label>
        <textarea name="description" id="group_description" rows="4"
            class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 @error('description') border-rose-500 @enderror"
            placeholder="Informations complémentaires…">{{ old('description', $group->description) }}</textarea>
        @error('description')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
    </div>
</div>
