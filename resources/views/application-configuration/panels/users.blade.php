@php
    $configTab = 'users';
@endphp
<div class="application-config-panel space-y-4" data-config-tab="{{ $configTab }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('users.create') }}" class="adventiste-btn-primary text-sm no-underline">+ Nouvel utilisateur</a>
    </div>

    <div class="adventiste-card-pro-static p-4 sm:p-5">
        <form method="get" action="{{ route('application-configuration.index') }}" class="flex flex-wrap items-center gap-3 app-config-filter-form">
            <input type="hidden" name="tab" value="{{ $configTab }}">
            @if (request()->filled('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher (nom ou email)" class="w-full md:w-[360px] rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm">
            <button type="submit" class="adventiste-btn-primary">Filtrer</button>
            <a href="{{ route('application-configuration.index', ['tab' => $configTab]) }}" class="adventiste-btn-secondary no-underline">Réinitialiser</a>
        </form>
    </div>

    <div class="adventiste-table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-700 dark:text-slate-200">
                        <th class="px-4 py-3 font-semibold">Nom</th>
                        <th class="px-4 py-3 font-semibold">Email</th>
                        <th class="px-4 py-3 font-semibold">Paroisse</th>
                        <th class="px-4 py-3 font-semibold">Rôle</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-700/70">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                @if ($user->paroisse)
                                    {{ $user->paroisse->nom }}
                                @elseif ($user->hasRole('super_admin'))
                                    <span class="text-slate-400">—</span>
                                @else
                                    <span class="text-amber-600 dark:text-amber-400 text-xs font-medium">Non assignée</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->roles->isNotEmpty())
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-900/30 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                        {{ $user->roles->first()->libelle_role ?? ucfirst(str_replace('_', ' ', $user->roles->first()->name)) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300">Aucun</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <x-action-button
                                        variant="edit"
                                        href="{{ route('users.edit', $user) }}"
                                        custom-classes="border border-[#00b464]/35 bg-emerald-50/90 dark:bg-emerald-950/40 text-[#00a055] dark:text-emerald-300 hover:bg-emerald-100/90 dark:hover:bg-emerald-900/50 hover:border-[#00b464]/55 focus:ring-2 focus:ring-[#00b464]/30"
                                    />
                                    <x-action-button
                                        variant="delete"
                                        action="{{ route('users.destroy', $user) }}"
                                        method="DELETE"
                                        confirm-message="Supprimer cet utilisateur ?"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucun utilisateur trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('application-configuration.partials.pagination-footer', ['paginator' => $users, 'itemLabel' => 'utilisateurs'])
</div>
