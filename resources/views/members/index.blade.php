@extends('layouts.app')

@section('title', 'Membres')
@section('page-title', 'Gestion des membres')

@section('btn-create')
<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('members.index') }}" class="adventiste-btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Rafraîchir
    </a>
    @can('create_members')
    <a href="{{ route('members.create') }}" class="adventiste-btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Ajouter un membre
    </a>
    @endcan
</div>
@endsection

@section('content-container-class', 'max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8')

@section('content')
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden mb-6">
    <form method="GET" action="{{ route('members.index') }}" class="px-6 py-4 flex flex-wrap items-end gap-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
        <div class="min-w-40">
            <label for="f_statut" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Statut</label>
            <select name="statut" id="f_statut" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Tous</option>
                @foreach(['actif' => 'Actif', 'inactif' => 'Inactif', 'décédé' => 'Décédé'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('statut') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <label for="f_sexe" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Sexe</label>
            <select name="sexe" id="f_sexe" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Tous</option>
                <option value="M" @selected(request('sexe') === 'M')>Masculin</option>
                <option value="F" @selected(request('sexe') === 'F')>Féminin</option>
            </select>
        </div>
        @if(isset($paroisses) && $paroisses->count() > 0)
        <div class="min-w-48">
            <label for="f_paroisse" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Paroisse</label>
            <select name="paroisse_id" id="f_paroisse" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Toutes</option>
                @foreach($paroisses as $paroisse)
                    <option value="{{ $paroisse->id }}" @selected((string) request('paroisse_id') === (string) $paroisse->id)>{{ $paroisse->nom }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="flex gap-2">
            <button type="submit" class="adventiste-btn-primary">Filtrer</button>
            @if (request()->hasAny(['statut', 'sexe', 'paroisse_id']))
            <a href="{{ route('members.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-700/80 flex justify-end">
        <div class="w-full max-w-xs">
            <label for="members-local-search" class="sr-only">Filtrer la liste</label>
            <input type="text" id="members-local-search" placeholder="Filtrer les membres visibles…" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
        </div>
    </div>

    @if($members->count() > 0)
    <div class="overflow-x-auto">
        <table id="members-table" class="w-full text-sm">
            <thead>
                <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Nom</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden sm:table-cell">Sexe</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden md:table-cell">Téléphone</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden lg:table-cell">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Statut</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden md:table-cell">Paroisse</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @foreach($members as $member)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">
                        <div class="flex items-center gap-3">
                            <span class="shrink-0 w-10 h-10 rounded-full bg-emerald-500/15 dark:bg-emerald-500/25 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-sm font-bold">{{ strtoupper(substr($member->prenom, 0, 1)) }}</span>
                            <span>{{ $member->prenom }} {{ $member->nom }}</span>
                        </div>
                        <span class="sm:hidden text-xs text-slate-500 dark:text-slate-400 mt-1 block">{{ $member->sexe === 'F' ? 'Féminin' : 'Masculin' }} · {{ $member->telephone ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden sm:table-cell">{{ $member->sexe === 'F' ? 'Féminin' : 'Masculin' }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden md:table-cell">{{ $member->telephone ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden lg:table-cell">{{ $member->email ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @php
                            $badgeRing = match ($member->statut) {
                                'actif' => 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-200 ring-emerald-500/25',
                                'inactif' => 'bg-amber-500/15 text-amber-900 dark:text-amber-200 ring-amber-500/25',
                                default => 'bg-rose-500/15 text-rose-800 dark:text-rose-200 ring-rose-500/25',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $badgeRing }}">{{ ucfirst($member->statut) }}</span>
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell">
                        <span class="inline-flex rounded-lg bg-sky-500/10 text-sky-800 dark:text-sky-200 px-2 py-0.5 text-xs font-medium">{{ $member->paroisse?->nom ?? 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions">
                            <x-action-button variant="view" href="{{ route('members.show', $member) }}" />
                            @can('edit_members')
                            <x-action-button variant="edit" href="{{ route('members.edit', $member) }}" />
                            @endcan
                            @can('delete_members')
                            <x-action-button variant="delete" action="{{ route('members.destroy', $member) }}" method="DELETE" confirm-message="Supprimer définitivement ce membre ?" />
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($members->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 flex justify-center">
        {{ $members->withQueryString()->links() }}
    </div>
    @endif
    @else
    <div class="px-6 py-16 text-center">
        <i class="fas fa-user-friends text-5xl text-slate-200 dark:text-slate-600 mb-4 block" aria-hidden="true"></i>
        <h3 class="text-slate-600 dark:text-slate-400 font-medium mb-2">Aucun membre trouvé</h3>
        <p class="text-sm text-slate-500 dark:text-slate-500 mb-6">Commencez par ajouter votre premier membre de la paroisse.</p>
        @can('create_members')
        <a href="{{ route('members.create') }}" class="adventiste-btn-primary inline-flex">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Ajouter un membre
        </a>
        @endcan
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('members-table');
        const searchInput = document.getElementById('members-local-search');
        if (!table || !searchInput) return;
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            rows.forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    });
</script>
@endpush
