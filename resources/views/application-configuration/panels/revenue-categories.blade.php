@php
    $configTab = 'revenue-categories';
@endphp
<div class="application-config-panel space-y-4" data-config-tab="{{ $configTab }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('revenue-categories.create') }}" class="adventiste-btn-primary text-sm no-underline">+ Nouvelle catégorie</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total catégories</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $categories->total() }}</p>
        </div>
        <div class="adventiste-card-pro-static p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Sur cette page</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $categories->count() }}</p>
        </div>
    </div>

    <div class="adventiste-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200">
                        <th class="px-4 py-3 font-semibold">Code</th>
                        <th class="px-4 py-3 font-semibold">Nom</th>
                        <th class="px-4 py-3 font-semibold">Paroisse</th>
                        <th class="px-4 py-3 font-semibold">Ordre</th>
                        <th class="px-4 py-3 font-semibold">Actif</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $category->code }}</td>
                            <td class="px-4 py-3">{{ $category->nom }}</td>
                            <td class="px-4 py-3">{{ $category->paroisse?->nom ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $category->ordre }}</td>
                            <td class="px-4 py-3">
                                @if ($category->actif)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-900/30 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300">Oui</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300">Non</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <x-action-button
                                        variant="edit"
                                        href="{{ route('revenue-categories.edit', $category) }}"
                                        custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30"
                                    />
                                    <x-action-button
                                        variant="delete"
                                        action="{{ route('revenue-categories.destroy', $category) }}"
                                        method="DELETE"
                                        confirm-message="Supprimer cette catégorie ?"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Aucune catégorie pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('application-configuration.partials.pagination-footer', ['paginator' => $categories, 'itemLabel' => 'catégories'])
</div>
