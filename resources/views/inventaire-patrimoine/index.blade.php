@extends('layouts.app')

@section('title', 'Inventaire patrimoine')
@section('page-title', 'Inventaire patrimoine')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Patrimoine</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h4 class="card-title mb-0">
                    <i class="fas fa-landmark me-2"></i>
                    Inventaire patrimoine
                </h4>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('inventaire-patrimoine.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-sync-alt me-1"></i> Rafraîchir
                    </a>
                    <a href="{{ route('inventaire-patrimoine.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Ajouter un bien
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('inventaire-patrimoine.index') }}" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Catégorie</label>
                            <input type="text" name="categorie" class="form-control" value="{{ request('categorie') }}" placeholder="Ex: mobilier, équipement">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Recherche</label>
                            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Nom, référence, description...">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100" type="submit"><i class="fas fa-filter me-1"></i> Filtrer</button>
                        </div>
                    </div>
                </form>

                @if($items->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>Référence</th>
                                <th>Lieu</th>
                                <th>Valeur estimée</th>
                                <th>Paroisse</th>
                                <th class="text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td><strong>{{ $item->nom }}</strong></td>
                                <td>{{ $item->categorie ?? '—' }}</td>
                                <td>{{ $item->reference ?? '—' }}</td>
                                <td>{{ $item->lieu ?? '—' }}</td>
                                <td>{{ \App\Helpers\ParoisseConfig::formatMontant($item->valeur_estimee) }}</td>
                                <td>{{ $item->paroisse?->nom ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('inventaire-patrimoine.edit', $item) }}" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-pen"></i></a>
                                    <form action="{{ route('inventaire-patrimoine.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce bien de l\'inventaire ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 d-flex justify-content-center">{{ $items->withQueryString()->links() }}</div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-landmark fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun bien dans l'inventaire patrimoine</h5>
                    <a href="{{ route('inventaire-patrimoine.create') }}" class="btn btn-primary mt-3"><i class="fas fa-plus me-1"></i> Ajouter un bien</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
