@extends('layouts.app')

@section('title', 'Modifier inventaire - Catholique')
@section('page-title', 'Modifier l’article')
@section('page-title-info', 'Mise à jour de la fiche inventaire.')

@section('header-back')
    <x-back-link :href="route('inventories.index')" />
@endsection

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6">
        <form method="post" action="{{ route('inventories.update', $inventory) }}" class="space-y-8">
            @csrf
            @method('PUT')
            @include('inventories._form', ['inventory' => $inventory, 'paroisses' => $paroisses])
            <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-600/60">
                <button type="submit" class="adventiste-btn-primary">Mettre à jour</button>
                <a href="{{ route('inventories.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
