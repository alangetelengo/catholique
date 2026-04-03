@extends('layouts.app')

@section('title', 'Nouvelle recette - Catholique')
@section('page-title', 'Nouvelle recette')
@section('page-title-info', 'Saisie d\'une nouvelle entrée de revenu.')

@section('header-back')
    <x-back-link :href="route('revenues.index')" />
@endsection

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
            Enregistrez la recette avec la catégorie et le type exacts. Le jour de la semaine est calculé automatiquement selon la date choisie.
        </p>
        <form method="post" action="{{ route('revenues.store') }}" class="space-y-8">
            @csrf
            @include('revenues._form', ['formColumns' => 3])
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                <a href="{{ route('revenues.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
