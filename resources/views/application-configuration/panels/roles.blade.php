@php
    $configTab = 'roles';
@endphp
<div class="application-config-panel space-y-4" data-config-tab="{{ $configTab }}">
    <div class="flex flex-wrap items-center gap-2">
        @can('manage_roles')
            <a href="{{ route('roles.create') }}" class="adventiste-btn-primary text-sm no-underline">+ Ajouter un rôle</a>
        @endcan
    </div>

    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-linear-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Libellé</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Nom technique</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Guard</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                    @forelse ($roles as $role)
                        <tr class="hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-6 py-4 font-medium">{{ $role->libelle_role ?? ucfirst(str_replace('_', ' ', $role->name)) }}</td>
                            <td class="px-6 py-4"><code class="text-xs rounded-md bg-slate-100 dark:bg-slate-900 px-2 py-1">{{ $role->name }}</code></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg bg-sky-500/10 text-sky-800 dark:text-sky-200 px-2 py-0.5 text-xs font-medium">{{ $role->guard_name }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @can('manage_roles')
                                    <div class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                        <x-action-button variant="edit" href="{{ route('roles.edit', $role) }}" />
                                        @if ($role->name !== 'super_admin')
                                            <x-action-button variant="delete" action="{{ route('roles.destroy', $role) }}" method="DELETE" confirm-message="Supprimer ce rôle ?" />
                                        @endif
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                Aucun rôle défini.
                                @can('manage_roles')
                                    <a href="{{ route('roles.create') }}" class="block mt-3 adventiste-btn-primary text-sm no-underline mx-auto w-max">Ajouter un rôle</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('application-configuration.partials.pagination-footer', ['paginator' => $roles, 'itemLabel' => 'rôles'])
    </div>
</div>
