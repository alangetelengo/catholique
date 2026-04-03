@extends('layouts.app')

@section('title', 'Nouvelle paroisse - Catholique')
@section('page-title', 'Nouvelle paroisse')
@section('page-title-info', 'Créer une paroisse : identifiants, contact et rattachement éventuel d’un curé (membre actif).')

@section('header-back')
    <x-back-link :href="route('paroisses.index')" />
@endsection

@section('content')
    <div class="adventiste-card-pro-static p-6">
        <form method="post" action="{{ route('paroisses.store') }}" class="space-y-6">
            @csrf
            @include('paroisses._form', ['paroisse' => $paroisse, 'members' => $members])
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
                <a href="{{ route('paroisses.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
