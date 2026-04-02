@php
    $configTab = 'appearance';
@endphp
<div class="application-config-panel space-y-4" data-config-tab="{{ $configTab }}">
    <div class="adventiste-card-pro-static p-6 sm:p-8 max-w-2xl">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Identité affichée (nom, logo, responsable)</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
            Renseignez le <strong class="font-medium text-slate-800 dark:text-slate-200">nom de la paroisse</strong>, le <strong class="font-medium text-slate-800 dark:text-slate-200">chemin du logo</strong> et le <strong class="font-medium text-slate-800 dark:text-slate-200">responsable</strong> (curé, vicaire…). Le reste du thème (couleurs, PDF, loader) suit les réglages par défaut de l’application.
        </p>
        <a href="{{ route('configurations.workspace') }}" class="adventiste-btn-primary inline-flex no-underline">
            Modifier les paramètres paroisse
        </a>
    </div>

    @can('manage_paroisses')
        <div class="adventiste-card-pro-static p-6 sm:p-8 max-w-2xl">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Liste des paroisses</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                Consulter les paroisses enregistrées, filtrer par nom ou statut, et accéder à la fiche pour modifier les informations de structure (ville, code, curé, etc.).
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('application-configuration.index', ['tab' => 'paroisses']) }}" class="adventiste-btn-primary inline-flex no-underline">
                    Ouvrir la liste des paroisses
                </a>
                @if (auth()->user()?->hasRole('super_admin'))
                    <a href="{{ route('paroisses.create') }}" class="adventiste-btn-secondary inline-flex no-underline">
                        + Nouvelle paroisse
                    </a>
                @endif
            </div>
        </div>
    @endcan
</div>
