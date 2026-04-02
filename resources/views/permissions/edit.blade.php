@extends('layouts.app')

@section('title', 'Permissions')
@section('page-title', 'Modifier une permission')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Modifier la permission</h4>
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

                <form action="{{ route('permissions.update', $permission) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Libellé de la permission</label>
                            <input type="text" name="libelle_permission" class="form-control"
                                   value="{{ old('libelle_permission', $permission->libelle_permission) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom technique (slug)</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $permission->name) }}" required>
                        </div>
                    </div>

                    <div class="text-end mt-4 pt-4 border-top">
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

