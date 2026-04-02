@extends('layouts.app')

@section('title', 'Nouvel utilisateur - Catholique')
@section('page-title', 'Créer un utilisateur')
@section('page-title-info', 'Ajoute un compte utilisateur et assigne un rôle.')

@section('content')
    <div class="adventiste-card-pro-static p-6">
        <form method="post" action="{{ route('users.store') }}">
            @csrf
            @include('users._form', ['submitLabel' => 'Créer', 'isEdit' => false, 'paroisses' => $paroisses])
        </form>
    </div>
@endsection
