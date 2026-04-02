@extends('layouts.app')

@section('title', 'Créer un rôle — Catholique')
@section('page-title', 'Créer un rôle')
@section('page-title-info', 'Définissez un libellé, un nom technique (slug Spatie) et les permissions associées.')

@section('btn-create')
    <a href="{{ route('application-configuration.index', ['tab' => 'roles']) }}" class="adventiste-btn-secondary text-sm no-underline">
        <i class="fas fa-arrow-left me-1.5" aria-hidden="true"></i> Retour
    </a>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="adventiste-card-pro-static p-6 sm:p-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/30 px-4 py-3 text-sm text-red-800 dark:text-red-200">
                        <p class="font-semibold mb-2">Corrigez les erreurs suivantes :</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="libelle_role" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Libellé du rôle</label>
                            <input id="libelle_role" type="text" name="libelle_role" value="{{ old('libelle_role') }}"
                                class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100"
                                required autocomplete="off">
                            @error('libelle_role')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Nom technique (slug)</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm font-mono text-slate-900 dark:text-slate-100"
                                required autocomplete="off" placeholder="ex. paroisse_admin">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Utilisé dans le code et par Spatie, sans espaces.</p>
                            @error('name')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Permissions</label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" id="js-role-perm-all"
                                    class="inline-flex items-center rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/50 px-3 py-1.5 text-xs font-semibold text-emerald-800 dark:text-emerald-200 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                                    Tout cocher
                                </button>
                                <button type="button" id="js-role-perm-none"
                                    class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    Tout décocher
                                </button>
                            </div>
                        </div>
                        <div id="role-permissions" class="max-h-[22rem] overflow-y-auto rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/80 dark:bg-slate-900/40 p-4 sm:p-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach ($permissions as $permission)
                                    <label for="perm_{{ $permission->id }}"
                                        class="flex gap-3 rounded-lg border border-transparent hover:border-emerald-200/60 dark:hover:border-emerald-800/50 hover:bg-white/80 dark:hover:bg-slate-800/60 px-3 py-2.5 cursor-pointer transition-colors">
                                        <input class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/40 dark:border-slate-600 dark:bg-slate-800"
                                            type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}"
                                            @checked(in_array($permission->id, old('permissions', []), true))>
                                        <span class="min-w-0 text-sm">
                                            <span class="font-medium text-slate-800 dark:text-slate-100 block">
                                                {{ $permission->libelle_permission ?? ucfirst(str_replace('_', ' ', $permission->name)) }}
                                            </span>
                                            <code class="text-[11px] text-slate-500 dark:text-slate-400 break-all">{{ $permission->name }}</code>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-700/80">
                        <button type="submit" class="adventiste-btn-primary">Créer le rôle</button>
                        <a href="{{ route('application-configuration.index', ['tab' => 'roles']) }}" class="adventiste-btn-secondary no-underline">Annuler</a>
                    </div>
                </form>
            </div>
        </div>

        <aside class="lg:col-span-1">
            <div class="adventiste-card-pro-static p-5 sm:p-6 border border-amber-200/40 dark:border-amber-900/30 bg-amber-50/40 dark:bg-amber-950/20">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-3">
                    <i class="fas fa-info-circle text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                    En bref
                </h2>
                <ul class="text-sm text-slate-600 dark:text-slate-400 space-y-2 list-disc list-inside">
                    <li>Le <strong class="text-slate-800 dark:text-slate-200">libellé</strong> s’affiche dans l’interface.</li>
                    <li>Le <strong class="text-slate-800 dark:text-slate-200">nom technique</strong> ne doit pas être modifié une fois le rôle utilisé.</li>
                    <li>Cochez uniquement les permissions nécessaires au profil.</li>
                </ul>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var box = document.getElementById('role-permissions');
    if (!box) return;
    function setAll(on) {
        box.querySelectorAll('input[type="checkbox"][name="permissions[]"]').forEach(function (c) { c.checked = on; });
    }
    var bAll = document.getElementById('js-role-perm-all');
    var bNone = document.getElementById('js-role-perm-none');
    if (bAll) bAll.addEventListener('click', function () { setAll(true); });
    if (bNone) bNone.addEventListener('click', function () { setAll(false); });
});
</script>
@endpush
