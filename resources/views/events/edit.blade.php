@extends('layouts.app')

@section('title', 'Événements')
@section('page-title', 'Modifier un événement')

@section('content')
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white m-0 flex items-center gap-2">
            <i class="fas fa-calendar-alt text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
            Modifier l'événement
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

        <form action="{{ route('events.update', $event) }}" method="POST">
            @csrf
            @method('PUT')

            @include('events._form', ['event' => $event, 'paroisses' => $paroisses, 'celebrants' => $celebrants])

            <div class="flex flex-wrap justify-end gap-2 mt-8 pt-6 border-t border-slate-200 dark:border-slate-600">
                <a href="{{ route('events.index') }}" class="adventiste-btn-secondary no-underline">Annuler</a>
                <button type="submit" class="adventiste-btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>
@endsection
