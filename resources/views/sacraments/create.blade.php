@extends('layouts.app')

@section('title', 'Ajouter - ' . (\App\Models\Sacrament::TYPES[$type] ?? $type))
@section('page-title', 'Ajouter un ' . (\App\Models\Sacrament::TYPES[$type] ?? $type))

@section('content-container-class', 'max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-9">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
                    <i class="fas fa-heart text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                    Nouveau {{ \App\Models\Sacrament::TYPES[$type] ?? $type }}
                </h2>
            </div>
            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50/80 dark:bg-rose-950/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                        <ul class="list-disc list-inside m-0 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(auth()->user()->hasRole('super_admin') && $paroisses->count() === 0)
                    <div class="mb-6 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50/80 dark:bg-amber-950/30 px-4 py-3 text-sm text-amber-900 dark:text-amber-200">
                        Aucune paroisse n’existe encore. Créez au moins une paroisse avant d’enregistrer un sacrement.
                    </div>
                @endif

                <form action="{{ route('sacraments.store') }}" method="POST">
                    @csrf
                    @include('sacraments._form', [
                        'sacrament' => $sacrament,
                        'type' => $type,
                        'paroisses' => $paroisses,
                        'paroisseId' => $paroisseId ?? null,
                        'celebrants' => $celebrants,
                        'members' => $members ?? collect(),
                    ])
                    <div class="flex flex-wrap justify-end gap-2 mt-8 pt-6 border-t border-slate-200 dark:border-slate-600">
                        <a href="{{ route('sacraments.index', ['type' => $type]) }}" class="adventiste-btn-secondary no-underline">Annuler</a>
                        <button type="submit" class="adventiste-btn-primary">Enregistrer</button>
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
                <li>Renseigner la date, le lieu et les personnes concernées.</li>
                <li>Le super administrateur choisit la paroisse ; les autres utilisateurs enregistrent pour leur paroisse.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
