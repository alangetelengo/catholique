@php
    $item = $item ?? null;
    $fc = 'w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100';
    $err = 'border-red-500 dark:border-red-500/60 ring-1 ring-red-500/25';
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Nom du bien <span class="text-red-500">*</span></label>
        <input type="text" name="nom" value="{{ old('nom', $item?->nom) }}" required
               class="{{ $fc }} @error('nom') {{ $err }} @enderror">
        @error('nom')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Catégorie</label>
        <input type="text" name="categorie" value="{{ old('categorie', $item?->categorie) }}" placeholder="Ex. mobilier, équipement, véhicule"
               class="{{ $fc }} @error('categorie') {{ $err }} @enderror">
        @error('categorie')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Référence</label>
        <input type="text" name="reference" value="{{ old('reference', $item?->reference) }}"
               class="{{ $fc }} @error('reference') {{ $err }} @enderror">
        @error('reference')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Lieu</label>
        <input type="text" name="lieu" value="{{ old('lieu', $item?->lieu) }}" placeholder="Où se trouve le bien"
               class="{{ $fc }} @error('lieu') {{ $err }} @enderror">
        @error('lieu')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Valeur estimée ({{ \App\Helpers\ParoisseConfig::get(null, 'monnaie', 'FCFA') }})</label>
        <input type="number" step="0.01" min="0" name="valeur_estimee" value="{{ old('valeur_estimee', $item?->valeur_estimee) }}"
               class="{{ $fc }} @error('valeur_estimee') {{ $err }} @enderror">
        @error('valeur_estimee')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date d'acquisition</label>
        <input type="date" name="date_acquisition" value="{{ old('date_acquisition', $item?->date_acquisition?->format('Y-m-d')) }}"
               class="{{ $fc }} @error('date_acquisition') {{ $err }} @enderror">
        @error('date_acquisition')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">État</label>
        <input type="text" name="etat" value="{{ old('etat', $item?->etat) }}" placeholder="Ex. bon, moyen, à réparer"
               class="{{ $fc }} @error('etat') {{ $err }} @enderror">
        @error('etat')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    @if (isset($paroisses) && $paroisses->count() > 0)
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse</label>
            <select name="paroisse_id" class="{{ $fc }} @error('paroisse_id') {{ $err }} @enderror">
                @foreach ($paroisses as $p)
                    <option value="{{ $p->id }}" @selected((string) old('paroisse_id', $item?->paroisse_id) === (string) $p->id)>{{ $p->nom }}</option>
                @endforeach
            </select>
            @error('paroisse_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
    @endif
    <div class="md:col-span-2">
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Description</label>
        <textarea name="description" rows="3" class="{{ $fc }} @error('description') {{ $err }} @enderror">{{ old('description', $item?->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Notes</label>
        <textarea name="notes" rows="2" class="{{ $fc }} @error('notes') {{ $err }} @enderror">{{ old('notes', $item?->notes) }}</textarea>
        @error('notes')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
</div>
