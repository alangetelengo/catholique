@extends('layouts.app')

@section('title', 'Nouvel inventaire - Catholique')
@section('page-title', 'Nouvel article d’inventaire')
@section('page-title-info', 'Ajout d’un bien ou d’une ligne matérielle pour la paroisse.')
@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed border-b border-slate-200/80 dark:border-slate-600/60 pb-6">
            Renseignez la désignation et la catégorie. La référence est unique par paroisse si vous la renseignez.
        </p>
        <form method="post" action="{{ route('inventories.store') }}" class="space-y-8">
            @csrf
            @include('inventories._form', ['inventory' => $inventory, 'paroisses' => $paroisses])
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                <a href="{{ route('inventories.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
