@extends('layouts.app')

@section('title', 'Modifier une permission — Catholique')
@section('page-title', 'Modifier une permission')
@section('page-title-info', 'Ajustez le libellé ou le nom technique de la permission « {{ $permission->name }} ».')

@section('btn-create')
    <a href="{{ route('application-configuration.index', ['tab' => 'permissions']) }}" class="adventiste-btn-secondary text-sm no-underline">
        <i class="fas fa-arrow-left me-1.5" aria-hidden="true"></i> Retour
    </a>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
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

                <form action="{{ route('permissions.update', $permission) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="libelle_permission" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Libellé</label>
                            <input id="libelle_permission" type="text" name="libelle_permission"
                                value="{{ old('libelle_permission', $permission->libelle_permission) }}"
                                class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100"
                                required autocomplete="off">
                            @error('libelle_permission')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Nom technique (slug)</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $permission->name) }}"
                                class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm font-mono text-slate-900 dark:text-slate-100"
                                required autocomplete="off">
                            @error('name')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-700/80">
                        <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                        <a href="{{ route('application-configuration.index', ['tab' => 'permissions']) }}" class="adventiste-btn-secondary no-underline">Annuler</a>
                    </div>
                </form>
            </div>
        </div>

        <aside class="lg:col-span-1">
            <div class="adventiste-card-pro-static p-5 sm:p-6 border border-sky-200/40 dark:border-sky-900/30 bg-sky-50/40 dark:bg-sky-950/20">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-3">
                    <i class="fas fa-exclamation-triangle text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
                    Attention
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-400">Renommer le slug peut casser les policies ou le code qui utilise l’ancien nom. Préférez en général ne modifier que le libellé.</p>
            </div>
        </aside>
    </div>
@endsection
