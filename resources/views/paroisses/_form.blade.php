{{-- Formulaire paroisse : $paroisse, $members (membres actifs pour curé). --}}
@php
    $gridClass = 'revenue-form-grid revenue-form-grid--three';
    $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
@endphp

<div class="{{ $gridClass }}">
    <div class="md:col-span-3">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Nom de la paroisse <span class="text-red-600">*</span></label>
        <input type="text" name="nom" value="{{ old('nom', $paroisse->nom) }}" class="{{ $field }}" required maxlength="255">
        @error('nom')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Adresse</label>
        <input type="text" name="adresse" value="{{ old('adresse', $paroisse->adresse) }}" class="{{ $field }}" maxlength="255">
        @error('adresse')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Ville</label>
        <input type="text" name="ville" value="{{ old('ville', $paroisse->ville) }}" class="{{ $field }}" maxlength="120">
        @error('ville')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Pays</label>
        <input type="text" name="pays" value="{{ old('pays', $paroisse->pays) }}" class="{{ $field }}" maxlength="120">
        @error('pays')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Téléphone</label>
        <input type="text" name="telephone" value="{{ old('telephone', $paroisse->telephone) }}" class="{{ $field }}" maxlength="50">
        @error('telephone')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">E-mail</label>
        <input type="email" name="email" value="{{ old('email', $paroisse->email) }}" class="{{ $field }}" maxlength="255">
        @error('email')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Code paroisse</label>
        <input type="text" name="code_paroisse" value="{{ old('code_paroisse', $paroisse->code_paroisse) }}" class="{{ $field }}" maxlength="100" placeholder="Unique si renseigné">
        @error('code_paroisse')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Diocèse</label>
        <input type="text" name="diocèse" value="{{ old('diocèse', $paroisse->diocèse) }}" class="{{ $field }}" maxlength="255">
        @error('diocèse')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Curé (membre)</label>
        <select name="curé_id" class="{{ $field }}">
            <option value="">— Non renseigné —</option>
            @foreach ($members as $m)
                <option value="{{ $m->id }}" {{ (string) old('curé_id', $paroisse->curé_id) === (string) $m->id ? 'selected' : '' }}>
                    {{ $m->prenom }} {{ $m->nom }}
                </option>
            @endforeach
        </select>
        @error('curé_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-3">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Description</label>
        <textarea name="description" rows="3" class="{{ $field }}">{{ old('description', $paroisse->description) }}</textarea>
        @error('description')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-3 flex items-center gap-2">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-800 dark:text-slate-200">
            <input type="checkbox" name="actif" value="1" {{ old('actif', $paroisse->actif) ? 'checked' : '' }}>
            Paroisse active
        </label>
        @error('actif')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
</div>
