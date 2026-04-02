@extends('layouts.app')

@section('title', 'Nouveau rapport charges fixes - Catholique')
@section('page-title', 'Nouveau rapport charges fixes')
@section('page-title-info', 'Les charges fixes sont reportées séparément, sans déduction sur les recettes.')
@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <form method="post" action="{{ route('charges-fixes-reports.store') }}" class="space-y-8">
            @csrf
            @include('charges-fixes-reports._form')
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Générer et enregistrer</button>
                <a href="{{ route('charges-fixes-reports.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection

