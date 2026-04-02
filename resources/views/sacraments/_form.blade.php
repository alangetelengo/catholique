@php
    $sacrament = $sacrament ?? null;
    $isEdit = isset($sacrament) && $sacrament->exists;
@endphp
<input type="hidden" name="type" value="{{ $type }}">

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @if(auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
        <div>
            <label for="sacrament-paroisse-id" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Paroisse</label>
            <select name="paroisse_id" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" id="sacrament-paroisse-id" @if($isEdit) disabled @endif required>
                @foreach($paroisses as $paroisse)
                    <option value="{{ $paroisse->id }}" @selected((string) old('paroisse_id', $sacrament?->paroisse_id ?? $paroisseId) === (string) $paroisse->id)>{{ $paroisse->nom }}</option>
                @endforeach
            </select>
            @if($isEdit)<input type="hidden" name="paroisse_id" value="{{ $sacrament->paroisse_id }}">@endif
        </div>
    @endif
    <div>
        <label for="sac-date" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Date de célébration</label>
        <input type="date" name="date_celebration" id="sac-date" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" value="{{ old('date_celebration', $sacrament?->date_celebration?->format('Y-m-d')) }}" required>
    </div>
    <div>
        <label for="sac-lieu" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Lieu</label>
        <input type="text" name="lieu" id="sac-lieu" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" value="{{ old('lieu', $sacrament?->lieu) }}" placeholder="Ex : Église Saint-X">
    </div>
    <div>
        <label for="sac-celebrant" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Célébrant</label>
        <select name="celebrant_id" id="sac-celebrant" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
            <option value="">—</option>
            @foreach($celebrants as $c)
                <option value="{{ $c->id }}" @selected((string) old('celebrant_id', $sacrament?->celebrant_id) === (string) $c->id)>{{ $c->prenom }} {{ $c->nom }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="sac-ben-name" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nom du bénéficiaire (si non membre)</label>
        <input type="text" name="beneficiary_name" id="sac-ben-name" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" value="{{ old('beneficiary_name', $sacrament?->beneficiary_name) }}" placeholder="Nom complet">
    </div>
    @if(isset($members) && $members->count() > 0)
    <div>
        <label for="sac-ben-id" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Membre (bénéficiaire)</label>
        <select name="beneficiary_id" id="sac-ben-id" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
            <option value="">—</option>
            @foreach($members as $m)
                <option value="{{ $m->id }}" @selected((string) old('beneficiary_id', $sacrament?->beneficiary_id) === (string) $m->id)>{{ $m->prenom }} {{ $m->nom }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="md:col-span-2">
        <label for="sac-notes" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Notes</label>
        <textarea name="notes" id="sac-notes" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" rows="3">{{ old('notes', $sacrament?->notes) }}</textarea>
    </div>
</div>
