@extends('layouts.app')

@section('title', $sacrament->type_label . ' - ' . ($sacrament->beneficiary_name ?: optional($sacrament->beneficiary)->prenom . ' ' . optional($sacrament->beneficiary)->nom))
@section('page-title', $sacrament->type_label)

@section('header-back')
    <x-back-link :href="route('sacraments.index', ['type' => $sacrament->type])" />
@endsection

@section('content-container-class', 'max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8')

@section('content')
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
            <i class="fas fa-heart text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            {{ $sacrament->type_label }}
        </h2>
        <div class="flex flex-wrap items-center gap-2">
            @can(\App\Http\Controllers\SacramentController::TYPE_PERMISSIONS[$sacrament->type]['edit'] ?? 'edit_baptisms')
            <x-action-button variant="edit" href="{{ route('sacraments.edit', $sacrament) }}" />
            @endcan
        </div>
    </div>
    <div class="p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-12 gap-4 text-sm">
            <dt class="sm:col-span-3 font-semibold text-slate-500 dark:text-slate-400">Date de célébration</dt>
            <dd class="sm:col-span-9 text-slate-800 dark:text-slate-100">{{ $sacrament->date_celebration?->format('d/m/Y') }}</dd>

            <dt class="sm:col-span-3 font-semibold text-slate-500 dark:text-slate-400">Bénéficiaire / Nom</dt>
            <dd class="sm:col-span-9 text-slate-800 dark:text-slate-100">{{ $sacrament->beneficiary_name ?: ($sacrament->beneficiary ? $sacrament->beneficiary->prenom . ' ' . $sacrament->beneficiary->nom : '—') }}</dd>

            <dt class="sm:col-span-3 font-semibold text-slate-500 dark:text-slate-400">Lieu</dt>
            <dd class="sm:col-span-9 text-slate-800 dark:text-slate-100">{{ $sacrament->lieu ?? '—' }}</dd>

            <dt class="sm:col-span-3 font-semibold text-slate-500 dark:text-slate-400">Célébrant</dt>
            <dd class="sm:col-span-9 text-slate-800 dark:text-slate-100">{{ $sacrament->celebrant ? $sacrament->celebrant->prenom . ' ' . $sacrament->celebrant->nom : '—' }}</dd>

            @if($sacrament->paroisse && auth()->user()->hasRole('super_admin'))
            <dt class="sm:col-span-3 font-semibold text-slate-500 dark:text-slate-400">Paroisse</dt>
            <dd class="sm:col-span-9 text-slate-800 dark:text-slate-100">{{ $sacrament->paroisse->nom }}</dd>
            @endif

            @if($sacrament->notes)
            <dt class="sm:col-span-3 font-semibold text-slate-500 dark:text-slate-400">Notes</dt>
            <dd class="sm:col-span-9 text-slate-800 dark:text-slate-100 whitespace-pre-wrap">{{ $sacrament->notes }}</dd>
            @endif
        </dl>
    </div>
</div>
@endsection
