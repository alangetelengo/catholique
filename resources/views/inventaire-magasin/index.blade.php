@extends('layouts.app')

@section('title', 'Inventaire magasin — Catholique')
@section('page-title', 'Inventaire produits alimentaires')
@section('page-title-info', 'Stocks du magasin (denrées), filtres par catégorie et recherche. Les lignes en alerte sont signalées.')

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('inventaire-magasin.index') }}" class="adventiste-btn-secondary text-sm">Rafraîchir</a>
        <a href="{{ route('inventaire-magasin.create') }}" class="adventiste-btn-primary">+ Ajouter un article</a>
    </div>
@endsection

@section('content')
    @php
        $collection = $items->getCollection();
        $alertesPage = $collection->filter(fn ($i) => $i->isAlerte())->count();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Articles (total)</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $items->total() }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Sur cette page</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $items->count() }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4 border-t-4 border-t-amber-500">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Alertes stock (page)</p>
            <p class="mt-1 text-2xl font-bold text-amber-700 dark:text-amber-400">{{ $alertesPage }}</p>
        </div>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('inventaire-magasin.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label for="mag_cat" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Catégorie</label>
                <input id="mag_cat" type="text" name="categorie" value="{{ request('categorie') }}" placeholder="Ex. féculents, boissons"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="mag_q" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Recherche</label>
                <input id="mag_q" type="text" name="q" value="{{ request('q') }}" placeholder="Nom, notes…"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="adventiste-btn-primary">Filtrer</button>
                <a href="{{ route('inventaire-magasin.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
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
                            <th class="px-4 py-3 font-semibold">Quantité</th>
                            <th class="px-4 py-3 font-semibold">Unité</th>
                            <th class="px-4 py-3 font-semibold">Péremption</th>
                            <th class="px-4 py-3 font-semibold">Paroisse</th>
                            <th class="px-4 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach ($items as $item)
                            <tr class="text-slate-700 dark:text-slate-200 {{ $item->isAlerte() ? 'bg-amber-50/60 dark:bg-amber-950/25' : '' }}">
                                <td class="px-4 py-3 font-medium">
                                    {{ $item->nom }}
                                    @if ($item->isAlerte())
                                        <span class="ms-2 inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/40 px-2 py-0.5 text-xs font-semibold text-red-800 dark:text-red-200">Alerte</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $item->categorie ?? '—' }}</td>
                                <td class="px-4 py-3">{{ number_format((float) $item->quantite, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3">{{ $item->unite ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $item->date_peremption?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $item->paroisse?->nom ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-action-button
                                            variant="edit"
                                            href="{{ route('inventaire-magasin.edit', $item) }}"
                                            custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30"
                                        />
                                        <x-action-button
                                            variant="delete"
                                            action="{{ route('inventaire-magasin.destroy', $item) }}"
                                            method="DELETE"
                                            confirm-message="Supprimer cet article ?"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @include('partials.pagination-fr', ['paginator' => $items, 'itemLabel' => 'articles'])
    @else
        <div class="adventiste-card-pro-static p-12 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400">
                <i class="fas fa-boxes-stacked text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Aucun article</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Ajoutez un premier produit alimentaire à l’inventaire.</p>
            <a href="{{ route('inventaire-magasin.create') }}" class="adventiste-btn-primary mt-6 inline-flex">+ Ajouter un article</a>
        </div>
    @endif
@endsection
