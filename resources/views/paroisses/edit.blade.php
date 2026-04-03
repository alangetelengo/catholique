@extends('layouts.app')

@section('title', 'Modifier la paroisse - Catholique')
@section('page-title', 'Modifier la paroisse')
@section('page-title-info')
    Mettre à jour les informations de : {{ $paroisse->nom }}
@endsection

@section('header-back')
    <x-back-link :href="route('paroisses.index')" />
@endsection

@section('content')
    <div class="adventiste-card-pro-static p-6">
        <form method="post" action="{{ route('paroisses.update', $paroisse) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('paroisses._form', ['paroisse' => $paroisse, 'members' => $members])
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="adventiste-btn-primary">Mettre à jour</button>
                <a href="{{ route('paroisses.index') }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
