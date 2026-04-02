@php
    /** @var \App\Models\Event|null $event */
    $event = $event ?? null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="event-titre" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Titre de l'événement</label>
        <input type="text"
               id="event-titre"
               name="titre"
               class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35"
               data-transform="upper"
               value="{{ old('titre', $event?->titre) }}"
               required>
    </div>
    <div>
        <label for="event-type" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Type d'événement</label>
        @php
            $type = old('type', $event?->type ?? 'messe');
        @endphp
        <select name="type" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" id="event-type" required>
            <option value="messe" @selected($type === 'messe')>Messe</option>
            <option value="célébration" @selected($type === 'célébration')>Célébration</option>
            <option value="activité" @selected($type === 'activité')>Activité</option>
        </select>
    </div>
    <div>
        <label for="event-paroisse" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Paroisse</label>
        @if(auth()->check() && auth()->user()->hasRole('super_admin'))
            <select name="paroisse_id" id="event-paroisse" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" required>
                <option value="">—</option>
                @foreach($paroisses as $paroisse)
                    <option value="{{ $paroisse->id }}" @selected((string) old('paroisse_id', $event?->paroisse_id) === (string) $paroisse->id)>
                        {{ $paroisse->nom }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="text" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-900/50 px-3 py-2 text-sm text-slate-600 dark:text-slate-400" value="{{ auth()->user()->paroisse?->nom }}" disabled>
        @endif
    </div>

    <div>
        <label for="event-date" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Date</label>
        <input type="date" name="date_evenement" id="event-date" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35"
               value="{{ old('date_evenement', $event?->date_evenement?->format('Y-m-d')) }}" required>
    </div>
    <div>
        <label for="event-heure" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Heure</label>
        <input type="time" name="heure_evenement" id="event-heure" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35"
               value="{{ old('heure_evenement', optional($event?->heure_evenement)->format('H:i')) }}">
    </div>
    <div>
        <label for="event-lieu" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Lieu</label>
        <input type="text" name="lieu" id="event-lieu" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" value="{{ old('lieu', $event?->lieu) }}">
    </div>

    <div>
        <label for="event-celebrant" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Célébré par</label>
        <select name="celebre_par_id" id="event-celebrant" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
            <option value="">—</option>
            @foreach($celebrants as $celebrant)
                <option value="{{ $celebrant->id }}" @selected((string) old('celebre_par_id', $event?->celebre_par_id) === (string) $celebrant->id)>
                    {{ $celebrant->prenom }} {{ $celebrant->nom }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label for="event-intention" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
            Intention de messe (optionnel)
            <span class="block font-normal text-slate-500 dark:text-slate-500 mt-0.5">Par exemple : Pour la famille X, pour les défunts Y, action de grâce, etc.</span>
        </label>
        <input type="text" name="intention" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" id="event-intention"
               placeholder="Laisser vide si non applicable"
               value="{{ old('intention', $event?->intention) }}">
    </div>

    <div class="md:col-span-2">
        <label for="event-description" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Description</label>
        <textarea name="description" id="event-description" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" rows="4">{{ old('description', $event?->description) }}</textarea>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('event-type');
        const intentionInput = document.getElementById('event-intention');

        if (!typeSelect || !intentionInput) {
            return;
        }

        const toggleIntentionState = () => {
            const isMesse = typeSelect.value === 'messe';
            intentionInput.placeholder = isMesse
                ? 'Ex : Pour la famille X, pour les défunts Y, action de grâce...'
                : 'Laisser vide si non applicable';
        };

        typeSelect.addEventListener('change', toggleIntentionState);
        toggleIntentionState();
    });
</script>
@endpush
