@extends('layouts.app')

@section('title', 'Rôles')
@section('page-title', 'Modifier un rôle')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Modifier le rôle</h4>
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

                <form action="{{ route('roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Libellé du rôle</label>
                            <input type="text" name="libelle_role" class="form-control"
                                   value="{{ old('libelle_role', $role->libelle_role) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom technique (slug)</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $role->name) }}" required>
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
                                @php
                                    $rolePermissionIds = $role->permissions->pluck('id')->all();
                                @endphp
                                @foreach($permissions as $permission)
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                                   value="{{ $permission->id }}"
                                                   id="perm_{{ $permission->id }}"
                                                   @checked(in_array($permission->id, old('permissions', $rolePermissionIds), true))>
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
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

