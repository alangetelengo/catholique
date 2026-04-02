@extends('layouts.app')

@section('title', 'Rôles')
@section('page-title', 'Gestion des rôles')

@section('btn-create')
<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('roles.index') }}" class="adventiste-btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Rafraîchir
    </a>
    @can('manage_users')
    <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 no-underline">
        <i class="fas fa-users" aria-hidden="true"></i> Utilisateurs
    </a>
    @endcan
    @can('manage_permissions')
    <a href="{{ route('permissions.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 no-underline">
        <i class="fas fa-key" aria-hidden="true"></i> Permissions
    </a>
    @endcan
    @can('manage_roles')
    <a href="{{ route('roles.create') }}" class="adventiste-btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Ajouter un rôle
    </a>
    @endcan
</div>
@endsection

@section('content-container-class', 'max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8')

@section('content')
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
    @if($roles->count() > 0)
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
                @foreach($roles as $role)
                <tr class="group hover:bg-emerald-50/50 dark:hover:bg-slate-700/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium">{{ $role->libelle_role ?? ucfirst(str_replace('_', ' ', $role->name)) }}</td>
                    <td class="px-6 py-4"><code class="text-xs rounded-md bg-slate-100 dark:bg-slate-900 px-2 py-1 text-slate-800 dark:text-slate-200">{{ $role->name }}</code></td>
                    <td class="px-6 py-4">
                        <span class="inline-flex rounded-lg bg-sky-500/10 text-sky-800 dark:text-sky-200 px-2 py-0.5 text-xs font-medium">{{ $role->guard_name }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="inline-flex flex-wrap items-center justify-end gap-1.5" role="group" aria-label="Actions">
                            @can('manage_roles')
                            <x-action-button variant="edit" href="{{ route('roles.edit', $role) }}" />
                            @if($role->name !== 'super_admin')
                            <x-action-button variant="delete" action="{{ route('roles.destroy', $role) }}" method="DELETE" confirm-message="Supprimer ce rôle ?" />
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($roles->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 flex justify-center">
        {{ $roles->links() }}
    </div>
    @endif
    @else
    <div class="px-6 py-16 text-center">
        <i class="fas fa-user-shield text-5xl text-slate-200 dark:text-slate-600 mb-4 block" aria-hidden="true"></i>
        <h3 class="text-slate-600 dark:text-slate-400 font-medium mb-2">Aucun rôle défini</h3>
        <p class="text-sm text-slate-500 dark:text-slate-500 mb-6">Créez des rôles pour gérer les permissions des utilisateurs.</p>
        @can('manage_roles')
        <a href="{{ route('roles.create') }}" class="adventiste-btn-primary inline-flex">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Ajouter un rôle
        </a>
        @endcan
    </div>
    @endif
</div>
@endsection
