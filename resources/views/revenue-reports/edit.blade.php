@extends('layouts.app')

@section('title', 'Modifier rapport recettes - Catholique')
@section('page-title', 'Modifier rapport de recettes')
@section('page-title-info', 'Mettez à jour les paramètres du rapport puis régénérez.')

@section('header-back')
    <x-back-link :href="route('revenue-reports.index')" />
@endsection

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
            Ajustez la période, la catégorie ou la paroisse, puis mettez à jour le rapport.
        </p>
        <form method="post" action="{{ route('revenue-reports.update', $revenueReport) }}" class="space-y-8">
            @csrf
            @method('put')
            @include('revenue-reports._form')
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Mettre à jour</button>
                <a href="{{ route('revenue-reports.show', $revenueReport) }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection

