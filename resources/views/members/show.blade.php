@extends('layouts.app')

@section('title', 'Membres')
@section('page-title', 'Détail du membre')

@section('content-container-class', 'max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8')

@section('content')
@php
    $fullName = trim(($member->prenom ?? '') . ' ' . ($member->nom ?? ''));
    $initials = collect(explode(' ', $fullName))
        ->filter()
        ->map(fn (string $part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $statusLabel = [
        'actif' => 'Actif',
        'inactif' => 'Inactif',
        'décédé' => 'Décédé',
    ][$member->statut] ?? ucfirst($member->statut ?? 'Inconnu');

    $statusBadge = match ($member->statut) {
        'actif' => 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-200 ring-emerald-500/25',
        'inactif' => 'bg-amber-500/15 text-amber-900 dark:text-amber-200 ring-amber-500/25',
        'décédé' => 'bg-rose-500/15 text-rose-800 dark:text-rose-200 ring-rose-500/25',
        default => 'bg-slate-500/15 text-slate-700 dark:text-slate-200 ring-slate-500/25',
    };
@endphp

<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-linear-to-br from-slate-900 via-slate-800 to-slate-900 text-white shadow-lg overflow-hidden mb-6">
    <div class="px-6 py-8 sm:px-8 flex flex-wrap items-center justify-between gap-6">
        <div class="flex items-center gap-5 min-w-0">
            <div class="shrink-0 w-20 h-20 rounded-2xl bg-white/15 ring-2 ring-white/30 flex items-center justify-center text-2xl font-bold tracking-tight" aria-hidden="true">
                {{ $initials !== '' ? $initials : 'M' }}
            </div>
            <div class="min-w-0">
                <h2 class="text-xl sm:text-2xl font-bold flex items-center gap-2 m-0">
                    <i class="fas fa-user text-emerald-400 shrink-0" aria-hidden="true"></i>
                    <span class="truncate">{{ $fullName !== '' ? $fullName : 'Membre sans nom' }}</span>
                </h2>
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-300">
                    <span><span class="text-slate-400">Paroisse</span> {{ $member->paroisse?->nom ?? 'N/A' }}</span>
                    <span class="hidden sm:inline text-slate-500" aria-hidden="true">•</span>
                    <span class="inline-flex items-center gap-2">
                        <span class="text-slate-400">Statut</span>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide ring-1 ring-inset {{ $statusBadge }}">{{ $statusLabel }}</span>
                    </span>
                </div>
            </div>
        </div>
        <div class="flex shrink-0 gap-2">
            @can('edit_members')
            <x-action-button variant="edit" href="{{ route('members.edit', $member) }}" custom-classes="border border-white/30 bg-white/10 text-white hover:bg-white/20 focus:ring-white/30" />
            @endcan
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-7 space-y-6">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white m-0">Coordonnées</h3>
            </div>
            <dl class="divide-y divide-slate-100 dark:divide-slate-700/80 text-sm">
                <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Téléphone</dt>
                    <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $member->telephone ?? '—' }}</dd>
                </div>
                <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Email</dt>
                    <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $member->email ?? '—' }}</dd>
                </div>
                <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Adresse</dt>
                    <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $member->adresse ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>
    <div class="lg:col-span-5 space-y-6">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white m-0">Informations personnelles</h3>
            </div>
            <dl class="divide-y divide-slate-100 dark:divide-slate-700/80 text-sm">
                <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Date de naissance</dt>
                    <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $member->date_naissance?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Sexe</dt>
                    <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">
                        @if($member->sexe === 'M')
                            Masculin
                        @elseif($member->sexe === 'F')
                            Féminin
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Créé le</dt>
                    <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $member->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Modifié le</dt>
                    <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $member->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>
    <div class="lg:col-span-12">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white m-0">Notes</h3>
            </div>
            <div class="px-6 py-4 text-sm text-slate-700 dark:text-slate-200 whitespace-pre-wrap">{{ $member->notes ?? '—' }}</div>
        </div>
    </div>
</div>

<div class="mt-6 flex justify-end">
    <a href="{{ route('members.index') }}" class="adventiste-btn-secondary no-underline">Retour</a>
</div>
@endsection
