@extends('layouts.app')

@section('title', 'Rapports financiers enregistrés — Catholique')
@section('page-title', 'Rapports financiers enregistrés')
@section('page-title-info', 'Versions enregistrées du rapport mensuel : totaux figés (recettes hors Procure, dépenses toutes catégories, solde). Filtrez par paroisse (super admin) et par année.')

@section('btn-create')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-secondary text-sm">Rafraîchir</a>
        <a href="{{ route('financial-reports.index') }}" class="adventiste-btn-primary">
            <i class="fas fa-file-invoice-dollar me-2" aria-hidden="true"></i>Générer un rapport
        </a>
    </div>
@endsection

@section('content')
    <div class="adventiste-card-pro-static p-4 sm:p-5 mb-6">
        <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-filter text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            Filtres
        </h2>
        <form method="GET" action="{{ route('financial-reports.list') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            @if (auth()->user()->hasRole('super_admin') && $paroisses->count() > 0)
                <div>
                    <label for="list_paroisse" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Paroisse</label>
                    <select id="list_paroisse" name="paroisse_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                        <option value="">Toutes</option>
                        @foreach ($paroisses as $paroisse)
                            <option value="{{ $paroisse->id }}" @selected(request('paroisse_id') == $paroisse->id)>{{ $paroisse->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label for="list_year" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Année</label>
                <select id="list_year" name="year" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                    <option value="">Toutes</option>
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex flex-wrap gap-2 lg:col-span-2">
                <button type="submit" class="adventiste-btn-primary">Filtrer</button>
                <a href="{{ route('financial-reports.list') }}" class="adventiste-btn-secondary">Réinitialiser</a>
            </div>
        </form>
        @if (request()->filled('paroisse_id') || request()->filled('year'))
            <div class="mt-4 flex flex-wrap gap-2">
                @if (request()->filled('paroisse_id') && auth()->user()->hasRole('super_admin'))
                    @php $pNom = $paroisses->firstWhere('id', (int) request('paroisse_id')); @endphp
                    @if ($pNom)
                        <span class="inline-flex items-center rounded-full bg-sky-50 dark:bg-sky-900/30 px-3 py-1 text-xs font-medium text-sky-800 dark:text-sky-200">Paroisse : {{ $pNom->nom }}</span>
                    @endif
                @endif
                @if (request()->filled('year'))
                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-3 py-1 text-xs font-medium text-slate-700 dark:text-slate-200">Année : {{ request('year') }}</span>
                @endif
            </div>
        @endif
    </div>

    @if ($reports->count() > 0)
        <div class="adventiste-table-shell">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-700 dark:text-slate-200">
                            <th class="px-4 py-3 font-semibold">Période</th>
                            @if (auth()->user()->hasRole('super_admin'))
                                <th class="px-4 py-3 font-semibold">Paroisse</th>
                            @endif
                            <th class="px-4 py-3 font-semibold text-right" title="Recettes validées hors Procure (hub)">Recettes</th>
                            <th class="px-4 py-3 font-semibold text-right" title="Toutes catégories de dépenses validées">Dépenses</th>
                            <th class="px-4 py-3 font-semibold text-right" title="Recettes − dépenses">Solde</th>
                            <th class="px-4 py-3 font-semibold">Créé le</th>
                            <th class="px-4 py-3 font-semibold">Créé par</th>
                            <th class="px-4 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                        @foreach ($reports as $report)
                            <tr class="text-slate-700 dark:text-slate-200">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ $report->date_debut->copy()->locale(app()->getLocale())->translatedFormat('F Y') }}
                                    </span>
                                    <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">
                                        {{ $report->date_debut->format('d/m/Y') }} — {{ $report->date_fin->format('d/m/Y') }}
                                    </span>
                                </td>
                                @if (auth()->user()->hasRole('super_admin'))
                                    <td class="px-4 py-3">{{ $report->paroisse->nom ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400">
                                    {{ \App\Helpers\ParoisseConfig::formatMontant($report->total_recettes) }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-700 dark:text-rose-400">
                                    {{ \App\Helpers\ParoisseConfig::formatMontant($report->total_depenses) }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold {{ $report->solde >= 0 ? 'text-sky-700 dark:text-sky-300' : 'text-amber-700 dark:text-amber-400' }}">
                                    {{ \App\Helpers\ParoisseConfig::formatMontant($report->solde) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $report->createdBy->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                        <x-action-button
                                            variant="view"
                                            :href="route('financial-reports.show', $report)"
                                            title="Voir / Imprimer"
                                        />
                                        @can('generate_financial_reports')
                                            @php $editHref = $report->editUrlForList(); @endphp
                                            @if ($editHref)
                                                <x-action-button
                                                    variant="edit"
                                                    :href="$editHref"
                                                    title="Modifier"
                                                    custom-classes="border border-emerald-200 dark:border-emerald-800/80 bg-emerald-50/80 dark:bg-emerald-950/35 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 focus:ring-2 focus:ring-emerald-400/30"
                                                />
                                            @endif
                                            <x-action-button
                                                variant="delete"
                                                :action="route('financial-reports.destroy', $report)"
                                                method="DELETE"
                                                confirm-message="Supprimer ce rapport enregistré ? Cette action est réversible côté administrateur de base (soft delete)."
                                                confirm-text="Supprimer"
                                            />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @include('partials.pagination-fr', ['paginator' => $reports, 'itemLabel' => 'rapports'])
    @else
        <div class="adventiste-card-pro-static p-12 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400">
                <i class="fas fa-file-invoice text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Aucun rapport enregistré</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto">
                Générez un rapport mensuel depuis le hub des rapports financiers pour l’enregistrer ici.
            </p>
            <a href="{{ route('financial-reports.index') }}" class="adventiste-btn-primary mt-6 inline-flex">
                <i class="fas fa-file-invoice-dollar me-2" aria-hidden="true"></i>Générer un rapport
            </a>
        </div>
    @endif
@endsection
