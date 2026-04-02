@extends('layouts.app')

@section('title', 'Créer une configuration')
@section('page-title', 'Créer une configuration')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('configurations.index') }}">Configurations</a></li>
    <li class="breadcrumb-item active" aria-current="page">Créer</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-cog me-2"></i>
                    Nouvelle configuration
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('configurations.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Clé <span class="text-danger">*</span></label>
                        <input type="text" name="cle" class="form-control @error('cle') is-invalid @enderror"
                               value="{{ old('cle') }}" required>
                        @error('cle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Identifiant unique de la configuration (ex: nom_paroisse, couleur_primaire)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Valeur <span class="text-danger">*</span></label>
                        <input type="text" name="valeur" class="form-control @error('valeur') is-invalid @enderror"
                               value="{{ old('valeur') }}" required>
                        @error('valeur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                            <option value="string" {{ old('type') == 'string' ? 'selected' : '' }}>String</option>
                            <option value="integer" {{ old('type') == 'integer' ? 'selected' : '' }}>Integer</option>
                            <option value="boolean" {{ old('type') == 'boolean' ? 'selected' : '' }}>Boolean</option>
                            <option value="float" {{ old('type') == 'float' ? 'selected' : '' }}>Float</option>
                            <option value="json" {{ old('type') == 'json' ? 'selected' : '' }}>JSON</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('configurations.index') }}" class="btn btn-secondary">Annuler</a>
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
                    <li class="mb-2">1. Choisir une <strong>clé</strong> unique et sa <strong>valeur</strong>.</li>
                    <li class="mb-0">2. Ex. : <code>nom_paroisse</code>, <code>couleur_primaire</code>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
