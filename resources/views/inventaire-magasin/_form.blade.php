@php
    $item = $item ?? null;
    $fc = 'w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100';
    $err = 'border-red-500 dark:border-red-500/60 ring-1 ring-red-500/25';
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Nom de l'article <span class="text-red-500">*</span></label>
        <input type="text" name="nom" value="{{ old('nom', $item?->nom) }}" required
               class="{{ $fc }} @error('nom') {{ $err }} @enderror">
        @error('nom')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Catégorie</label>
        <input type="text" name="categorie" value="{{ old('categorie', $item?->categorie) }}" placeholder="Ex. féculents, boissons, conserves"
               class="{{ $fc }} @error('categorie') {{ $err }} @enderror">
        @error('categorie')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Quantité <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" min="0" name="quantite" value="{{ old('quantite', $item?->quantite ?? 0) }}" required
               class="{{ $fc }} @error('quantite') {{ $err }} @enderror">
        @error('quantite')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Unité</label>
        <input type="text" name="unite" value="{{ old('unite', $item?->unite ?? 'unité') }}" placeholder="kg, L, pièce, carton…"
               class="{{ $fc }} @error('unite') {{ $err }} @enderror">
        @error('unite')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Quantité min. (alerte)</label>
        <input type="number" step="0.01" min="0" name="quantite_min_alerte" value="{{ old('quantite_min_alerte', $item?->quantite_min_alerte) }}"
               class="{{ $fc }} @error('quantite_min_alerte') {{ $err }} @enderror">
        @error('quantite_min_alerte')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date de péremption</label>
        <input type="date" name="date_peremption" value="{{ old('date_peremption', $item?->date_peremption?->format('Y-m-d')) }}"
               class="{{ $fc }} @error('date_peremption') {{ $err }} @enderror">
        @error('date_peremption')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Emplacement</label>
        <input type="text" name="emplacement" value="{{ old('emplacement', $item?->emplacement) }}"
               class="{{ $fc }} @error('emplacement') {{ $err }} @enderror">
        @error('emplacement')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
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
        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Notes</label>
        <textarea name="notes" rows="3" class="{{ $fc }} @error('notes') {{ $err }} @enderror">{{ old('notes', $item?->notes) }}</textarea>
        @error('notes')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
</div>
