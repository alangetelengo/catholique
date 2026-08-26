@extends('layouts.app')

@section('title', $caisse->nom.' - Catholique')
@section('page-title', $caisse->nom)
@section('page-title-info', 'Historique des mouvements de la caisse.')

@section('header-back')
    <x-back-link :href="route('caisses.index')" />
@endsection

@section('content-container-class', 'w-full max-w-none px-4 sm:px-6 lg:px-8')

@section('content')
    <div class="adventiste-card-pro-static w-full p-5 sm:p-6 space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-4">
            <div>
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $caisse->description }}</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums">{{ number_format($caisse->solde_disponible ?? 0, 0, ',', ' ') }} <span class="text-base font-normal">FCFA</span></p>
            </div>
            <div class="flex flex-wrap gap-2">
                @unless ($caisse->est_tresorerie)
                    <a href="{{ route('caisses.credit.create', ['caisse_id' => $caisse->id]) }}" class="adventiste-btn-primary">Crédit direct</a>
                    <a href="{{ route('caisses.virement.create', ['caisse_id' => $caisse->id]) }}" class="adventiste-btn-secondary">Alimenter</a>
                @endunless
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-left">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Libellé</th>
                        <th class="px-4 py-3 font-semibold text-right">Crédit</th>
                        <th class="px-4 py-3 font-semibold text-right">Débit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mouvements as $mouvement)
                        <tr class="border-t border-slate-200 dark:border-slate-700">
                            <td class="px-4 py-3">{{ $mouvement->date_mouvement?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', $mouvement->type) }}</td>
                            <td class="px-4 py-3">{{ $mouvement->libelle }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-emerald-700 dark:text-emerald-400">
                                {{ $mouvement->sens === 'credit' ? number_format($mouvement->montant, 0, ',', ' ') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-red-600 dark:text-red-400">
                                {{ $mouvement->sens === 'debit' ? number_format($mouvement->montant, 0, ',', ' ') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">Aucun mouvement.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $mouvements->links() }}
    </div>
@endsection
