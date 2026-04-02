@php $item = $item ?? null; @endphp
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Nom de l'article <span class="text-danger">*</span></label>
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $item?->nom) }}" required>
        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Catégorie</label>
        <input type="text" name="categorie" class="form-control @error('categorie') is-invalid @enderror" value="{{ old('categorie', $item?->categorie) }}" placeholder="Ex: féculents, boissons, conserves">
        @error('categorie')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Quantité <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="quantite" class="form-control @error('quantite') is-invalid @enderror" value="{{ old('quantite', $item?->quantite ?? 0) }}" required>
        @error('quantite')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Unité</label>
        <input type="text" name="unite" class="form-control @error('unite') is-invalid @enderror" value="{{ old('unite', $item?->unite ?? 'unité') }}" placeholder="kg, L, pièce, carton...">
        @error('unite')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Quantité min. (alerte)</label>
        <input type="number" step="0.01" min="0" name="quantite_min_alerte" class="form-control @error('quantite_min_alerte') is-invalid @enderror" value="{{ old('quantite_min_alerte', $item?->quantite_min_alerte) }}">
        @error('quantite_min_alerte')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Date de péremption</label>
        <input type="date" name="date_peremption" class="form-control @error('date_peremption') is-invalid @enderror" value="{{ old('date_peremption', $item?->date_peremption?->format('Y-m-d')) }}">
        @error('date_peremption')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Emplacement</label>
        <input type="text" name="emplacement" class="form-control @error('emplacement') is-invalid @enderror" value="{{ old('emplacement', $item?->emplacement) }}">
        @error('emplacement')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @if(isset($paroisses) && $paroisses->count() > 0)
    <div class="col-md-6 mb-3">
        <label class="form-label">Paroisse</label>
        <select name="paroisse_id" class="form-control @error('paroisse_id') is-invalid @enderror">
            @foreach($paroisses as $p)
                <option value="{{ $p->id }}" @selected((string)old('paroisse_id', $item?->paroisse_id) === (string)$p->id)>{{ $p->nom }}</option>
            @endforeach
        </select>
        @error('paroisse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @endif
    <div class="col-12 mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $item?->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
