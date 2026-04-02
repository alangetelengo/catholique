@extends('layouts.app')

@section('title', 'Créer un rôle')
@section('page-title', 'Créer un rôle')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Rôles</a></li>
    <li class="breadcrumb-item active" aria-current="page">Créer</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-user-tag me-2"></i>
                    Nouveau rôle
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

                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Libellé du rôle</label>
                            <input type="text" name="libelle_role" class="form-control" value="{{ old('libelle_role') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom technique (slug)</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            <small class="text-muted">Utilisé dans le code, par ex. <code>paroisse_admin</code>.</small>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span>Permissions</span>
                                <span>
                                    <button type="button" class="btn btn-sm btn-primary me-1"
                                            data-check-toggle
                                            data-check-toggle-target="#role-permissions">
                                        Tout cocher
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-uncheck-toggle
                                            data-check-toggle-target="#role-permissions">
                                        Tout décocher
                                    </button>
                                </span>
                            </label>
                            <div class="row" id="role-permissions">
                                @foreach($permissions as $permission)
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                                   value="{{ $permission->id }}"
                                                   id="perm_{{ $permission->id }}">
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ $permission->libelle_permission ?? ucfirst(str_replace('_', ' ', $permission->name)) }}
                                                <small class="text-muted d-block"><code>{{ $permission->name }}</code></small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4 pt-4 border-top">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Annuler</a>
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
                    <li class="mb-0">2. Cocher les permissions à attribuer au rôle.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

