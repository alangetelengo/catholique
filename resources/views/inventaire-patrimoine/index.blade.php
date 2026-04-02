@extends('layouts.app')

@section('title', 'Inventaire patrimoine — Catholique')
@section('page-title', 'Inventaire patrimoine')
@section('page-title-info', 'Biens de la paroisse (mobilier, équipement, etc.) avec valeur estimée et localisation.')

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('inventaire-patrimoine.index') }}" class="adventiste-btn-secondary text-sm">Rafraîchir</a>
        <a href="{{ route('inventaire-patrimoine.create') }}" class="adventiste-btn-primary">+ Ajouter un bien</a>
    </div>
@endsection

@section('content')
    @php
        $collection = $items->getCollection();
        $valeurPage = (float) $collection->sum(fn ($i) => (float) ($i->valeur_estimee ?? 0));
        $formatFcfa = static fn (float $v): string => number_format($v, 0, ',', ' ') . ' fcfa';
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Biens (total)</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $items->total() }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Sur cette page</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $items->count() }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-emerald-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Valeur estimée (page)</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $formatFcfa($valeurPage) }}</p>
        </div>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('inventaire-patrimoine.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label for="pat_cat" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Catégorie</label>
                <input id="pat_cat" type="text" name="categorie" value="{{ request('categorie') }}" placeholder="Ex. mobilier, équipement"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="pat_q" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Recherche</label>
                <input id="pat_q" type="text" name="q" value="{{ request('q') }}" placeholder="Nom, référence, description…"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="adventiste-btn-primary">Filtrer</button>
                <a href="{{ route('inventaire-patrimoine.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            </div>
        </form>
        @if (request()->filled('categorie') || request()->filled('q'))
            <div class="mt-4 flex flex-wrap gap-2">
                @if (request('categorie'))
                    <span class="inline-flex items-center rounded-full bg-sky-50 dark:bg-sky-900/30 px-3 py-1 text-xs font-medium text-sky-800 dark:text-sky-200">Catégorie : {{ request('categorie') }}</span>
                @endif
                @if (request('q'))
                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-3 py-1 text-xs font-medium text-slate-700 dark:text-slate-200">Recherche : {{ request('q') }}</span>
                @endif
            </div>
        @endif
    </div>

    @if ($items->count() > 0)
        <div class="adventiste-table-shell">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200">
                            <th class="px-4 py-3 font-semibold">Nom</th>
                            <th class="px-4 py-3 font-semibold">Catégorie</th>
                            <th class="px-4 py-3 font-semibold">Référence</th>
                            <th class="px-4 py-3 font-semibold">Lieu</th>
                            <th class="px-4 py-3 font-semibold">Valeur estimée</th>
                            <th class="px-4 py-3 font-semibold">Paroisse</th>
                            <th class="px-4 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach ($items as $item)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3 font-medium">{{ $item->nom }}</td>
                                <td class="px-4 py-3">{{ $item->categorie ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $item->reference ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $item->lieu ?? '—' }}</td>
                                <td class="px-4 py-3 font-semibold">{{ \App\Helpers\ParoisseConfig::formatMontant($item->valeur_estimee) }}</td>
                                <td class="px-4 py-3">{{ $item->paroisse?->nom ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-action-button
                                            variant="edit"
                                            href="{{ route('inventaire-patrimoine.edit', $item) }}"
                                            custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30"
                                        />
                                        <x-action-button
                                            variant="delete"
                                            action="{{ route('inventaire-patrimoine.destroy', $item) }}"
                                            method="DELETE"
                                            confirm-message="Supprimer ce bien de l’inventaire ?"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $items->withQueryString()->links() }}</div>
    @else
        <div class="adventiste-card-pro-static p-12 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400">
                <i class="fas fa-landmark text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Aucun bien</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Enregistrez le patrimoine de la paroisse.</p>
            <a href="{{ route('inventaire-patrimoine.create') }}" class="adventiste-btn-primary mt-6 inline-flex">+ Ajouter un bien</a>
        </div>
    @endif
@endsection
