@extends('layouts.app')

@section('title', 'Ajouter un bien — Patrimoine')
@section('page-title', 'Ajouter un bien au patrimoine')
@section('page-title-info', 'Fiche d’un bien : identification, lieu, valeur estimée et état.')

@section('header-back')
    <x-back-link :href="route('inventaire-patrimoine.index')" />
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="adventiste-card-pro-static p-5 sm:p-6">
                <h2 class="mb-6 flex items-center gap-2 text-base font-semibold text-slate-900 dark:text-white border-b border-slate-200/80 dark:border-slate-600/60 pb-4">
                    <i class="fas fa-landmark text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                    Nouveau bien patrimonial
                </h2>
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 dark:border-red-800/60 bg-red-50 dark:bg-red-950/30 px-4 py-3 text-sm text-red-800 dark:text-red-200">
                        <p class="font-semibold mb-2">Veuillez corriger les champs suivants :</p>
                        <ul class="list-disc list-inside space-y-1 m-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('inventaire-patrimoine.store') }}" method="POST" class="space-y-6">
                    @csrf
                    @include('inventaire-patrimoine._form')
                    <div class="flex flex-wrap gap-3 border-t border-slate-200/80 dark:border-slate-600/60 pt-6">
                        <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                        <a href="{{ route('inventaire-patrimoine.index') }}" class="adventiste-btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
        <div>
            <div class="adventiste-card-pro-static p-5 border-t-4 border-t-sky-500">
                <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                    <i class="fas fa-info-circle text-sky-600 dark:text-sky-400" aria-hidden="true"></i>
                    En bref
                </h3>
                <ul class="m-0 list-none space-y-3 p-0 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    <li>1. Saisir le <strong class="text-slate-800 dark:text-slate-200">nom</strong> du bien et sa <strong class="text-slate-800 dark:text-slate-200">catégorie</strong>.</li>
                    <li>2. Indiquer le lieu et la valeur estimée si connus.</li>
                    <li>3. Ajouter une description pour l’identification.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
