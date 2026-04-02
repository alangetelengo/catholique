@extends('layouts.app')

@section('title', 'Créer une permission')
@section('page-title', 'Créer une permission')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
    <li class="breadcrumb-item active" aria-current="page">Créer</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-key me-2"></i>
                    Nouvelle permission
                </h4>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <h6 class="alert-heading mb-2">Erreurs de validation</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('permissions.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Libellé de la permission</label>
                            <input type="text" name="libelle_permission" class="form-control"
                                   value="{{ old('libelle_permission') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom technique (slug)</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            <small class="text-muted">Utilisé dans le code, par ex. <code>view_members</code>.</small>
                        </div>
                    </div>

                    <div class="text-end mt-4 pt-4 border-top">
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Créer</button>
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
                    <li class="mb-2">1. Saisir le libellé et le nom technique (slug).</li>
                    <li class="mb-0">2. Ex. : <code>view_members</code>, <code>edit_revenues</code>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

