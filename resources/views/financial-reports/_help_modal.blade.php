{{-- Modal Aide rapports financiers (HTML dialog + Tailwind) --}}
<dialog id="financialReportHelpModal" class="max-w-3xl w-[calc(100%-2rem)] rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl p-0 backdrop:bg-slate-900/50">
    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-600 flex items-start justify-between gap-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2" id="financialReportHelpModalLabel">
            <i class="fas fa-info-circle text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            Aide : rapport mensuel de justification
        </h2>
        <button type="button" class="shrink-0 rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" onclick="document.getElementById('financialReportHelpModal').close()" aria-label="Fermer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
    <div class="px-6 py-4 max-h-[min(70vh,28rem)] overflow-y-auto text-sm text-slate-600 dark:text-slate-300">
        <p class="mb-3">Ce rapport agrège les <strong>recettes</strong> (popote/subvention) et les <strong>dépenses</strong> sur la période choisie, puis affiche le solde (excédent ou déficit).</p>
        <ul class="list-none m-0 p-0 space-y-2">
            <li><strong class="text-slate-800 dark:text-slate-100">Paroisse</strong> — (Super admin uniquement) Choisissez la paroisse pour laquelle générer le rapport.</li>
            <li><strong class="text-slate-800 dark:text-slate-100">Mois / Année</strong> — Sélectionnez le mois et l’année de la période à analyser.</li>
            <li><strong class="text-slate-800 dark:text-slate-100">Calculer le rapport</strong> — Lance le calcul : total recettes, total dépenses (par catégorie), solde et listes détaillées.</li>
            <li class="mb-0"><strong class="text-slate-800 dark:text-slate-100">Enregistrer ce rapport</strong> — (Si vous avez le droit) Enregistre une version figée du rapport pour archivage et historique.</li>
        </ul>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 mb-0">
            <i class="fas fa-lightbulb text-amber-500 me-1" aria-hidden="true"></i>
            Les liens en haut de page permettent d’accéder au rapport Subvention Popote, au rapport Charges fixes et à la liste des rapports déjà enregistrés.
        </p>
    </div>
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-600 flex justify-end">
        <button type="button" class="adventiste-btn-primary" onclick="document.getElementById('financialReportHelpModal').close()">J’ai compris</button>
    </div>
</dialog>
