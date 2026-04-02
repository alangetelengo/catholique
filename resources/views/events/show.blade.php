@extends('layouts.app')

@section('title', 'Événements')
@section('page-title', 'Détail de l\'événement')

@section('content')
@php
    $subtitleParts = [];
    if ($event->date_evenement) {
        $subtitleParts[] = $event->date_evenement->format('d/m/Y');
    }
    if ($event->heure_evenement) {
        $subtitleParts[] = $event->heure_evenement->format('H:i');
    }
    $subtitle = implode(' • ', $subtitleParts);
@endphp

<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-linear-to-br from-slate-900 via-slate-800 to-slate-900 text-white shadow-lg overflow-hidden mb-6">
    <div class="px-6 py-8 sm:px-8 flex flex-wrap items-center justify-between gap-6">
        <div class="flex items-center gap-5 min-w-0">
            <div class="shrink-0 w-16 h-16 rounded-2xl bg-white/15 ring-2 ring-white/30 flex items-center justify-center text-2xl" aria-hidden="true">
                <i class="fas fa-calendar-alt text-emerald-400"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-xl sm:text-2xl font-bold m-0 truncate">{{ $event->titre }}</h2>
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-300">
                    @if($subtitle !== '')
                        <span><span class="text-slate-400">Date</span> {{ $subtitle }}</span>
                        <span class="hidden sm:inline text-slate-500" aria-hidden="true">•</span>
                    @endif
                    <span><span class="text-slate-400">Type</span> {{ $event->type }}</span>
                    <span class="hidden sm:inline text-slate-500" aria-hidden="true">•</span>
                    <span class="inline-flex rounded-lg bg-white/10 px-2 py-0.5 text-xs font-medium">{{ $event->paroisse?->nom ?? 'Paroisse : N/A' }}</span>
                </div>
            </div>
        </div>
        <div class="flex shrink-0 gap-2">
            @can('edit_events')
            <x-action-button variant="edit" href="{{ route('events.edit', $event) }}" custom-classes="border border-white/30 bg-white/10 text-white hover:bg-white/20 focus:ring-white/30" />
            @endcan
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white m-0">Informations</h3>
        </div>
        <dl class="divide-y divide-slate-100 dark:divide-slate-700/80 text-sm">
            <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Titre</dt>
                <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $event->titre }}</dd>
            </div>
            <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Type</dt>
                <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $event->type }}</dd>
            </div>
            <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Date</dt>
                <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $event->date_evenement?->format('d/m/Y') ?? '—' }}</dd>
            </div>
            <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Heure</dt>
                <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $event->heure_evenement?->format('H:i') ?? '—' }}</dd>
            </div>
            <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Lieu</dt>
                <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $event->lieu ?? '—' }}</dd>
            </div>
            <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Intention</dt>
                <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $event->intention ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white m-0">Personnes</h3>
        </div>
        <dl class="divide-y divide-slate-100 dark:divide-slate-700/80 text-sm">
            <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Célébré par</dt>
                <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">
                    @if($event->celebrePar)
                        {{ $event->celebrePar->prenom }} {{ $event->celebrePar->nom }}
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Participants</dt>
                <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">
                    @if($event->participants->count() > 0)
                        <ul class="m-0 pl-4 list-disc">
                            @foreach($event->participants as $participant)
                                <li>{{ $participant->prenom }} {{ $participant->nom }}</li>
                            @endforeach
                        </ul>
                    @else
                        <span class="text-slate-500 dark:text-slate-400">Aucun participant renseigné</span>
                    @endif
                </dd>
            </div>
            <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Créé le</dt>
                <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $event->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
            <div class="px-6 py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Modifié le</dt>
                <dd class="sm:col-span-2 text-slate-800 dark:text-slate-100">{{ $event->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="lg:col-span-2 rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white m-0">Description</h3>
        </div>
        <div class="px-6 py-4 text-sm text-slate-800 dark:text-slate-100 whitespace-pre-wrap">{{ $event->description ?? '—' }}</div>
    </div>

    <div class="lg:col-span-2 flex justify-end">
        <a href="{{ route('events.index') }}" class="adventiste-btn-secondary no-underline">Retour</a>
    </div>
</div>
@endsection
