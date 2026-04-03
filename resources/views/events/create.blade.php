@extends('layouts.app')

@section('title', 'Créer un événement')
@section('page-title', 'Créer un événement')

@section('header-back')
    <x-back-link :href="route('events.index')" />
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('events.index') }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">Événements</a></li>
    <li class="breadcrumb-item active text-slate-500 dark:text-slate-400" aria-current="page">Créer</li>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-9">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                    Nouvel événement
                </h2>
            </div>
            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50/80 dark:bg-rose-950/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                        <p class="font-semibold mb-2">Erreurs de validation</p>
                        <ul class="list-disc list-inside m-0 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('events.store') }}" method="POST">
                    @csrf

                    @include('events._form', ['paroisses' => $paroisses, 'celebrants' => $celebrants])

                    <div class="flex flex-wrap justify-end gap-2 mt-8 pt-6 border-t border-slate-200 dark:border-slate-600">
                        <a href="{{ route('events.index') }}" class="adventiste-btn-secondary no-underline">Annuler</a>
                        <button type="submit" class="adventiste-btn-primary">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="lg:col-span-3">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                En bref
            </h3>
            <ul class="text-sm text-slate-600 dark:text-slate-400 space-y-2 m-0 pl-4 list-disc">
                <li>Indiquer le <strong>type</strong>, la <strong>date</strong> et le lieu.</li>
                <li>Ajouter le célébrant et les participants si besoin.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
