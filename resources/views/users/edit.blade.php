@extends('layouts.app')

@section('title', 'Modifier utilisateur - Catholique')
@section('page-title', 'Modifier utilisateur')
@section('page-title-info', 'Mets à jour les informations et le rôle du compte.')

@section('content')
    <div class="adventiste-card-pro-static p-6">
        <form method="post" action="{{ route('users.update', $user) }}">
            @csrf
            @method('put')
            @include('users._form', ['submitLabel' => 'Mettre à jour', 'isEdit' => true, 'paroisses' => $paroisses])
        </form>
    </div>
@endsection
