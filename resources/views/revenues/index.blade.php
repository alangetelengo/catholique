@extends('layouts.app')

@section('title', 'Revenus - Catholique')
@section('page-title', 'Revenus')
@section('page-title-info', 'Gestion des recettes de la paroisse avec filtres par période, catégorie et type.')

@section('btn-create')
    <a href="{{ route('revenues.create') }}" class="adventiste-btn-primary">+ Nouvelle recette</a>
@endsection

@section('content')
    @php
        $countPage = $revenues->count();
        $formatFcfa = static fn (float $value): string => number_format($value, 0, ',', ' ') . ' fcfa';
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Recettes (page)</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $countPage }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total des recettes</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $formatFcfa($totalMontantRecettes) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Dernière recette</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">
                @if ($montantDerniereRecette !== null)
                    {{ $formatFcfa($montantDerniereRecette) }}
                @else
                    <span class="text-slate-400 dark:text-slate-500">—</span>
                @endif
            </p>
        </div>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-5">
        <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Recherche (notes, référence, donateur)"
                class="md:col-span-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm"
            >
            <select name="categorie" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                <option value="">Toutes catégories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->code }}" {{ request('categorie') === $category->code ? 'selected' : '' }}>{{ $category->nom }}</option>
                @endforeach
            </select>
            <div>
                <label for="date_from" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date début</label>
                <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label for="date_to" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date fin</label>
                <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            </div>
            <div class="md:col-span-5 flex items-center gap-2">
                <button class="adventiste-btn-primary" type="submit">Filtrer</button>
                <a href="{{ route('revenues.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            </div>
        </form>
        @if (request()->filled('q') || request()->filled('categorie') || request()->filled('date_from') || request()->filled('date_to'))
            <div class="mt-4 flex flex-wrap gap-2">
                @if (request('q'))
                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-3 py-1 text-xs font-medium text-slate-700 dark:text-slate-200">Recherche: {{ request('q') }}</span>
                @endif
                @if (request('categorie'))
                    <span class="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-300">Catégorie: {{ request('categorie') }}</span>
                @endif
                @if (request('date_from'))
                    <span class="inline-flex items-center rounded-full bg-sky-50 dark:bg-sky-900/30 px-3 py-1 text-xs font-medium text-sky-700 dark:text-sky-300">De: {{ request('date_from') }}</span>
                @endif
                @if (request('date_to'))
                    <span class="inline-flex items-center rounded-full bg-sky-50 dark:bg-sky-900/30 px-3 py-1 text-xs font-medium text-sky-700 dark:text-sky-300">À: {{ request('date_to') }}</span>
                @endif
            </div>
        @endif
    </div>

    <div class="adventiste-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200">
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Catégorie</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Montant</th>
                        <th class="px-4 py-3 font-semibold">Paiement</th>
                        <th class="px-4 py-3 font-semibold">Référence</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse ($revenues as $revenue)
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-4 py-3">{{ optional($revenue->date_recette)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $revenue->category?->nom ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $revenue->type?->nom ?? '-' }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $formatFcfa((float) $revenue->montant) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                                    {{ str_replace('_', ' ', ucfirst($revenue->methode_paiement)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $revenue->reference_paiement ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <x-action-button
                                        variant="edit"
                                        href="{{ route('revenues.edit', $revenue) }}"
                                        custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30"
                                    />
                                    <x-action-button
                                        variant="delete"
                                        action="{{ route('revenues.destroy', $revenue) }}"
                                        method="DELETE"
                                        confirm-message="Supprimer cette recette ?"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">Aucune recette trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        @include('partials.pagination-fr', ['paginator' => $revenues, 'itemLabel' => 'recettes'])
    </div>
@endsection
