@php
    $configTab = 'paroisses';
@endphp
<div class="application-config-panel space-y-4" data-config-tab="{{ $configTab }}">
    @if ($canManage)
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('paroisses.create') }}" class="adventiste-btn-primary text-sm no-underline">+ Nouvelle paroisse</a>
        </div>

        <div class="adventiste-card-pro-static p-4 sm:p-5">
            <form method="get" action="{{ route('application-configuration.index') }}" class="flex flex-wrap items-end gap-3 app-config-filter-form">
                <input type="hidden" name="tab" value="{{ $configTab }}">
                @if (request()->filled('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                <div class="flex-1 min-w-[12rem]">
                    <label for="pq" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Recherche</label>
                    <input id="pq" type="text" name="q" value="{{ request('q') }}" placeholder="Nom, ville, code…" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label for="pactif" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Statut</label>
                    <select id="pactif" name="actif" class="rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
                        <option value="" {{ ! request()->filled('actif') ? 'selected' : '' }}>Toutes</option>
                        <option value="1" {{ request('actif') === '1' ? 'selected' : '' }}>Actives</option>
                        <option value="0" {{ request('actif') === '0' ? 'selected' : '' }}>Inactives</option>
                    </select>
                </div>
                <button type="submit" class="adventiste-btn-primary">Filtrer</button>
                <a href="{{ route('application-configuration.index', ['tab' => $configTab]) }}" class="adventiste-btn-secondary no-underline">Réinitialiser</a>
            </form>
        </div>
    @endif

    <div class="adventiste-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200">
                        <th class="px-4 py-3 font-semibold">Nom</th>
                        <th class="px-4 py-3 font-semibold">Ville</th>
                        <th class="px-4 py-3 font-semibold">Code</th>
                        <th class="px-4 py-3 font-semibold">Curé</th>
                        <th class="px-4 py-3 font-semibold">Statut</th>
                        @if ($canManage)
                            <th class="px-4 py-3 font-semibold text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse ($paroisses as $p)
                        <tr class="text-slate-700 dark:text-slate-200">
                            <td class="px-4 py-3 font-medium">{{ $p->nom }}</td>
                            <td class="px-4 py-3">{{ $p->ville ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->code_paroisse ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($p->curé)
                                    {{ $p->curé->prenom }} {{ $p->curé->nom }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($p->actif)
                                    <span class="inline-flex rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:text-emerald-200">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-200 dark:bg-slate-600 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">Inactive</span>
                                @endif
                            </td>
                            @if ($canManage)
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-action-button
                                            variant="edit"
                                            href="{{ route('paroisses.edit', $p) }}"
                                            custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30"
                                        />
                                        @if ($p->actif)
                                            <x-action-button
                                                variant="delete"
                                                action="{{ route('paroisses.destroy', $p) }}"
                                                method="DELETE"
                                                confirm-message="Désactiver cette paroisse ? Les données liées seront conservées."
                                            />
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManage ? 6 : 5 }}" class="px-4 py-8 text-center text-slate-500">Aucune paroisse trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('application-configuration.partials.pagination-footer', ['paginator' => $paroisses, 'itemLabel' => 'paroisses'])
</div>
