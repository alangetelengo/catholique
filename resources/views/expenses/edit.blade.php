@extends('layouts.app')

@section('title', 'Modifier dépense - Catholique')
@section('page-title', 'Modifier une dépense')
@section('page-title-info', 'Mise à jour des informations d\'une dépense existante.')
@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
            Enregistrez la dépense avec la catégorie et le type exacts. Le jour de la semaine est calculé automatiquement selon la date choisie.
            Référence: <span class="font-mono">{{ $expense->facture_reference ?? 'N/A' }}</span>
        </p>
        <form method="post" action="{{ route('expenses.update', $expense) }}" class="space-y-8">
            @csrf
            @method('put')
            @include('expenses._form', ['formColumns' => 3])
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Mettre à jour</button>
                <a href="{{ route('expenses.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection

