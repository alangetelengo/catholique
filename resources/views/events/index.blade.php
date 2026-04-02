@extends('layouts.app')

@section('title', 'Événements')
@section('page-title', 'Gestion des événements')

@section('btn-create')
<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('events.index') }}" class="adventiste-btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Rafraîchir
    </a>
    @can('create_events')
    <a href="{{ route('events.create') }}" class="adventiste-btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Ajouter un événement
    </a>
    @endcan
</div>
@endsection

@section('content-container-class', 'max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8')

@section('content')
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden mb-6">
    <form method="GET" action="{{ route('events.index') }}" class="px-6 py-4 flex flex-wrap items-end gap-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
        <div class="min-w-36">
            <label for="ev_type" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Type</label>
            <select name="type" id="ev_type" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Tous</option>
                @foreach(['messe' => 'Messe', 'célébration' => 'Célébration', 'activité' => 'Activité'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <label for="ev_df" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Date du</label>
            <input type="date" name="date_from" id="ev_df" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" value="{{ request('date_from') }}">
        </div>
        <div class="min-w-36">
            <label for="ev_dt" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Au</label>
            <input type="date" name="date_to" id="ev_dt" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" value="{{ request('date_to') }}">
        </div>
        @if(auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
        <div class="min-w-48">
            <label for="ev_paroisse" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Paroisse</label>
            <select name="paroisse_id" id="ev_paroisse" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Toutes</option>
                @foreach($paroisses as $paroisse)
                    <option value="{{ $paroisse->id }}" @selected((string) request('paroisse_id') === (string) $paroisse->id)>{{ $paroisse->nom }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="flex gap-2">
            <button type="submit" class="adventiste-btn-primary">Filtrer</button>
        </div>
    </form>

    <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-700/80 flex justify-end">
        <div class="w-full max-w-xs">
            <label for="events-local-search" class="sr-only">Recherche locale</label>
            <input type="text" id="events-local-search" placeholder="Titre, lieu, célébrant..." class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
        </div>
    </div>

    @if($events->count() > 0)
    <div class="overflow-x-auto">
        <table id="events-table" class="w-full text-sm">
            <thead>
                <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Titre</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Type</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Célébré par</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @foreach($events as $event)
                <tr class="hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4">
                        <span class="block font-semibold">{{ $event->date_evenement?->format('d/m/Y') ?? '—' }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ optional($event->heure_evenement)->format('H:i') }}</span>
                    </td>
                    <td class="px-6 py-4 font-medium">{{ $event->titre }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex rounded-lg bg-emerald-500/10 text-emerald-800 dark:text-emerald-200 px-2 py-0.5 text-xs font-medium">{{ ucfirst($event->type) }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ optional($event->celebrePar)->prenom }} {{ optional($event->celebrePar)->nom }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions">
                            <x-action-button variant="view" href="{{ route('events.show', $event) }}" />
                            @can('edit_events')
                            <x-action-button variant="edit" href="{{ route('events.edit', $event) }}" />
                            @endcan
                            @can('delete_events')
                            <x-action-button variant="delete" action="{{ route('events.destroy', $event) }}" method="DELETE" confirm-message="Êtes-vous sûr de vouloir supprimer cet événement ?" />
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($events->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 flex justify-center">
        {{ $events->withQueryString()->links() }}
    </div>
    @endif
    @else
    <div class="px-6 py-16 text-center">
        <i class="fas fa-calendar-alt text-5xl text-slate-200 dark:text-slate-600 mb-4 block" aria-hidden="true"></i>
        <h3 class="text-slate-600 dark:text-slate-400 font-medium mb-2">Aucun événement trouvé</h3>
        <p class="text-sm text-slate-500 dark:text-slate-500 mb-6">Commencez par ajouter votre premier événement (messe, célébration, activité...).</p>
        @can('create_events')
        <a href="{{ route('events.create') }}" class="adventiste-btn-primary inline-flex">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Ajouter un événement
        </a>
        @endcan
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('events-table');
        const searchInput = document.getElementById('events-local-search');
        if (!table || !searchInput) return;

        const rows = Array.from(table.querySelectorAll('tbody tr'));

        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            rows.forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    });
</script>
@endpush
