@extends('layouts.app')

@section('title', 'Modifier rapport Subvention Popote - Catholique')
@section('page-title', 'Modifier rapport Subvention Popote')
@section('page-title-info', 'Mettre à jour la période du rapport puis régénérer.')
@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <form method="post" action="{{ route('popote-reports.update', $popoteReport) }}" class="space-y-8">
            @csrf
            @method('put')
            @include('popote-reports._form')
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Mettre à jour</button>
                <a href="{{ route('popote-reports.show', $popoteReport) }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection

