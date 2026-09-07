@extends('layouts.app')

@section('title', 'Modifier la paroisse - Catholique')
@section('page-title', 'Modifier la paroisse')
@section('page-title-info')
    {{ $paroisse->nom }}
@endsection

@section('header-back')
@endsection

@section('content')
    <div class="adventiste-card-pro-static p-6">
        <form method="post" action="{{ route('paroisses.update', $paroisse) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('paroisses._form', [
                'paroisse' => $paroisse,
                'members' => $members,
                'canQuickCreateCure' => $canQuickCreateCure ?? false,
            ])
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="adventiste-btn-primary">Mettre à jour</button>
                <a href="{{ route('application-configuration.index', ['tab' => 'paroisses']) }}" class="adventiste-btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
