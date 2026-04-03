@extends('layouts.app')

@section('title', 'Paramètres paroisse — Catholique')
@section('page-title', 'Paramètres paroisse')
@section('page-title-info', 'Identité affichée dans l’application, chemin du logo et nom du responsable (curé, vicaire, etc.).')

@section('header-back')
    <x-back-link :href="route('application-configuration.index', ['tab' => 'appearance'])" label="Retour configuration" />
@endsection

@section('content')
    <div class="max-w-3xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
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

                <form action="{{ route('configurations.update-bulk') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="identite">

                    <div>
                        <label for="nom_paroisse" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Nom de la paroisse</label>
                        <input id="nom_paroisse" type="text" name="nom_paroisse"
                            value="{{ old('nom_paroisse', \App\Helpers\ParoisseConfig::get($paroisseId, 'nom_paroisse')) }}"
                            placeholder="Ex. Saint-Esprit de Moungali"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100">
                    </div>

                    <div>
                        <label for="logo_path" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Logo (chemin fichier)</label>
                        <input id="logo_path" type="text" name="logo_path"
                            value="{{ old('logo_path', \App\Helpers\ParoisseConfig::get($paroisseId, 'logo_path')) }}"
                            placeholder="/images/logo-paroisse.svg"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm font-mono text-slate-900 dark:text-slate-100">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Chemin relatif au dossier <code class="text-[11px]">public</code> (SVG, PNG, etc.).</p>
                    </div>

                    <div>
                        <label for="responsable_paroisse" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Responsable</label>
                        <input id="responsable_paroisse" type="text" name="responsable_paroisse"
                            value="{{ old('responsable_paroisse', \App\Helpers\ParoisseConfig::get($paroisseId, 'responsable_paroisse', '')) }}"
                            placeholder="Ex. Père Jean Dupont, curé"
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2.5 text-sm text-slate-900 dark:text-slate-100">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Affiché sur les documents ou l’en-tête si votre modèle l’utilise.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-slate-200/80 dark:border-slate-700/80">
                        <button type="submit" class="adventiste-btn-primary">
                            <i class="fas fa-save me-2" aria-hidden="true"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="lg:col-span-1">
            <div class="adventiste-card-pro-static p-5 sm:p-6 border border-violet-200/40 dark:border-violet-900/30 bg-violet-50/30 dark:bg-violet-950/20">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-3">
                    <i class="fas fa-church text-violet-600 dark:text-violet-400" aria-hidden="true"></i>
                    À savoir
                </h2>
                <ul class="text-sm text-slate-600 dark:text-slate-400 space-y-2 list-disc list-inside">
                    <li>Ces réglages sont stockés pour <strong class="text-slate-800 dark:text-slate-200">votre paroisse</strong>.</li>
                    <li>Les couleurs, PDF, loader et connexion utilisent désormais les <strong class="text-slate-800 dark:text-slate-200">valeurs par défaut</strong> du thème (non modifiables ici).</li>
                    <li>Pour le curé en base « Paroisses », utilisez aussi la fiche <strong class="text-slate-800 dark:text-slate-200">Paroisses</strong> si besoin.</li>
                </ul>
            </div>
        </aside>
    </div>
@endsection
