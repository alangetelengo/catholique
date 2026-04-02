@php
    $configTab = 'permissions';
@endphp
<div class="application-config-panel space-y-4" data-config-tab="{{ $configTab }}">
    <div class="flex flex-wrap items-center gap-2">
        @can('manage_permissions')
            <a href="{{ route('permissions.create') }}" class="adventiste-btn-primary text-sm no-underline">+ Ajouter une permission</a>
        @endcan
    </div>

    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
        @if ($permissions->count() > 0)
            <div class="px-4 sm:px-6 py-3 border-b border-slate-100 dark:border-slate-700/80">
                <label for="perm-search-hub" class="sr-only">Filtrer les permissions</label>
                <input type="search" id="perm-search-hub" placeholder="Filtrer les permissions…" class="w-full max-w-sm rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35">
            </div>
            <div class="overflow-x-auto">
                <table id="permissions-table-hub" class="w-full text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-50 to-slate-100/80 dark:from-slate-700/80 dark:to-slate-800/80 border-b-2 border-slate-200 dark:border-slate-600">
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Libellé</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Nom technique</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Guard</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80 text-slate-800 dark:text-slate-100">
                        @foreach ($permissions as $permission)
                            <tr class="perm-row-hub hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="px-6 py-4 font-medium">{{ $permission->libelle_permission ?? ucfirst(str_replace('_', ' ', $permission->name)) }}</td>
                                <td class="px-6 py-4"><code class="text-xs rounded-md bg-slate-100 dark:bg-slate-900 px-2 py-1">{{ $permission->name }}</code></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-lg bg-sky-500/10 text-sky-800 dark:text-sky-200 px-2 py-0.5 text-xs font-medium">{{ $permission->guard_name }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @can('manage_permissions')
                                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                            <x-action-button variant="edit" href="{{ route('permissions.edit', $permission) }}" />
                                            <x-action-button variant="delete" action="{{ route('permissions.destroy', $permission) }}" method="DELETE" confirm-message="Supprimer cette permission ?" />
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <script>
                (function () {
                    var input = document.getElementById('perm-search-hub');
                    if (!input) return;
                    var q = input.value.trim().toLowerCase();
                    input.addEventListener('input', function () {
                        q = this.value.trim().toLowerCase();
                        document.querySelectorAll('#permissions-table-hub tbody tr.perm-row-hub').forEach(function (row) {
                            row.style.display = !q || row.innerText.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
                        });
                    });
                })();
            </script>
            @include('application-configuration.partials.pagination-footer', ['paginator' => $permissions, 'itemLabel' => 'permissions'])
        @else
            <div class="px-6 py-16 text-center">
                <p class="text-slate-600 dark:text-slate-400 font-medium mb-2">Aucune permission définie</p>
                @can('manage_permissions')
                    <a href="{{ route('permissions.create') }}" class="adventiste-btn-primary inline-flex text-sm no-underline mt-4">Ajouter une permission</a>
                @endcan
            </div>
        @endif
    </div>
</div>
