@extends('layouts.app')

@section('title', 'Nouveau rapport recettes - Catholique')
@section('page-title', 'Nouveau rapport de recettes')
@section('page-title-info', 'Générez et enregistrez un rapport global ou par catégorie.')

@section('header-back')
    <x-back-link :href="route('revenue-reports.index')" />
@endsection

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
            Choisissez la paroisse, le type de rapport et la période (mensuelle ou annuelle), puis enregistrez.
        </p>
        <form method="post" action="{{ route('revenue-reports.store') }}" class="space-y-8">
            @csrf
            @include('revenue-reports._form')
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" name="action_mode" value="preview" class="adventiste-btn-secondary">Générer seulement</button>
                <button type="submit" name="action_mode" value="save" class="adventiste-btn-primary">Générer et enregistrer</button>
                <a href="{{ route('revenue-reports.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection

