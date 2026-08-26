@extends('layouts.app')

@section('title', 'Dépenses - Catholique')
@section('page-title', 'Dépenses')
@section('page-title-info', 'Gestion des charges financées par les caisses, avec filtres par période, type et caisse.')
@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('btn-create')
    <a href="{{ route('expenses.create') }}" class="adventiste-btn-primary">+ Nouvelle dépense</a>
@endsection

@section('content')
    @php
        $formatFcfa = static fn (float $value): string => number_format($value, 0, ',', ' ') . ' fcfa';
        $caisses = $caisses ?? collect();
        $expenseTypes = $expenseTypes ?? collect();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total des dépenses</p>
            <p class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-400">{{ $formatFcfa($totalMontantDepenses) }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Dernière dépense</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">
                @if ($montantDerniereDepense !== null)
                    {{ $formatFcfa($montantDerniereDepense) }}
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
                placeholder="Recherche (libellé, notes, fournisseur)"
                class="md:col-span-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm"
            >
            <select name="expense_type_id" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                <option value="">Tous types de dépense</option>
                @foreach ($expenseTypes as $expenseType)
                    <option value="{{ $expenseType->id }}" {{ request('expense_type_id') == $expenseType->id ? 'selected' : '' }}>
                        {{ $expenseType->nom }}
                    </option>
                @endforeach
            </select>
            <select name="caisse_id" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                <option value="">Toutes caisses</option>
                @foreach ($caisses as $caisse)
                    <option value="{{ $caisse->id }}" {{ request('caisse_id') == $caisse->id ? 'selected' : '' }}>
                        {{ $caisse->nom }}
                    </option>
                @endforeach
            </select>
            <div class="grid grid-cols-2 gap-3 md:contents">
                <div>
                    <label for="date_from" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date début</label>
                    <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date fin</label>
                    <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                </div>
            </div>
            <div class="md:col-span-5 flex items-center gap-2">
                <button class="adventiste-btn-primary" type="submit">Filtrer</button>
                <a href="{{ route('expenses.index') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            </div>
        </form>

        @if (request()->filled('q') || request()->filled('expense_type_id') || request()->filled('caisse_id') || request()->filled('date_from') || request()->filled('date_to'))
            <div class="mt-4 flex flex-wrap gap-2">
                @if (request('q'))
                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-3 py-1 text-xs font-medium text-slate-700 dark:text-slate-200">Recherche: {{ request('q') }}</span>
                @endif
                @if (request('expense_type_id'))
                    @php
                        $selectedExpenseType = $expenseTypes->firstWhere('id', (int) request('expense_type_id'));
                    @endphp
                    @if ($selectedExpenseType)
                        <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-900/30 px-3 py-1 text-xs font-medium text-amber-800 dark:text-amber-200">Type dépense: {{ $selectedExpenseType->nom }}</span>
                    @endif
                @endif
                @if (request('caisse_id'))
                    @php
                        $selectedCaisse = $caisses->firstWhere('id', (int) request('caisse_id'));
                    @endphp
                    @if ($selectedCaisse)
                        <span class="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-900/30 px-3 py-1 text-xs font-medium text-emerald-800 dark:text-emerald-200">Caisse: {{ $selectedCaisse->nom }}</span>
                    @endif
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

    <div class="adventiste-table-shell min-w-0 w-full">
        <table class="w-full min-w-[48rem] text-sm">
            <thead>
                <tr class="text-left text-slate-700 dark:text-slate-200">
                    <th class="px-3 py-3 font-semibold whitespace-nowrap">Date</th>
                    <th class="px-3 py-3 font-semibold">Libellé</th>
                    <th class="px-3 py-3 font-semibold whitespace-nowrap">Type</th>
                    <th class="px-3 py-3 font-semibold">Caisses</th>
                    <th class="px-3 py-3 font-semibold whitespace-nowrap">Montant</th>
                    <th class="px-3 py-3 font-semibold text-right sticky right-0 z-20 bg-slate-100 dark:bg-slate-900 shadow-[-6px_0_8px_-6px_rgba(15,23,42,0.18)]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                @forelse ($expenses as $expense)
                    <tr class="text-slate-700 dark:text-slate-200 group">
                        <td class="px-3 py-3 whitespace-nowrap">{{ optional($expense->date_depense)->format('d/m/Y') }}</td>
                        <td class="px-3 py-3 max-w-[14rem]">
                            <span class="line-clamp-2" title="{{ $expense->libelle }}">{{ $expense->libelle ?: '—' }}</span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            {{ $expense->expenseType?->nom ?? '—' }}
                        </td>
                        <td class="px-3 py-3 text-xs min-w-[10rem] max-w-[16rem]">
                            @if($expense->fundingSources->isNotEmpty())
                                <div class="flex flex-col gap-1">
                                    @foreach($expense->fundingSources as $source)
                                        @php
                                            $sourceLabel = $source->caisse?->nom
                                                ?? $source->revenueType?->nom
                                                ?? '—';
                                        @endphp
                                        <div class="inline-flex flex-wrap items-center gap-1 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded w-fit max-w-full">
                                            <span class="text-emerald-700 dark:text-emerald-300 truncate">{{ $sourceLabel }}</span>
                                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold shrink-0">({{ number_format((float) $source->montant_alloue, 0, ',', ' ') }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($expense->revenueType)
                                <span class="text-slate-500 dark:text-slate-400 italic">{{ $expense->revenueType->nom }} (legacy)</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 font-semibold whitespace-nowrap">{{ $formatFcfa((float) $expense->montant) }}</td>
                        {{-- <td class="px-3 py-3 hidden xl:table-cell">
                            <div class="flex items-center gap-1.5 text-xs">
                                @if($expense->piece_facture_path)
                                    <a href="{{ Storage::url($expense->piece_facture_path) }}"
                                       target="_blank"
                                       title="Facture"
                                       class="inline-flex items-center gap-0.5 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                @endif
                                @if($expense->piece_recu_path)
                                    <a href="{{ Storage::url($expense->piece_recu_path) }}"
                                       target="_blank"
                                       title="Reçu"
                                       class="inline-flex items-center gap-0.5 text-sky-600 hover:text-sky-700 dark:text-sky-400">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"></path>
                                            <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path>
                                        </svg>
                                    </a>
                                @endif
                                @if($expense->piece_autre_path)
                                    <a href="{{ Storage::url($expense->piece_autre_path) }}"
                                       target="_blank"
                                       title="Autre"
                                       class="inline-flex items-center gap-0.5 text-amber-600 hover:text-amber-700 dark:text-amber-400">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>
                                @endif
                                @if(!$expense->piece_facture_path && !$expense->piece_recu_path && !$expense->piece_autre_path)
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </div>
                        </td> --}}
                        <td class="px-3 py-3 sticky right-0 z-10 bg-white dark:bg-slate-800 group-hover:bg-[rgb(240_253_244_/_0.45)] dark:group-hover:bg-[rgb(15_23_42_/_0.65)] shadow-[-6px_0_8px_-6px_rgba(15,23,42,0.18)]">
                            <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                <x-action-button
                                    variant="edit"
                                    href="{{ route('expenses.edit', $expense) }}"
                                    custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30"
                                />
                                <x-action-button
                                    variant="delete"
                                    action="{{ route('expenses.destroy', $expense) }}"
                                    method="DELETE"
                                    confirm-message="Supprimer cette dépense ?"
                                />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">Aucune dépense trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        @include('partials.pagination-fr', ['paginator' => $expenses, 'itemLabel' => 'dépenses'])
    </div>
@endsection
