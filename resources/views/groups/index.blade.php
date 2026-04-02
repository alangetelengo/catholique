@extends('layouts.app')

@section('title', 'Groupes')
@section('page-title', 'Groupes et mouvements')

@section('btn-create')
<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('groups.index') }}" class="adventiste-btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Rafraîchir
    </a>
    @can('create_groups')
    <a href="{{ route('groups.create') }}" class="adventiste-btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Nouveau groupe
    </a>
    @endcan
</div>
@endsection

@section('content-container-class', 'max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8')

@section('content')
@php
    $typeLabels = ['chorale' => 'Chorale', 'catéchisme' => 'Catéchisme', 'mouvement' => 'Mouvement', 'autre' => 'Autre'];
@endphp
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden mb-6">
    <form method="GET" action="{{ route('groups.index') }}" class="px-6 py-4 flex flex-wrap items-end gap-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
        <div class="min-w-40">
            <label for="g_type" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Type</label>
            <select name="type" id="g_type" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Tous</option>
                @foreach($typeLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @if(isset($paroisses) && $paroisses->count() > 0)
        <div class="min-w-48">
            <label for="g_paroisse" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Paroisse</label>
            <select name="paroisse_id" id="g_paroisse" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Toutes</option>
                @foreach($paroisses as $paroisse)
                    <option value="{{ $paroisse->id }}" @selected((string) request('paroisse_id') === (string) $paroisse->id)>{{ $paroisse->nom }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="min-w-48 flex-1 max-w-xs">
            <label for="g_q" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Recherche</label>
            <input type="text" name="q" id="g_q" value="{{ request('q') }}" placeholder="Nom, description…"
                class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="adventiste-btn-primary">Filtrer</button>
            @if (request()->hasAny(['type', 'paroisse_id', 'q']))
            <a href="{{ route('groups.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            @endif
        </div>
    </form>

    @if($groups->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Nom</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden sm:table-cell">Type</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden md:table-cell">Responsable</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest hidden lg:table-cell">Paroisse</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @foreach($groups as $group)
                <tr class="hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors">
                    <td class="px-6 py-4 font-medium">
                        {{ $group->nom }}
                        <span class="sm:hidden text-xs text-slate-500 dark:text-slate-400 mt-1 block">{{ $typeLabels[$group->type] ?? $group->type }}</span>
                    </td>
                    <td class="px-6 py-4 hidden sm:table-cell">
                        <span class="inline-flex rounded-lg bg-violet-500/10 text-violet-800 dark:text-violet-200 px-2 py-0.5 text-xs font-medium">{{ $typeLabels[$group->type] ?? $group->type }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 hidden md:table-cell">
                        @if($group->responsable)
                            {{ $group->responsable->prenom }} {{ $group->responsable->nom }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <span class="inline-flex rounded-lg bg-sky-500/10 text-sky-800 dark:text-sky-200 px-2 py-0.5 text-xs font-medium">{{ $group->paroisse?->nom ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group">
                            @can('edit_groups')
                            <x-action-button variant="edit" href="{{ route('groups.edit', $group) }}" />
                            @endcan
                            @can('delete_groups')
                            <x-action-button variant="delete" action="{{ route('groups.destroy', $group) }}" method="DELETE" confirm-message="Supprimer ce groupe ? Les liens avec les membres seront retirés." />
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($groups->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 flex justify-center">
        @include('partials.pagination-fr', ['paginator' => $groups, 'itemLabel' => 'groupes'])
    </div>
    @endif
    @else
    <div class="px-6 py-16 text-center">
        <span class="text-5xl mb-4 block" aria-hidden="true">🙏</span>
        <h3 class="text-slate-600 dark:text-slate-400 font-medium mb-2">Aucun groupe</h3>
        <p class="text-sm text-slate-500 dark:text-slate-500 mb-6">Créez des groupes (chorale, catéchisme, mouvements…).</p>
        @can('create_groups')
        <a href="{{ route('groups.create') }}" class="adventiste-btn-primary inline-flex">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Nouveau groupe
        </a>
        @endcan
    </div>
    @endif
</div>
@endsection
