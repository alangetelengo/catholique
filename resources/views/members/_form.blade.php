@php
    /** @var \App\Models\Member|null $member */
    $member = $member ?? null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="prenom">Prénom</label>
        <input type="text" name="prenom" id="prenom" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" data-transform="title" value="{{ old('prenom', $member?->prenom) }}" required>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="nom">Nom</label>
        <input type="text" name="nom" id="nom" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" data-transform="upper" value="{{ old('nom', $member?->nom) }}" required>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="date_naissance">Date de naissance</label>
        <input type="date" name="date_naissance" id="date_naissance" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" value="{{ old('date_naissance', $member?->date_naissance?->format('Y-m-d')) }}">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="sexe">Sexe</label>
        @php
            $sexe = old('sexe', $member?->sexe ?? 'M');
        @endphp
        <select name="sexe" id="sexe" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" required>
            <option value="M" @selected($sexe === 'M')>Masculin</option>
            <option value="F" @selected($sexe === 'F')>Féminin</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="telephone">Téléphone</label>
        <input type="text" name="telephone" id="telephone" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" data-input="phone" value="{{ old('telephone', $member?->telephone) }}">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="email">Email</label>
        <input type="email" name="email" id="email" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" data-transform="lower" value="{{ old('email', $member?->email) }}">
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="adresse">Adresse</label>
        <input type="text" name="adresse" id="adresse" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" value="{{ old('adresse', $member?->adresse) }}">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="statut">Statut</label>
        <select name="statut" id="statut" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" required>
            @foreach(['actif' => 'Actif', 'inactif' => 'Inactif', 'décédé' => 'Décédé'] as $value => $label)
                <option value="{{ $value }}" @selected(old('statut', $member?->statut ?? 'actif') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    @if(auth()->check() && auth()->user()->hasRole('super_admin'))
        <div class="md:col-span-1">
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="paroisse_id">Paroisse</label>
            <select name="paroisse_id" id="paroisse_id" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" required>
                <option value="">—</option>
                @foreach($paroisses as $paroisse)
                    <option value="{{ $paroisse->id }}" @selected((string) old('paroisse_id', $member?->paroisse_id) === (string) $paroisse->id)>
                        {{ $paroisse->nom }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="notes">Notes</label>
        <textarea name="notes" id="notes" rows="4" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">{{ old('notes', $member?->notes) }}</textarea>
    </div>
</div>
