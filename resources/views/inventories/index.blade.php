@extends('layouts.app')

@section('title', 'Inventaires - Catholique')
@section('page-title', 'Inventaires')
@section('page-title-info', 'Biens et matériel de la paroisse : suivi par catégorie, emplacement et état.')

@section('btn-create')
    <a href="{{ route('inventories.create') }}" class="adventiste-btn-primary">+ Nouvel article</a>
@endsection

@section('content')
    @php
        $categories = [
            'mobilier_liturgique' => 'Mobilier liturgique',
            'mobilier' => 'Mobilier',
            'materiel_technique' => 'Matériel technique',
            'consommable' => 'Consommable',
            'autre' => 'Autre',
        ];
        $etats = [
            'bon' => 'Bon état',
            'usage' => 'Usagé',
            'a_reparer' => 'À réparer',
            'hors_service' => 'Hors service',
        ];
        $formatFcfa = static fn (?float $v): string => $v === null ? '—' : number_format($v, 0, ',', ' ') . ' FCFA';
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Articles (page)</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $inventories->count() }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total lignes (filtre)</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $inventories->total() }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Quantité (page)</p>
            <p class="mt-1 text-2xl font-bold text-indigo-700 dark:text-indigo-400">{{ number_format((float) $inventories->getCollection()->sum('quantite'), 2, ',', ' ') }}</p>
        </div>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Recherche (désignation, réf., emplacement…)"
                class="md:col-span-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm"
            >
            @if($paroisses->isNotEmpty())
                <select name="paroisse_id" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                    <option value="">Toutes paroisses</option>
                    @foreach ($paroisses as $p)
                        <option value="{{ $p->id }}" {{ (string) request('paroisse_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->nom }}</option>
                    @endforeach
                </select>
            @endif
            <select name="categorie" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                <option value="">Toutes catégories</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" {{ request('categorie') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="etat" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                <option value="">Tous états</option>
                @foreach ($etats as $key => $label)
                    <option value="{{ $key }}" {{ request('etat') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="md:col-span-6 flex items-center gap-2">
                <button class="adventiste-btn-primary" type="submit">Filtrer</button>
                <a href="{{ route('inventories.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="adventiste-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200">
                        @if(auth()->user()?->hasRole('super_admin'))
                            <th class="px-4 py-3 font-semibold">Paroisse</th>
                        @endif
                        <th class="px-4 py-3 font-semibold">Désignation</th>
                        <th class="px-4 py-3 font-semibold">Réf.</th>
                        <th class="px-4 py-3 font-semibold">Catégorie</th>
                        <th class="px-4 py-3 font-semibold">Qté</th>
                        <th class="px-4 py-3 font-semibold">Emplacement</th>
                        <th class="px-4 py-3 font-semibold">État</th>
                        <th class="px-4 py-3 font-semibold">Valeur est.</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse ($inventories as $row)
                        <tr class="text-slate-700 dark:text-slate-200">
                            @if(auth()->user()?->hasRole('super_admin'))
                                <td class="px-4 py-3">{{ $row->paroisse?->nom ?? '—' }}</td>
                            @endif
                            <td class="px-4 py-3 font-medium">{{ $row->designation }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $row->reference_inventaire ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $categories[$row->categorie] ?? $row->categorie }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $row->quantite, 2, ',', ' ') }} {{ $row->unite }}</td>
                            <td class="px-4 py-3">{{ $row->emplacement ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                                    {{ $etats[$row->etat] ?? $row->etat }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $formatFcfa($row->valeur_estimee !== null ? (float) $row->valeur_estimee : null) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-action-button
                                        variant="edit"
                                        href="{{ route('inventories.edit', $row) }}"
                                        custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30"
                                    />
                                    <x-action-button
                                        variant="delete"
                                        action="{{ route('inventories.destroy', $row) }}"
                                        method="DELETE"
                                        confirm-message="Retirer cet article de l’inventaire ?"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->hasRole('super_admin') ? 9 : 8 }}" class="px-4 py-8 text-center text-slate-500">Aucun article trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('partials.pagination-fr', ['paginator' => $inventories, 'itemLabel' => 'articles'])
@endsection
