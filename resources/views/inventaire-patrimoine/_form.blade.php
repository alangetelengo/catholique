@php $item = $item ?? null; @endphp
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Nom du bien <span class="text-danger">*</span></label>
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $item?->nom) }}" required>
        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Catégorie</label>
        <input type="text" name="categorie" class="form-control @error('categorie') is-invalid @enderror" value="{{ old('categorie', $item?->categorie) }}" placeholder="Ex: mobilier, équipement, véhicule">
        @error('categorie')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Référence</label>
        <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference', $item?->reference) }}">
        @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Lieu</label>
        <input type="text" name="lieu" class="form-control @error('lieu') is-invalid @enderror" value="{{ old('lieu', $item?->lieu) }}" placeholder="Où se trouve le bien">
        @error('lieu')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Valeur estimée ({{ \App\Helpers\ParoisseConfig::get(null, 'monnaie', 'FCFA') }})</label>
        <input type="number" step="0.01" min="0" name="valeur_estimee" class="form-control @error('valeur_estimee') is-invalid @enderror" value="{{ old('valeur_estimee', $item?->valeur_estimee) }}">
        @error('valeur_estimee')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Date d'acquisition</label>
        <input type="date" name="date_acquisition" class="form-control @error('date_acquisition') is-invalid @enderror" value="{{ old('date_acquisition', $item?->date_acquisition?->format('Y-m-d')) }}">
        @error('date_acquisition')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">État</label>
        <input type="text" name="etat" class="form-control @error('etat') is-invalid @enderror" value="{{ old('etat', $item?->etat) }}" placeholder="Ex: bon, moyen, à réparer">
        @error('etat')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $item?->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $item?->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
