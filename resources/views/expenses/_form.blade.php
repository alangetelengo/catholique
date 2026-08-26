@php
    $gridColumns = (int) ($formColumns ?? 2);
    $gridClass = $gridColumns === 3 ? 'revenue-form-grid revenue-form-grid--three' : 'revenue-form-grid';
    $field = 'w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900/90 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 focus:border-emerald-500/80 transition-shadow';
    $caisses = $caisses ?? collect();
    $caissesJson = $caisses->map(fn ($caisse) => [
        'id' => $caisse->id,
        'code' => $caisse->code,
        'nom' => $caisse->nom,
        'solde_disponible' => round((float) ($caisse->solde_disponible ?? 0), 2),
    ])->values();
    $expenseTypesJson = collect($expenseTypes ?? [])->map(fn ($type) => [
        'id' => $type->id,
        'code' => $type->code,
    ])->values();
@endphp

<div class="{{ $gridClass }}">
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Type de dépense <span class="text-red-600">*</span></label>
        <select name="expense_type_id" id="expense_type_id" class="{{ $field }}" required>
            <option value="">-- Choisir le type --</option>
            @foreach (($expenseTypes ?? collect()) as $type)
                <option value="{{ $type->id }}"
                    data-code="{{ $type->code }}"
                    {{ (string) old('expense_type_id', $expense->expense_type_id) === (string) $type->id ? 'selected' : '' }}>
                    {{ $type->nom }}@if (! $type->actif) (inactif)@endif
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Une suggestion de caisse homonyme sera proposée si elle a un solde.</p>
        @error('expense_type_id')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Montant total (FCFA) <span class="text-red-600">*</span></label>
        <input type="text" name="montant" id="montant_total" value="{{ old('montant', $expense->montant) }}" class="{{ $field }} js-montant-fcfa" placeholder="10 000 fcfa" required>
        @error('montant')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Libellé (description de la dépense) <span class="text-red-600">*</span></label>
        <input type="text" name="libelle" id="libelle" value="{{ old('libelle', $expense->libelle) }}" class="{{ $field }}" placeholder="Ex: Achat hosties" required>
        @error('libelle')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Date de dépense <span class="text-red-600">*</span></label>
        <input type="date" name="date_depense" id="date_depense" value="{{ old('date_depense', optional($expense->date_depense)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="{{ $field }}" required>
        @error('date_depense')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Fournisseur</label>
        <input type="text" name="fournisseur" value="{{ old('fournisseur', $expense->fournisseur) }}" class="{{ $field }}" placeholder="Nom du fournisseur ou du vendeur">
        @error('fournisseur')<p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="revenue-form-grid__full">
        <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-2">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Caisses de financement <span class="text-red-600">*</span></h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mb-4">
                Prélevez sur une ou plusieurs caisses opérationnelles. Si une caisse est vide,
                <a href="{{ route('caisses.virement.create') }}" class="underline text-emerald-700 dark:text-emerald-400">alimentez-la d&apos;abord</a>
                (virement trésorerie ou crédit direct). La trésorerie générale n&apos;apparaît pas ici.
            </p>

            @error('funding_sources')
                <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    @if(is_array($message))
                        @foreach($message as $error)
                            <p class="text-sm text-red-600 dark:text-red-400">• {{ $error }}</p>
                        @endforeach
                    @else
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @endif
                </div>
            @enderror

            <p id="funding-solde-warning" class="mb-3 hidden text-sm text-amber-700 dark:text-amber-300"></p>

            <div id="funding-sources-container">
                @php
                    $existingSources = old('funding_sources', $expense->fundingSources ?? collect());
                    if (!is_array($existingSources) && !($existingSources instanceof \Illuminate\Support\Collection)) {
                        $existingSources = [];
                    }
                @endphp

                @forelse($existingSources as $index => $source)
                    @php
                        $selectedCaisseId = is_array($source) ? ($source['caisse_id'] ?? '') : ($source->caisse_id ?? '');
                    @endphp
                    <div class="funding-source-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg" data-index="{{ $index }}">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Caisse</label>
                            <select name="funding_sources[{{ $index }}][caisse_id]" class="funding-source-select w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm" required>
                                <option value="">-- Choisir une caisse --</option>
                                @foreach ($caisses as $caisse)
                                    <option value="{{ $caisse->id }}"
                                        data-solde="{{ $caisse->solde_disponible ?? 0 }}"
                                        data-code="{{ $caisse->code }}"
                                        {{ (string) $selectedCaisseId === (string) $caisse->id ? 'selected' : '' }}>
                                        {{ $caisse->nom }} ({{ number_format($caisse->solde_disponible ?? 0, 0, ',', ' ') }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Montant alloué (FCFA)</label>
                            <input type="number"
                                name="funding_sources[{{ $index }}][montant_alloue]"
                                class="funding-source-amount w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                                step="0.01"
                                min="0.01"
                                value="{{ is_array($source) ? ($source['montant_alloue'] ?? '') : ($source->montant_alloue ?? '') }}"
                                required>
                            <p class="funding-source-solde-hint mt-1 text-xs text-slate-500 dark:text-slate-400"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Action</label>
                            <button type="button" class="remove-funding-source w-full px-3 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40">
                                Retirer
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="funding-source-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg" data-index="0">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Caisse</label>
                            <select name="funding_sources[0][caisse_id]" class="funding-source-select w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm" required>
                                <option value="">-- Choisir une caisse --</option>
                                @foreach ($caisses as $caisse)
                                    <option value="{{ $caisse->id }}" data-solde="{{ $caisse->solde_disponible ?? 0 }}" data-code="{{ $caisse->code }}">
                                        {{ $caisse->nom }} ({{ number_format($caisse->solde_disponible ?? 0, 0, ',', ' ') }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Montant alloué (FCFA)</label>
                            <input type="number"
                                name="funding_sources[0][montant_alloue]"
                                class="funding-source-amount w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm"
                                step="0.01"
                                min="0.01"
                                required>
                            <p class="funding-source-solde-hint mt-1 text-xs text-slate-500 dark:text-slate-400"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Action</label>
                            <button type="button" class="remove-funding-source w-full px-3 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40">
                                Retirer
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" id="add-funding-source" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/40">
                    Ajouter une caisse
                </button>
                <div class="text-sm text-right">
                    <div>
                        <span class="text-slate-600 dark:text-slate-400">Total alloué:</span>
                        <span id="total-alloue" class="ml-2 font-bold text-lg text-slate-900 dark:text-slate-100">0 FCFA</span>
                    </div>
                    <p id="total-alloue-hint" class="mt-1 text-xs text-slate-500 dark:text-slate-400"></p>
                </div>
            </div>
            @if ($caisses->isEmpty())
                <p class="mt-3 text-sm text-amber-700 dark:text-amber-300">Aucune caisse alimentée. Créez un crédit direct ou un virement avant d&apos;enregistrer une dépense.</p>
            @endif
        </div>
    </div>

    <div class="hidden">
        <select name="methode_paiement" required>
            @foreach (['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'carte' => 'Carte', 'mobile_money' => 'Mobile Money'] as $key => $label)
                <option value="{{ $key }}" {{ old('methode_paiement', $expense->methode_paiement) === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="revenue-form-grid__full">
        <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-2">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Documents justificatifs</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Facture</label>
                    <input type="file" name="piece_facture" accept=".pdf,.jpg,.jpeg,.png" class="{{ $field }}">
                    @if($expense->piece_facture_path)
                        <p class="mt-1.5 text-xs"><a href="{{ Storage::url($expense->piece_facture_path) }}" target="_blank" class="underline text-emerald-600">Voir le fichier</a></p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Reçu</label>
                    <input type="file" name="piece_recu" accept=".pdf,.jpg,.jpeg,.png" class="{{ $field }}">
                    @if($expense->piece_recu_path)
                        <p class="mt-1.5 text-xs"><a href="{{ Storage::url($expense->piece_recu_path) }}" target="_blank" class="underline text-emerald-600">Voir le fichier</a></p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Autre</label>
                    <input type="file" name="piece_autre" accept=".pdf,.jpg,.jpeg,.png" class="{{ $field }}">
                    @if($expense->piece_autre_path)
                        <p class="mt-1.5 text-xs"><a href="{{ Storage::url($expense->piece_autre_path) }}" target="_blank" class="underline text-emerald-600">Voir le fichier</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="revenue-form-grid__full">
        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Notes</label>
        <textarea name="notes" rows="4" class="{{ $field }}">{{ old('notes', $expense->notes) }}</textarea>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        let fundingSourceIndex = document.querySelectorAll('.funding-source-row').length;
        const container = document.getElementById('funding-sources-container');
        const addButton = document.getElementById('add-funding-source');
        const totalAlloueSpan = document.getElementById('total-alloue');
        const totalAlloueHint = document.getElementById('total-alloue-hint');
        const soldeWarning = document.getElementById('funding-solde-warning');
        const montantTotalInput = document.getElementById('montant_total');
        const expenseTypeSelect = document.getElementById('expense_type_id');
        const form = container ? container.closest('form') : null;
        const caissesData = @json($caissesJson);
        const expenseTypesData = @json($expenseTypesJson);
        const typeToCaisseCode = {
            transport: 'transport',
            carburant: 'transport',
            entretien_reparations: 'entretien',
            liturgie: 'liturgie',
            factures: 'charges',
            intendance: 'intendance',
            accueil_pastorale: 'accueil_pastorale',
            alimentation_popote: 'alimentation_popote',
            salaires: 'salaires',
            autre: 'divers'
        };

        function formatFcfa(value) {
            return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
        }

        function parseMontantTotal() {
            const raw = (montantTotalInput && montantTotalInput.value || '').toString()
                .replace(/\s/g, '')
                .replace(/fcfa/ig, '')
                .replace(',', '.');
            return parseFloat(raw) || 0;
        }

        function buildOptionsHtml(preserveValue) {
            preserveValue = preserveValue || '';
            let html = '<option value="">-- Choisir une caisse --</option>';
            caissesData.forEach(function (caisse) {
                const keep = String(caisse.id) === String(preserveValue);
                if (caisse.solde_disponible > 0 || keep) {
                    html += '<option value="' + caisse.id + '" data-solde="' + caisse.solde_disponible + '" data-code="' + caisse.code + '">'
                        + caisse.nom + ' (' + new Intl.NumberFormat('fr-FR').format(caisse.solde_disponible) + ' FCFA)</option>';
                }
            });
            return html;
        }

        function createFundingSourceRow(index) {
            const row = document.createElement('div');
            row.className = 'funding-source-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg';
            row.dataset.index = index;
            row.innerHTML =
                '<div>' +
                    '<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Caisse</label>' +
                    '<select name="funding_sources[' + index + '][caisse_id]" class="funding-source-select w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm" required>' +
                        buildOptionsHtml() +
                    '</select>' +
                '</div>' +
                '<div>' +
                    '<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Montant alloué (FCFA)</label>' +
                    '<input type="number" name="funding_sources[' + index + '][montant_alloue]" class="funding-source-amount w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm" step="0.01" min="0.01" required>' +
                    '<p class="funding-source-solde-hint mt-1 text-xs text-slate-500 dark:text-slate-400"></p>' +
                '</div>' +
                '<div>' +
                    '<label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Action</label>' +
                    '<button type="button" class="remove-funding-source w-full px-3 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">Retirer</button>' +
                '</div>';
            return row;
        }

        function getCaisseSolde(caisseId) {
            const found = caissesData.find(function (c) { return String(c.id) === String(caisseId); });
            return found ? Number(found.solde_disponible) : 0;
        }

        function updateRowHints() {
            document.querySelectorAll('.funding-source-row').forEach(function (row) {
                const select = row.querySelector('.funding-source-select');
                const amountInput = row.querySelector('.funding-source-amount');
                const hint = row.querySelector('.funding-source-solde-hint');
                if (!select || !amountInput || !hint) return;

                const solde = getCaisseSolde(select.value);
                const amount = parseFloat(amountInput.value) || 0;

                if (!select.value) {
                    hint.textContent = '';
                    amountInput.removeAttribute('max');
                    amountInput.classList.remove('border-red-500', 'text-red-600');
                    return;
                }

                amountInput.setAttribute('max', String(solde));
                hint.textContent = 'Disponible : ' + formatFcfa(solde);

                if (amount > solde + 0.01) {
                    hint.textContent = 'Dépassement : ' + formatFcfa(amount) + ' demandé pour ' + formatFcfa(solde) + ' disponible';
                    hint.classList.add('text-red-600');
                    hint.classList.remove('text-slate-500', 'dark:text-slate-400');
                    amountInput.classList.add('border-red-500', 'text-red-600');
                } else {
                    hint.classList.remove('text-red-600');
                    hint.classList.add('text-slate-500', 'dark:text-slate-400');
                    amountInput.classList.remove('border-red-500', 'text-red-600');
                }
            });
        }

        function aggregateByCaisse() {
            const totals = {};
            document.querySelectorAll('.funding-source-row').forEach(function (row) {
                const select = row.querySelector('.funding-source-select');
                const amountInput = row.querySelector('.funding-source-amount');
                if (!select || !select.value) return;
                const id = String(select.value);
                totals[id] = (totals[id] || 0) + (parseFloat(amountInput && amountInput.value) || 0);
            });
            return totals;
        }

        function validateSoldes() {
            const totals = aggregateByCaisse();
            const errors = [];
            Object.keys(totals).forEach(function (caisseId) {
                const solde = getCaisseSolde(caisseId);
                const demande = Math.round(totals[caisseId] * 100) / 100;
                if (demande > solde + 0.01) {
                    const caisse = caissesData.find(function (c) { return String(c.id) === String(caisseId); });
                    errors.push((caisse ? caisse.nom : 'Caisse') + ' : ' + formatFcfa(demande) + ' demandé, ' + formatFcfa(solde) + ' disponible');
                }
            });

            if (soldeWarning) {
                if (errors.length) {
                    soldeWarning.textContent = errors.join(' — ');
                    soldeWarning.classList.remove('hidden');
                } else {
                    soldeWarning.textContent = '';
                    soldeWarning.classList.add('hidden');
                }
            }

            return errors;
        }

        function updateTotalAlloue() {
            let total = 0;
            document.querySelectorAll('.funding-source-amount').forEach(function (input) {
                total += parseFloat(input.value) || 0;
            });
            totalAlloueSpan.textContent = formatFcfa(total);
            const montantTotal = parseMontantTotal();
            const match = Math.abs(total - montantTotal) <= 0.01 && total > 0;
            totalAlloueSpan.classList.toggle('text-red-600', !match && total > 0);
            totalAlloueSpan.classList.toggle('text-emerald-600', match);
            if (totalAlloueHint) {
                if (montantTotal <= 0) {
                    totalAlloueHint.textContent = '';
                } else if (match) {
                    totalAlloueHint.textContent = 'Le total correspond au montant de la dépense.';
                    totalAlloueHint.className = 'mt-1 text-xs text-emerald-600';
                } else {
                    totalAlloueHint.textContent = 'Doit égaler ' + formatFcfa(montantTotal) + '.';
                    totalAlloueHint.className = 'mt-1 text-xs text-red-600';
                }
            }
            updateRowHints();
            validateSoldes();
        }

        function filterUsedSources() {
            const used = {};
            document.querySelectorAll('.funding-source-select').forEach(function (select) {
                if (select.value) used[select.value] = true;
            });
            document.querySelectorAll('.funding-source-select').forEach(function (select) {
                Array.from(select.options).forEach(function (option) {
                    option.disabled = !!(option.value && option.value !== select.value && used[option.value]);
                });
            });
        }

        function suggestCaisseFromExpenseType() {
            if (!expenseTypeSelect || !container) return;
            const typeId = expenseTypeSelect.value;
            const type = expenseTypesData.find(function (t) { return String(t.id) === String(typeId); });
            if (!type) return;

            const caisseCode = typeToCaisseCode[type.code];
            if (!caisseCode) return;

            const caisse = caissesData.find(function (c) {
                return c.code === caisseCode && c.solde_disponible > 0;
            });
            if (!caisse) return;

            const firstSelect = container.querySelector('.funding-source-row .funding-source-select');
            if (!firstSelect || firstSelect.value) return;

            firstSelect.value = String(caisse.id);
            filterUsedSources();
            updateTotalAlloue();
        }

        if (addButton) {
            addButton.addEventListener('click', function () {
                container.appendChild(createFundingSourceRow(fundingSourceIndex++));
                updateTotalAlloue();
                filterUsedSources();
            });
        }

        if (container) {
            container.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-funding-source')) {
                    const rows = container.querySelectorAll('.funding-source-row');
                    if (rows.length > 1) {
                        e.target.closest('.funding-source-row').remove();
                        updateTotalAlloue();
                        filterUsedSources();
                    } else {
                        alert('Au moins une caisse est obligatoire.');
                    }
                }
            });
            container.addEventListener('input', function (e) {
                if (e.target.classList.contains('funding-source-amount')) updateTotalAlloue();
            });
            container.addEventListener('change', function (e) {
                if (e.target.classList.contains('funding-source-select')) {
                    filterUsedSources();
                    updateTotalAlloue();
                }
            });
        }

        if (montantTotalInput) montantTotalInput.addEventListener('input', updateTotalAlloue);
        if (expenseTypeSelect) expenseTypeSelect.addEventListener('change', suggestCaisseFromExpenseType);

        if (form) {
            form.addEventListener('submit', function (e) {
                const montantTotal = parseMontantTotal();
                let total = 0;
                document.querySelectorAll('.funding-source-amount').forEach(function (input) {
                    total += parseFloat(input.value) || 0;
                });
                const soldeErrors = validateSoldes();

                if (soldeErrors.length) {
                    e.preventDefault();
                    alert('Solde insuffisant sur une ou plusieurs caisses.\n' + soldeErrors.join('\n'));
                    return;
                }

                if (Math.abs(total - montantTotal) > 0.01) {
                    e.preventDefault();
                    alert('Le total des caisses (' + formatFcfa(total) + ') doit être égal au montant de la dépense (' + formatFcfa(montantTotal) + ').');
                }
            });
        }

        updateTotalAlloue();
        filterUsedSources();
        const firstFundingSelect = document.querySelector('.funding-source-select');
        if (!firstFundingSelect || !firstFundingSelect.value) {
            suggestCaisseFromExpenseType();
        }
    })();
</script>
@endpush
