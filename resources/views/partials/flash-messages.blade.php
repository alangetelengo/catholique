{{-- Aligné GED (utilisateurs/index) : alertes session, fermeture au clic (jQuery .js-flash-dismiss) --}}
@if (session('success'))
    <div class="ged-flash ged-flash--success mb-5 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200/80 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center gap-4 shadow-sm" role="alert">
        <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold" aria-hidden="true">✓</span>
        <span class="flex-1 font-medium">{{ session('success') }}</span>
        <button type="button" class="js-flash-dismiss flex-shrink-0 w-8 h-8 rounded-lg hover:bg-emerald-200/50 dark:hover:bg-emerald-800/30 flex items-center justify-center text-lg font-bold transition-colors text-emerald-800 dark:text-emerald-200" title="Fermer">×</button>
    </div>
@endif
@if (session('error'))
    <div class="ged-flash ged-flash--error mb-5 p-4 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200/80 dark:border-red-800 text-red-800 dark:text-red-200 flex items-center gap-4 shadow-sm" role="alert">
        <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/50 flex items-center justify-center text-red-600 dark:text-red-400 font-bold" aria-hidden="true">!</span>
        <span class="flex-1 font-medium">{{ session('error') }}</span>
        <button type="button" class="js-flash-dismiss flex-shrink-0 w-8 h-8 rounded-lg hover:bg-red-200/50 dark:hover:bg-red-800/30 flex items-center justify-center text-lg font-bold transition-colors" title="Fermer">×</button>
    </div>
@endif
@if (session('info'))
    <div class="ged-flash ged-flash--info mb-5 p-4 rounded-2xl bg-sky-50 dark:bg-sky-900/20 border border-sky-200/80 dark:border-sky-800 text-sky-900 dark:text-sky-100 flex items-center gap-4 shadow-sm" role="alert">
        <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-900/50 flex items-center justify-center text-sky-600 dark:text-sky-400 font-bold" aria-hidden="true">i</span>
        <span class="flex-1 font-medium">{{ session('info') }}</span>
        <button type="button" class="js-flash-dismiss flex-shrink-0 w-8 h-8 rounded-lg hover:bg-sky-200/50 dark:hover:bg-sky-800/30 flex items-center justify-center text-lg font-bold transition-colors" title="Fermer">×</button>
    </div>
@endif
@if (session('warning'))
    <div class="ged-flash ged-flash--warning mb-5 p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200/80 dark:border-amber-800 text-amber-900 dark:text-amber-100 flex items-center gap-4 shadow-sm" role="alert">
        <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400 font-bold" aria-hidden="true">!</span>
        <span class="flex-1 font-medium">{{ session('warning') }}</span>
        <button type="button" class="js-flash-dismiss flex-shrink-0 w-8 h-8 rounded-lg hover:bg-amber-200/50 dark:hover:bg-amber-800/30 flex items-center justify-center text-lg font-bold transition-colors" title="Fermer">×</button>
    </div>
@endif
