{{--
    Formulaire inventaire : champs alignés sur le style expenses/revenue (revenue-form-grid).
    $inventory : modèle (create ou edit). $paroisses : liste si super-admin (sinon collect vide).
--}}
@php
    $gridClass = 'revenue-form-grid revenue-form-grid--three';
    $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $isSuper = auth()->user()?->hasRole('super_admin');
@endphp

<div class="{{ $gridClass }}">
    @if($isSuper && $paroisses->isNotEmpty())
        <div class="md:col-span-3">
            <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Paroisse <span class="text-red-600">*</span></label>
            <select name="paroisse_id" class="{{ $field }}" required>
                @foreach ($paroisses as $p)
                    <option value="{{ $p->id }}" {{ (int) old('paroisse_id', $inventory->paroisse_id) === (int) $p->id ? 'selected' : '' }}>{{ $p->nom }}</option>
                @endforeach
            </select>
            @error('paroisse_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
    @else
        {{-- Paroisse implicite : envoyée pour satisfaire la validation « required ». --}}
        <input type="hidden" name="paroisse_id" value="{{ old('paroisse_id', $inventory->paroisse_id ?? auth()->user()?->paroisse_id) }}">
    @endif

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Désignation <span class="text-red-600">*</span></label>
        <input type="text" name="designation" value="{{ old('designation', $inventory->designation) }}" class="{{ $field }}" required maxlength="500">
        @error('designation')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Référence inventaire</label>
        <input type="text" name="reference_inventaire" value="{{ old('reference_inventaire', $inventory->reference_inventaire) }}" class="{{ $field }}" maxlength="128" placeholder="Ex. INV-2026-001">
        @error('reference_inventaire')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Catégorie <span class="text-red-600">*</span></label>
        <select name="categorie" class="{{ $field }}" required>
            @foreach ([
                'mobilier_liturgique' => 'Mobilier liturgique',
                'mobilier' => 'Mobilier',
                'materiel_technique' => 'Matériel technique',
                'consommable' => 'Consommable',
                'autre' => 'Autre',
            ] as $val => $lab)
                <option value="{{ $val }}" {{ old('categorie', $inventory->categorie) === $val ? 'selected' : '' }}>{{ $lab }}</option>
            @endforeach
        </select>
        @error('categorie')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Quantité <span class="text-red-600">*</span></label>
        <input type="number" name="quantite" step="0.01" min="0" value="{{ old('quantite', $inventory->quantite ?? 1) }}" class="{{ $field }}" required>
        @error('quantite')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Unité <span class="text-red-600">*</span></label>
        <input type="text" name="unite" value="{{ old('unite', $inventory->unite ?? 'unité') }}" class="{{ $field }}" required maxlength="64">
        @error('unite')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Emplacement</label>
        <input type="text" name="emplacement" value="{{ old('emplacement', $inventory->emplacement) }}" class="{{ $field }}" maxlength="255" placeholder="Sacristie, bureau…">
        @error('emplacement')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">État <span class="text-red-600">*</span></label>
        <select name="etat" class="{{ $field }}" required>
            @foreach ([
                'bon' => 'Bon état',
                'usage' => 'Usagé',
                'a_reparer' => 'À réparer',
                'hors_service' => 'Hors service',
            ] as $val => $lab)
                <option value="{{ $val }}" {{ old('etat', $inventory->etat) === $val ? 'selected' : '' }}>{{ $lab }}</option>
            @endforeach
        </select>
        @error('etat')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date d’acquisition</label>
        <input type="date" name="date_acquisition" value="{{ old('date_acquisition', optional($inventory->date_acquisition)->format('Y-m-d')) }}" class="{{ $field }}">
        @error('date_acquisition')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Valeur estimée (FCFA)</label>
        <input type="number" name="valeur_estimee" step="0.01" min="0" value="{{ old('valeur_estimee', $inventory->valeur_estimee) }}" class="{{ $field }}" placeholder="Optionnel">
        @error('valeur_estimee')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-3">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Notes</label>
        <textarea name="notes" rows="3" class="{{ $field }}">{{ old('notes', $inventory->notes) }}</textarea>
        @error('notes')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
</div>
