@extends('layouts.app')

@section('title', 'Caisses - Catholique')
@section('page-title', 'Caisses paroissiales')
@section('page-title-info', 'Soldes et alimentations des caisses.')

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6 space-y-6">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('caisses.credit.create') }}" class="adventiste-btn-primary">Crédit direct (ex-subvention)</a>
            <a href="{{ route('caisses.virement.create') }}" class="adventiste-btn-secondary">Alimenter une caisse</a>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-left">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Caisse</th>
                        <th class="px-4 py-3 font-semibold">Description</th>
                        <th class="px-4 py-3 font-semibold text-right">Solde (FCFA)</th>
                        <th class="px-4 py-3 font-semibold"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($caisses as $caisse)
                        <tr class="border-t border-slate-200 dark:border-slate-700">
                            <td class="px-4 py-3 font-medium">
                                {{ $caisse->nom }}
                                @if ($caisse->est_tresorerie)
                                    <span class="ml-2 text-xs text-amber-700 dark:text-amber-300">(trésorerie)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $caisse->description }}</td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ number_format($caisse->solde_disponible ?? 0, 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('caisses.show', $caisse) }}" class="text-emerald-700 dark:text-emerald-400 hover:underline">Mouvements</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">Aucune caisse configurée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
