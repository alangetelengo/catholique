@extends('layouts.app')

@section('title', 'Ajouter un bien au patrimoine')
@section('page-title', 'Ajouter un bien au patrimoine')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inventaire-patrimoine.index') }}">Patrimoine</a></li>
    <li class="breadcrumb-item active" aria-current="page">Ajouter</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-landmark me-2"></i>
                    Nouveau bien patrimonial
                </h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <form action="{{ route('inventaire-patrimoine.store') }}" method="POST">
                    @csrf
                    @include('inventaire-patrimoine._form')
                    <div class="text-end mt-4 pt-4 border-top">
                        <a href="{{ route('inventaire-patrimoine.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-3 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm create-help-panel">
            <div class="card-body p-4">
                <h6 class="mb-3 d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i>
                    En bref
                </h6>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">1. Saisir le <strong>nom</strong> du bien et sa <strong>catégorie</strong>.</li>
                    <li class="mb-2">2. Indiquer le lieu et la valeur estimée si connus.</li>
                    <li class="mb-0">3. Joindre une description pour l'identification.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
