{{-- Formulaire paroisse : $paroisse, $members, $canQuickCreateCure. --}}
@php
    $gridClass = 'revenue-form-grid revenue-form-grid--three';
    $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $actifChecked = session()->hasOldInput()
        ? (string) old('actif') === '1'
        : (bool) ($paroisse->exists ? $paroisse->actif : true);
@endphp

<div class="{{ $gridClass }}">
    <div class="md:col-span-3">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Nom de la paroisse <span class="text-red-600">*</span></label>
        <input type="text" name="nom" value="{{ old('nom', $paroisse->nom) }}" class="{{ $field }}" required maxlength="255">
        @error('nom')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Adresse</label>
        <input type="text" name="adresse" value="{{ old('adresse', $paroisse->adresse) }}" class="{{ $field }}" maxlength="255">
        @error('adresse')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Ville</label>
        <input type="text" name="ville" value="{{ old('ville', $paroisse->ville) }}" class="{{ $field }}" maxlength="120">
        @error('ville')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Pays</label>
        <input type="text" name="pays" value="{{ old('pays', $paroisse->pays) }}" class="{{ $field }}" maxlength="120">
        @error('pays')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Téléphone</label>
        <input type="text" name="telephone" value="{{ old('telephone', $paroisse->telephone) }}" class="{{ $field }}" maxlength="50">
        @error('telephone')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">E-mail</label>
        <input type="email" name="email" value="{{ old('email', $paroisse->email) }}" class="{{ $field }}" maxlength="255">
        @error('email')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Code paroisse</label>
        <input type="text" name="code_paroisse" value="{{ old('code_paroisse', $paroisse->code_paroisse) }}" class="{{ $field }}" maxlength="100" placeholder="Unique si renseigné">
        @error('code_paroisse')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Diocèse</label>
        <input type="text" name="diocese" value="{{ old('diocese', $paroisse->diocese) }}" class="{{ $field }}" maxlength="255">
        @error('diocese')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5" for="cure-select">Curé (membre)</label>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-stretch">
            <select name="cure_id" id="cure-select" class="{{ $field }} flex-1">
                <option value="">— Non renseigné —</option>
                @foreach ($members as $m)
                    <option value="{{ $m->id }}" {{ (string) old('cure_id', $paroisse->cure_id) === (string) $m->id ? 'selected' : '' }}>
                        {{ trim($m->prenom.' '.$m->nom) }}
                    </option>
                @endforeach
            </select>
            @if ($canQuickCreateCure ?? false)
                <button type="button"
                        id="btn-nouveau-cure"
                        class="adventiste-btn-secondary shrink-0 whitespace-nowrap text-sm"
                        data-quick-store-url="{{ route('members.quick-store') }}"
                        data-paroisse-id="{{ $paroisse->exists ? $paroisse->id : '' }}">
                    + Nouveau curé
                </button>
            @endif
        </div>
        @error('cure_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        @if (! $paroisse->exists)
            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Le curé créé ici sera rattaché à la paroisse à l’enregistrement.</p>
        @endif
    </div>

    <div class="md:col-span-3">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Description</label>
        <textarea name="description" rows="3" class="{{ $field }}">{{ old('description', $paroisse->description) }}</textarea>
        @error('description')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-3 flex items-center gap-2">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-800 dark:text-slate-200">
            <input type="checkbox" name="actif" value="1" {{ $actifChecked ? 'checked' : '' }}>
            Paroisse active
        </label>
        @error('actif')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
</div>

@if ($canQuickCreateCure ?? false)
    @push('body-modals')
        <dialog id="modal-nouveau-cure" class="m-auto max-w-lg w-[calc(100%-2rem)] rounded-2xl border border-slate-200/80 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-2xl p-0 backdrop:bg-slate-900/50">
            <form id="form-nouveau-cure" method="post" class="p-5 sm:p-6 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white m-0">Nouveau curé</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 m-0">Création rapide d’un membre actif. La saisie de la paroisse continue ensuite.</p>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" data-cure-dialog-close aria-label="Fermer">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div id="cure-form-errors" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5" for="cure-prenom">Prénom</label>
                        <input id="cure-prenom" name="prenom" type="text" maxlength="255" class="{{ $field }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5" for="cure-nom">Nom <span class="text-red-600">*</span></label>
                        <input id="cure-nom" name="nom" type="text" required maxlength="255" class="{{ $field }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5" for="cure-sexe">Sexe <span class="text-red-600">*</span></label>
                        <select id="cure-sexe" name="sexe" required class="{{ $field }}">
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5" for="cure-telephone">Téléphone</label>
                        <input id="cure-telephone" name="telephone" type="text" maxlength="50" class="{{ $field }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5" for="cure-email">E-mail</label>
                        <input id="cure-email" name="email" type="email" maxlength="255" class="{{ $field }}">
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-600">
                    <button type="button" class="adventiste-btn-secondary" data-cure-dialog-close>Annuler</button>
                    <button type="submit" id="cure-submit-btn" class="adventiste-btn-primary">Créer et sélectionner</button>
                </div>
            </form>
        </dialog>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var btn = document.getElementById('btn-nouveau-cure');
                var dialog = document.getElementById('modal-nouveau-cure');
                var form = document.getElementById('form-nouveau-cure');
                var select = document.getElementById('cure-select');
                var errorsEl = document.getElementById('cure-form-errors');
                var submitBtn = document.getElementById('cure-submit-btn');
                if (!btn || !dialog || !form || !select) return;

                var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                function closeDialog() {
                    if (typeof dialog.close === 'function') dialog.close();
                }

                function showErrors(message) {
                    if (!errorsEl) return;
                    errorsEl.textContent = message;
                    errorsEl.classList.remove('hidden');
                }

                function clearErrors() {
                    if (!errorsEl) return;
                    errorsEl.textContent = '';
                    errorsEl.classList.add('hidden');
                }

                btn.addEventListener('click', function () {
                    clearErrors();
                    form.reset();
                    if (typeof dialog.showModal === 'function') dialog.showModal();
                });

                dialog.querySelectorAll('[data-cure-dialog-close]').forEach(function (el) {
                    el.addEventListener('click', closeDialog);
                });

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    clearErrors();

                    var payload = {
                        prenom: form.prenom.value,
                        nom: form.nom.value,
                        sexe: form.sexe.value,
                        telephone: form.telephone.value || null,
                        email: form.email.value || null,
                    };
                    var paroisseId = btn.getAttribute('data-paroisse-id');
                    if (paroisseId) {
                        payload.paroisse_id = parseInt(paroisseId, 10);
                    }

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.dataset.loading = '1';
                    }

                    fetch(btn.getAttribute('data-quick-store-url'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                        credentials: 'same-origin',
                    })
                        .then(async function (response) {
                            var data = {};
                            try { data = await response.json(); } catch (e) {}
                            if (!response.ok) {
                                var msg = data.message || 'Impossible de créer le curé.';
                                if (data.errors) {
                                    msg = Object.values(data.errors).flat().join(' ');
                                }
                                throw new Error(msg);
                            }
                            return data;
                        })
                        .then(function (data) {
                            var option = new Option(data.label, String(data.id), true, true);
                            select.add(option);
                            select.value = String(data.id);
                            closeDialog();
                        })
                        .catch(function (err) {
                            showErrors(err.message || 'Erreur réseau.');
                        })
                        .finally(function () {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                delete submitBtn.dataset.loading;
                            }
                        });
                });
            });
        </script>
    @endpush
@endif
