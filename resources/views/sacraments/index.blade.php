@extends('layouts.app')

@section('title', \App\Models\Sacrament::TYPES[$type] ?? 'Sacrements')
@section('page-title', \App\Models\Sacrament::TYPES[$type] ?? 'Sacrements')

@section('btn-create')
<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('sacraments.index', ['type' => $type]) }}" class="adventiste-btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Rafraîchir
    </a>
    @can(\App\Http\Controllers\SacramentController::TYPE_PERMISSIONS[$type]['create'] ?? 'view_baptisms')
    <a href="{{ route('sacraments.create', ['type' => $type]) }}" class="adventiste-btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Ajouter
    </a>
    @endcan
</div>
@endsection

@section('content-container-class', 'max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8')

@section('content')
{{-- Utilise .adventiste-pill-nav (app.css) pour un contraste actif/inactif fiable --}}
<nav class="adventiste-pill-nav flex flex-wrap gap-2 mb-5" aria-label="Types de sacrements">
    @foreach(\App\Models\Sacrament::TYPES as $key => $label)
        @if(auth()->user()->can(\App\Http\Controllers\SacramentController::TYPE_PERMISSIONS[$key]['view']))
        <a href="{{ route('sacraments.index', ['type' => $key]) }}" class="{{ $type === $key ? 'is-active' : '' }}">
            {{ $label }}
        </a>
        @endif
    @endforeach
</nav>

<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden mb-6">
    <form method="GET" action="{{ route('sacraments.index') }}" class="px-6 py-4 flex flex-wrap items-end gap-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
        <input type="hidden" name="type" value="{{ $type }}">
        @if(auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
        <div class="min-w-48">
            <label for="sc_paroisse" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Paroisse</label>
            <select name="paroisse_id" id="sc_paroisse" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
                <option value="">Toutes</option>
                @foreach($paroisses as $paroisse)
                    <option value="{{ $paroisse->id }}" @selected((string) request('paroisse_id') === (string) $paroisse->id)>{{ $paroisse->nom }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="min-w-36">
            <label for="sc_df" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Date du</label>
            <input type="date" name="date_from" id="sc_df" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" value="{{ request('date_from') }}">
        </div>
        <div class="min-w-36">
            <label for="sc_dt" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Au</label>
            <input type="date" name="date_to" id="sc_dt" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" value="{{ request('date_to') }}">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="adventiste-btn-primary">Filtrer</button>
        </div>
    </form>

    @if($sacraments->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Bénéficiaire / Nom</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Lieu</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Célébrant</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                @foreach($sacraments as $sacrament)
                <tr class="hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ $sacrament->date_celebration?->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 font-medium">{{ $sacrament->beneficiary_name ?: ($sacrament->beneficiary ? $sacrament->beneficiary->prenom . ' ' . $sacrament->beneficiary->nom : '—') }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $sacrament->lieu ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $sacrament->celebrant ? $sacrament->celebrant->prenom . ' ' . $sacrament->celebrant->nom : '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions">
                            @can(\App\Http\Controllers\SacramentController::TYPE_PERMISSIONS[$type]['view'] ?? 'view_baptisms')
                            <x-action-button variant="view" href="{{ route('sacraments.show', $sacrament) }}" />
                            @endcan
                            @can(\App\Http\Controllers\SacramentController::TYPE_PERMISSIONS[$type]['edit'] ?? 'edit_baptisms')
                            <x-action-button variant="edit" href="{{ route('sacraments.edit', $sacrament) }}" />
                            @endcan
                            @can(\App\Http\Controllers\SacramentController::TYPE_PERMISSIONS[$type]['delete'] ?? 'delete_baptisms')
                            <x-action-button variant="delete" action="{{ route('sacraments.destroy', $sacrament) }}" method="DELETE" confirm-message="Supprimer cet enregistrement ?" />
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($sacraments->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 flex justify-center">
        {{ $sacraments->withQueryString()->links() }}
    </div>
    @endif
    @else
    <div class="px-6 py-16 text-center">
        <i class="fas fa-heart text-5xl text-slate-200 dark:text-slate-600 mb-4 block" aria-hidden="true"></i>
        <h3 class="text-slate-600 dark:text-slate-400 font-medium mb-2">Aucun enregistrement trouvé</h3>
        <p class="text-sm text-slate-500 dark:text-slate-500 mb-6">Aucun sacrement de type « {{ \App\Models\Sacrament::TYPES[$type] ?? $type }} » n'a été enregistré pour cette période.</p>
        @can(\App\Http\Controllers\SacramentController::TYPE_PERMISSIONS[$type]['create'] ?? 'view_baptisms')
        <a href="{{ route('sacraments.create', ['type' => $type]) }}" class="adventiste-btn-primary inline-flex">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Ajouter
        </a>
        @endcan
    </div>
    @endif
</div>
@endsection
