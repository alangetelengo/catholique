<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Nom complet</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" required>
        @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Paroisse</label>
        <select name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            <option value="">— Aucune (réservé au super administrateur)</option>
            @foreach ($paroisses ?? [] as $paroisse)
                <option value="{{ $paroisse->id }}" {{ (string) old('paroisse_id', $user->paroisse_id) === (string) $paroisse->id ? 'selected' : '' }}>
                    {{ $paroisse->nom }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Obligatoire pour les rôles autres que super administrateur (accès limité aux données de la paroisse).</p>
        @error('paroisse_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Mot de passe {{ $isEdit ? '(laisser vide pour conserver)' : '' }}</label>
        <input type="password" name="password" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" {{ $isEdit ? '' : 'required' }}>
        @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm" {{ $isEdit ? '' : 'required' }}>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Rôle</label>
        <select name="role" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            <option value="">Aucun rôle</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                    {{ $role->libelle_role ?? ucfirst(str_replace('_', ' ', $role->name)) }}
                </option>
            @endforeach
        </select>
        @error('role')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="adventiste-btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('users.index') }}" class="adventiste-btn-secondary">Annuler</a>
</div>
