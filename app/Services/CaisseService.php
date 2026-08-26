<?php

namespace App\Services;

use App\Models\Caisse;
use App\Models\CaisseMouvement;
use App\Models\Expense;
use App\Models\ExpenseFundingSource;
use App\Models\Revenue;
use App\Models\RevenueType;
use App\Support\CapitalMensuel;
use App\Support\SubventionMensuelle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CaisseService
{
    public function getSolde(Caisse $caisse, ?Expense $excludeExpense = null): float
    {
        if ($caisse->isTresorerie()) {
            return $this->getSoldeTresorerieGlobal($caisse);
        }

        $credits = (float) CaisseMouvement::query()
            ->where('caisse_id', $caisse->id)
            ->where('sens', CaisseMouvement::SENS_CREDIT)
            ->sum('montant');

        $debitsQuery = CaisseMouvement::query()
            ->where('caisse_id', $caisse->id)
            ->where('sens', CaisseMouvement::SENS_DEBIT);

        if ($excludeExpense) {
            $debitsQuery->where(function ($q) use ($excludeExpense): void {
                $q->whereNull('expense_id')
                    ->orWhere('expense_id', '!=', $excludeExpense->id);
            });
        }

        $debits = (float) $debitsQuery->sum('montant');

        return max(0.0, round($credits - $debits, 2));
    }

    public function getSoldeTresorerieGlobal(Caisse $tresorerie): float
    {
        $envelopes = $this->getEnvelopesCapital((int) $tresorerie->paroisse_id);

        return round((float) $envelopes->sum('disponible'), 2);
    }

    public function getSoldeMensuel(
        Caisse $caisse,
        string $moisCapital,
        int $anneeCapital,
        ?Expense $excludeExpense = null
    ): float {
        if ($caisse->isTresorerie()) {
            return $this->getDisponibleTresorerieMois((int) $caisse->paroisse_id, $moisCapital, $anneeCapital);
        }

        $credits = (float) CaisseMouvement::query()
            ->where('caisse_id', $caisse->id)
            ->where('sens', CaisseMouvement::SENS_CREDIT)
            ->where('mois_capital', $moisCapital)
            ->where('annee_capital', $anneeCapital)
            ->sum('montant');

        $debitsQuery = CaisseMouvement::query()
            ->where('caisse_id', $caisse->id)
            ->where('sens', CaisseMouvement::SENS_DEBIT)
            ->where('mois_capital', $moisCapital)
            ->where('annee_capital', $anneeCapital);

        if ($excludeExpense) {
            $debitsQuery->where(function ($q) use ($excludeExpense): void {
                $q->whereNull('expense_id')
                    ->orWhere('expense_id', '!=', $excludeExpense->id);
            });
        }

        $debits = (float) $debitsQuery->sum('montant');

        return max(0.0, round($credits - $debits, 2));
    }

    public function getDisponibleTresorerieMois(int $paroisseId, string $moisCapital, int $anneeCapital): float
    {
        $envelope = $this->getEnvelopesCapital($paroisseId)
            ->first(fn (array $row): bool => $row['mois_capital'] === $moisCapital && $row['annee_capital'] === $anneeCapital);

        return $envelope ? (float) $envelope['disponible'] : 0.0;
    }

    /**
     * Enveloppes mensuelles indépendantes du revenu principal (Banque).
     *
     * @return Collection<int, array{
     *     mois_capital: string,
     *     annee_capital: int,
     *     label: string,
     *     recu: float,
     *     alloue: float,
     *     disponible: float
     * }>
     */
    public function getEnvelopesCapital(int $paroisseId): Collection
    {
        $tresorerie = $this->getTresorerie($paroisseId);

        $recuParEnvelope = Revenue::query()
            ->where('paroisse_id', $paroisseId)
            ->where('statut', 'valide')
            ->whereHas('category', fn ($q) => $q->where('code', 'banque'))
            ->get()
            ->groupBy(function (Revenue $revenue): string {
                $mois = $revenue->mois_capital ?: $revenue->date_recette?->format('m') ?? '01';
                $annee = (int) ($revenue->date_recette?->format('Y') ?? now()->format('Y'));

                return CapitalMensuel::envelopeKey($mois, $annee);
            })
            ->map(fn (Collection $group): float => (float) $group->sum('montant'));

        $alloueParEnvelope = CaisseMouvement::query()
            ->where('caisse_id', $tresorerie->id)
            ->where('type', CaisseMouvement::TYPE_VIREMENT)
            ->where('sens', CaisseMouvement::SENS_DEBIT)
            ->get()
            ->groupBy(fn (CaisseMouvement $m): string => $this->envelopeKeyFromMouvement($m))
            ->map(fn (Collection $group): float => (float) $group->sum('montant'));

        $keys = $recuParEnvelope->keys()->merge($alloueParEnvelope->keys())->unique()->sort()->values();

        return $keys->map(function (string $key) use ($recuParEnvelope, $alloueParEnvelope): array {
            [$annee, $mois] = explode('-', $key);
            $recu = (float) ($recuParEnvelope[$key] ?? 0);
            $alloue = (float) ($alloueParEnvelope[$key] ?? 0);

            return [
                'mois_capital' => $mois,
                'annee_capital' => (int) $annee,
                'label' => CapitalMensuel::formatEnvelopeLabel($mois, (int) $annee),
                'recu' => $recu,
                'alloue' => $alloue,
                'disponible' => max(0.0, round($recu - $alloue, 2)),
            ];
        })->values();
    }

    /**
     * Enveloppes sélectionnables pour une dépense : union capital Banque et mois où des caisses ont un solde.
     *
     * @return Collection<int, array{
     *     mois_capital: string,
     *     annee_capital: int,
     *     label: string,
     *     recu: float,
     *     alloue: float,
     *     disponible: float,
     *     has_caisse_solde: bool
     * }>
     */
    public function getEnvelopesDepense(int $paroisseId, ?Expense $excludeExpense = null): Collection
    {
        $banqueEnvelopes = $this->getEnvelopesCapital($paroisseId)
            ->keyBy(fn (array $row): string => CapitalMensuel::envelopeKey($row['mois_capital'], $row['annee_capital']));

        $caisseKeys = $this->getEnvelopeKeysWithCaisseSolde($paroisseId, $excludeExpense);

        $allKeys = $banqueEnvelopes->keys()->merge($caisseKeys)->unique()->sort()->values();

        return $allKeys->map(function (string $key) use ($banqueEnvelopes, $paroisseId, $excludeExpense): array {
            [$annee, $mois] = explode('-', $key);
            $banque = $banqueEnvelopes->get($key);

            return [
                'mois_capital' => $mois,
                'annee_capital' => (int) $annee,
                'label' => CapitalMensuel::formatEnvelopeLabel($mois, (int) $annee),
                'recu' => (float) ($banque['recu'] ?? 0),
                'alloue' => (float) ($banque['alloue'] ?? 0),
                'disponible' => (float) ($banque['disponible'] ?? 0),
                'has_caisse_solde' => $this->operativeCaissesHaveSoldeMensuel($paroisseId, $mois, (int) $annee, $excludeExpense),
            ];
        })->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function getEnvelopeKeysWithCaisseSolde(int $paroisseId, ?Expense $excludeExpense = null): Collection
    {
        $caisses = Caisse::query()
            ->where('paroisse_id', $paroisseId)
            ->where('actif', true)
            ->where('est_tresorerie', false)
            ->get();

        if ($caisses->isEmpty()) {
            return collect();
        }

        $keys = CaisseMouvement::query()
            ->whereIn('caisse_id', $caisses->pluck('id'))
            ->where('sens', CaisseMouvement::SENS_CREDIT)
            ->whereIn('type', [
                CaisseMouvement::TYPE_CREDIT_DIRECT,
                CaisseMouvement::TYPE_VIREMENT,
                CaisseMouvement::TYPE_ALIMENTATION_RECETTE,
            ])
            ->get()
            ->map(fn (CaisseMouvement $m): string => $this->envelopeKeyFromMouvement($m))
            ->unique();

        return $keys->filter(function (string $key) use ($caisses, $excludeExpense): bool {
            [$annee, $mois] = explode('-', $key);

            return $this->operativeCaissesHaveSoldeMensuel(
                (int) $caisses->first()->paroisse_id,
                $mois,
                (int) $annee,
                $excludeExpense
            );
        })->values();
    }

    private function operativeCaissesHaveSoldeMensuel(
        int $paroisseId,
        string $moisCapital,
        int $anneeCapital,
        ?Expense $excludeExpense = null
    ): bool {
        $caisses = Caisse::query()
            ->where('paroisse_id', $paroisseId)
            ->where('actif', true)
            ->where('est_tresorerie', false)
            ->get();

        foreach ($caisses as $caisse) {
            if ($this->getSoldeMensuel($caisse, $moisCapital, $anneeCapital, $excludeExpense) > 0) {
                return true;
            }
        }

        return false;
    }

    private function envelopeKeyFromMouvement(CaisseMouvement $mouvement): string
    {
        if ($mouvement->mois_capital !== null && $mouvement->mois_capital !== '' && $mouvement->annee_capital !== null) {
            return CapitalMensuel::envelopeKey((string) $mouvement->mois_capital, (int) $mouvement->annee_capital);
        }

        $envelope = CapitalMensuel::envelopeFromDate(
            $mouvement->date_mouvement?->format('Y-m-d') ?? now()->toDateString()
        );

        return CapitalMensuel::envelopeKey($envelope['mois_capital'], $envelope['annee_capital']);
    }

    /**
     * Renseigne mois_capital / annee_capital sur les mouvements legacy sans enveloppe.
     */
    public function backfillMouvementEnvelopes(?int $paroisseId = null): int
    {
        $query = CaisseMouvement::query()
            ->where(function ($builder): void {
                $builder->whereNull('mois_capital')->orWhereNull('annee_capital');
            })
            ->whereIn('type', [
                CaisseMouvement::TYPE_VIREMENT,
                CaisseMouvement::TYPE_CREDIT_DIRECT,
                CaisseMouvement::TYPE_ALIMENTATION_RECETTE,
                CaisseMouvement::TYPE_DEPENSE,
            ]);

        if ($paroisseId !== null) {
            $query->where('paroisse_id', $paroisseId);
        }

        $count = 0;
        foreach ($query->cursor() as $mouvement) {
            $envelope = CapitalMensuel::envelopeFromDate(
                $mouvement->date_mouvement?->format('Y-m-d') ?? now()->toDateString()
            );
            $mouvement->update([
                'mois_capital' => $envelope['mois_capital'],
                'annee_capital' => $envelope['annee_capital'],
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * @return Collection<int, Caisse>
     */
    public function getCaissesAvecSolde(
        int $paroisseId,
        ?Expense $excludeExpense = null,
        bool $onlyOperatives = false,
        bool $onlyWithSolde = false,
        ?string $moisCapital = null,
        ?int $anneeCapital = null
    ): Collection {
        $query = Caisse::query()
            ->where('paroisse_id', $paroisseId)
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('nom');

        if ($onlyOperatives) {
            $query->where('est_tresorerie', false);
        }

        return $query->get()->map(function (Caisse $caisse) use ($excludeExpense, $moisCapital, $anneeCapital) {
            if ($caisse->isTresorerie()) {
                $caisse->solde_disponible = $this->getSoldeTresorerieGlobal($caisse);
            } elseif ($moisCapital !== null && $anneeCapital !== null) {
                $caisse->solde_disponible = $this->getSoldeMensuel($caisse, $moisCapital, $anneeCapital, $excludeExpense);
            } else {
                $caisse->solde_disponible = $this->getSolde($caisse, $excludeExpense);
            }

            return $caisse;
        })->when($onlyWithSolde, fn (Collection $items) => $items->filter(
            fn (Caisse $caisse) => round((float) $caisse->solde_disponible, 2) > 0
        )->values());
    }

    public function getTresorerie(int $paroisseId): Caisse
    {
        $caisse = Caisse::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', Caisse::CODE_TRESORERIE)
            ->first();

        if (! $caisse) {
            throw new InvalidArgumentException('La caisse de trésorerie générale est introuvable pour cette paroisse.');
        }

        return $caisse;
    }

    public function creditDirect(
        Caisse $caisse,
        float $montant,
        string $dateMouvement,
        string $libelle,
        ?string $notes = null,
        ?int $createdBy = null
    ): CaisseMouvement {
        if ($caisse->isTresorerie()) {
            throw new InvalidArgumentException('Utilisez une recette Banque pour créditer la trésorerie générale.');
        }

        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant du crédit doit être positif.');
        }

        $envelope = CapitalMensuel::envelopeFromDate($dateMouvement);

        return CaisseMouvement::query()->create([
            'paroisse_id' => $caisse->paroisse_id,
            'caisse_id' => $caisse->id,
            'type' => CaisseMouvement::TYPE_CREDIT_DIRECT,
            'sens' => CaisseMouvement::SENS_CREDIT,
            'montant' => $montant,
            'date_mouvement' => $dateMouvement,
            'mois_capital' => $envelope['mois_capital'],
            'annee_capital' => $envelope['annee_capital'],
            'libelle' => $libelle,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);
    }

    public function syncCreditFromBanqueRevenue(Revenue $revenue): void
    {
        $revenue->loadMissing('category', 'type');

        if ($revenue->category?->code !== 'banque') {
            $this->removeCreditsLinkedToRevenue($revenue);

            return;
        }

        if (($revenue->statut ?? 'valide') !== 'valide') {
            $this->removeCreditsLinkedToRevenue($revenue);

            return;
        }

        $moisCapital = $revenue->mois_capital ?: $revenue->date_recette?->format('m');
        $anneeCapital = (int) ($revenue->date_recette?->format('Y') ?? now()->format('Y'));

        $tresorerie = $this->getTresorerie((int) $revenue->paroisse_id);
        $existing = CaisseMouvement::query()
            ->where('revenue_id', $revenue->id)
            ->where('type', CaisseMouvement::TYPE_CREDIT_RECETTE)
            ->first();

        $payload = [
            'paroisse_id' => $revenue->paroisse_id,
            'caisse_id' => $tresorerie->id,
            'type' => CaisseMouvement::TYPE_CREDIT_RECETTE,
            'sens' => CaisseMouvement::SENS_CREDIT,
            'montant' => $revenue->montant,
            'date_mouvement' => $revenue->date_recette?->format('Y-m-d') ?? now()->toDateString(),
            'mois_capital' => $moisCapital,
            'annee_capital' => $anneeCapital,
            'libelle' => 'Recette Banque — '.($revenue->type?->nom ?? 'Revenu principal')
                .($moisCapital ? ' ('.SubventionMensuelle::formatMoisCapital($moisCapital).' '.$anneeCapital.')' : ''),
            'revenue_id' => $revenue->id,
            'revenue_type_id' => $revenue->revenue_type_id,
            'created_by' => $revenue->created_by,
        ];

        if ($existing) {
            $existing->update($payload);

            return;
        }

        CaisseMouvement::query()->create($payload);
    }

    public function removeCreditsLinkedToRevenue(Revenue $revenue): void
    {
        CaisseMouvement::query()
            ->where('revenue_id', $revenue->id)
            ->where('type', CaisseMouvement::TYPE_CREDIT_RECETTE)
            ->delete();
    }

    /**
     * @return array{debit: CaisseMouvement, credit: CaisseMouvement}
     */
    public function virementTresorerieVersCaisse(
        Caisse $destination,
        float $montant,
        string $dateMouvement,
        string $libelle,
        string $moisCapital,
        int $anneeCapital,
        ?string $notes = null,
        ?int $createdBy = null
    ): array {
        if ($destination->isTresorerie()) {
            throw new InvalidArgumentException('La destination doit être une caisse opérationnelle.');
        }

        if (! CapitalMensuel::isValidEnvelope($moisCapital, $anneeCapital)) {
            throw new InvalidArgumentException('Le mois du capital est invalide.');
        }

        if ((int) $destination->paroisse_id < 1) {
            throw new InvalidArgumentException('Paroisse invalide pour le virement.');
        }

        $tresorerie = $this->getTresorerie((int) $destination->paroisse_id);
        $solde = $this->getDisponibleTresorerieMois((int) $destination->paroisse_id, $moisCapital, $anneeCapital);

        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant du virement doit être positif.');
        }

        if ($montant > $solde) {
            throw new InvalidArgumentException(sprintf(
                'Capital %s insuffisant : %s FCFA demandé, %s FCFA disponible en trésorerie.',
                CapitalMensuel::formatEnvelopeLabel($moisCapital, $anneeCapital),
                number_format($montant, 0, ',', ' '),
                number_format($solde, 0, ',', ' ')
            ));
        }

        $moisLabel = CapitalMensuel::formatEnvelopeLabel($moisCapital, $anneeCapital);

        return DB::transaction(function () use ($tresorerie, $destination, $montant, $dateMouvement, $libelle, $notes, $createdBy, $moisCapital, $anneeCapital, $moisLabel) {
            $debit = CaisseMouvement::query()->create([
                'paroisse_id' => $tresorerie->paroisse_id,
                'caisse_id' => $tresorerie->id,
                'type' => CaisseMouvement::TYPE_VIREMENT,
                'sens' => CaisseMouvement::SENS_DEBIT,
                'montant' => $montant,
                'date_mouvement' => $dateMouvement,
                'mois_capital' => $moisCapital,
                'annee_capital' => $anneeCapital,
                'libelle' => $libelle ?: 'Virement vers '.$destination->nom.' ('.$moisLabel.')',
                'notes' => $notes,
                'contrepartie_caisse_id' => $destination->id,
                'created_by' => $createdBy,
            ]);

            $credit = CaisseMouvement::query()->create([
                'paroisse_id' => $destination->paroisse_id,
                'caisse_id' => $destination->id,
                'type' => CaisseMouvement::TYPE_VIREMENT,
                'sens' => CaisseMouvement::SENS_CREDIT,
                'montant' => $montant,
                'date_mouvement' => $dateMouvement,
                'mois_capital' => $moisCapital,
                'annee_capital' => $anneeCapital,
                'libelle' => $libelle ?: 'Virement depuis '.$tresorerie->nom.' ('.$moisLabel.')',
                'notes' => $notes,
                'contrepartie_caisse_id' => $tresorerie->id,
                'contrepartie_mouvement_id' => $debit->id,
                'created_by' => $createdBy,
            ]);

            $debit->update(['contrepartie_mouvement_id' => $credit->id]);

            return ['debit' => $debit, 'credit' => $credit];
        });
    }

    public function alimentationDepuisRecette(
        Caisse $destination,
        RevenueType $revenueType,
        float $montant,
        string $dateMouvement,
        string $libelle,
        ?string $notes = null,
        ?int $createdBy = null
    ): CaisseMouvement {
        if ($destination->isTresorerie()) {
            throw new InvalidArgumentException('Alimentez la trésorerie via une recette Banque.');
        }

        if ((int) $destination->paroisse_id !== (int) $revenueType->paroisse_id) {
            throw new InvalidArgumentException('La caisse et le type de recette doivent appartenir à la même paroisse.');
        }

        $revenueType->loadMissing('category');
        if ($revenueType->category?->code === 'banque' || SubventionMensuelle::isSubventionType($revenueType)) {
            throw new InvalidArgumentException('Utilisez un virement depuis la trésorerie ou un crédit direct de caisse.');
        }

        $solde = $this->getSoldeDisponibleRevenueType($revenueType);
        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant doit être positif.');
        }

        if ($montant > $solde) {
            throw new InvalidArgumentException(sprintf(
                '%s : %s FCFA demandé, %s FCFA disponible.',
                $revenueType->nom,
                number_format($montant, 0, ',', ' '),
                number_format($solde, 0, ',', ' ')
            ));
        }

        $envelope = CapitalMensuel::envelopeFromDate($dateMouvement);

        return CaisseMouvement::query()->create([
            'paroisse_id' => $destination->paroisse_id,
            'caisse_id' => $destination->id,
            'type' => CaisseMouvement::TYPE_ALIMENTATION_RECETTE,
            'sens' => CaisseMouvement::SENS_CREDIT,
            'montant' => $montant,
            'date_mouvement' => $dateMouvement,
            'mois_capital' => $envelope['mois_capital'],
            'annee_capital' => $envelope['annee_capital'],
            'libelle' => $libelle ?: 'Alimentation depuis '.$revenueType->nom,
            'notes' => $notes,
            'revenue_type_id' => $revenueType->id,
            'created_by' => $createdBy,
        ]);
    }

    public function getSoldeDisponibleRevenueType(RevenueType $revenueType, ?Expense $excludeExpense = null): float
    {
        $totalRecettes = (float) Revenue::query()
            ->where('revenue_type_id', $revenueType->id)
            ->where('statut', 'valide')
            ->sum('montant');

        $legacyDepensesQuery = ExpenseFundingSource::query()
            ->where('revenue_type_id', $revenueType->id)
            ->whereNull('caisse_id')
            ->whereHas('expense', fn ($q) => $q->where('statut', 'valide'));

        if ($excludeExpense) {
            $legacyDepensesQuery->where('expense_id', '!=', $excludeExpense->id);
        }

        $legacyDepenses = (float) $legacyDepensesQuery->sum('montant_alloue');

        $alimentations = (float) CaisseMouvement::query()
            ->where('revenue_type_id', $revenueType->id)
            ->where('type', CaisseMouvement::TYPE_ALIMENTATION_RECETTE)
            ->where('sens', CaisseMouvement::SENS_CREDIT)
            ->sum('montant');

        return max(0.0, round($totalRecettes - $legacyDepenses - $alimentations, 2));
    }

    /**
     * @param  list<array{caisse_id: int|string, montant_alloue: float|int|string}>  $fundingSources
     * @return array{valid: bool, errors: list<string>, total_alloue: float}
     */
    public function validateFundingSources(
        array $fundingSources,
        ?Expense $excludeExpense = null,
        ?int $paroisseId = null,
        ?string $moisCapital = null,
        ?int $anneeCapital = null
    ): array {
        $errors = [];
        $totalAlloue = 0.0;
        $totalsByCaisse = [];
        $resolvedParoisseId = $paroisseId ?? ($excludeExpense?->paroisse_id !== null ? (int) $excludeExpense->paroisse_id : null);

        if (! CapitalMensuel::isValidEnvelope($moisCapital, $anneeCapital)) {
            return [
                'valid' => false,
                'errors' => ['Le mois du capital (revenu principal) est obligatoire pour une dépense.'],
                'total_alloue' => 0.0,
            ];
        }

        foreach ($fundingSources as $index => $source) {
            if (empty($source['caisse_id']) || ! isset($source['montant_alloue'])) {
                $errors[] = 'La source #'.($index + 1).' est incomplète.';

                continue;
            }

            $caisse = Caisse::query()->find($source['caisse_id']);
            if (! $caisse || ! $caisse->actif) {
                $errors[] = 'Caisse invalide pour la source #'.($index + 1).'.';

                continue;
            }

            if ($resolvedParoisseId !== null && (int) $caisse->paroisse_id !== $resolvedParoisseId) {
                $errors[] = 'La caisse de la source #'.($index + 1).' n\'appartient pas à votre paroisse.';

                continue;
            }

            if ($caisse->isTresorerie()) {
                $errors[] = 'La trésorerie générale ne peut pas financer directement une dépense (source #'.($index + 1).').';

                continue;
            }

            $montantAlloue = (float) $source['montant_alloue'];
            if ($montantAlloue <= 0) {
                $errors[] = 'Le montant alloué doit être positif (source #'.($index + 1).').';

                continue;
            }

            $caisseId = (int) $caisse->id;
            $totalsByCaisse[$caisseId] = ($totalsByCaisse[$caisseId] ?? [
                'caisse' => $caisse,
                'montant' => 0.0,
                'indexes' => [],
            ]);
            $totalsByCaisse[$caisseId]['montant'] += $montantAlloue;
            $totalsByCaisse[$caisseId]['indexes'][] = $index + 1;
            $totalAlloue += $montantAlloue;
        }

        $moisLabel = CapitalMensuel::formatEnvelopeLabel($moisCapital, $anneeCapital);

        foreach ($totalsByCaisse as $entry) {
            /** @var Caisse $caisse */
            $caisse = $entry['caisse'];
            $montantDemande = round((float) $entry['montant'], 2);
            $solde = $this->getSoldeMensuel($caisse, $moisCapital, $anneeCapital, $excludeExpense);

            if ($montantDemande > $solde) {
                $errors[] = sprintf(
                    '%s (%s) : montant demandé %s FCFA mais seulement %s FCFA disponible. Alimentez d\'abord cette caisse pour ce mois.',
                    $caisse->nom,
                    $moisLabel,
                    number_format($montantDemande, 0, ',', ' '),
                    number_format($solde, 0, ',', ' ')
                );
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'total_alloue' => round($totalAlloue, 2),
        ];
    }

    /**
     * Crée les mouvements de trésorerie manquants pour les recettes Banque déjà enregistrées.
     */
    public function backfillBanqueCredits(?int $paroisseId = null): int
    {
        $query = Revenue::query()
            ->with(['category', 'type'])
            ->where('statut', 'valide')
            ->whereHas('category', fn ($q) => $q->where('code', 'banque'));

        if ($paroisseId !== null) {
            $query->where('paroisse_id', $paroisseId);
        }

        $count = 0;
        foreach ($query->cursor() as $revenue) {
            $alreadySynced = CaisseMouvement::query()
                ->where('revenue_id', $revenue->id)
                ->where('type', CaisseMouvement::TYPE_CREDIT_RECETTE)
                ->exists();

            if ($alreadySynced) {
                $this->syncCreditFromBanqueRevenue($revenue);

                continue;
            }

            $this->syncCreditFromBanqueRevenue($revenue);
            $count++;
        }

        return $count;
    }

    /**
     * @param  list<array{caisse_id: int|string, montant_alloue: float|int|string}>  $fundingSources
     */
    public function syncDepenseMouvements(Expense $expense, array $fundingSources): void
    {
        CaisseMouvement::query()
            ->where('expense_id', $expense->id)
            ->where('type', CaisseMouvement::TYPE_DEPENSE)
            ->delete();

        foreach ($expense->fundingSources as $source) {
            if (! $source->caisse_id) {
                continue;
            }

            CaisseMouvement::query()->create([
                'paroisse_id' => $expense->paroisse_id,
                'caisse_id' => $source->caisse_id,
                'type' => CaisseMouvement::TYPE_DEPENSE,
                'sens' => CaisseMouvement::SENS_DEBIT,
                'montant' => $source->montant_alloue,
                'date_mouvement' => $expense->date_depense?->format('Y-m-d') ?? now()->toDateString(),
                'mois_capital' => $expense->mois_capital,
                'annee_capital' => $expense->annee_capital,
                'libelle' => $expense->libelle,
                'expense_id' => $expense->id,
                'expense_funding_source_id' => $source->id,
                'created_by' => $expense->created_by,
            ]);
        }
    }

    public function removeDepenseMouvements(Expense $expense): void
    {
        CaisseMouvement::query()
            ->where('expense_id', $expense->id)
            ->where('type', CaisseMouvement::TYPE_DEPENSE)
            ->delete();
    }
}
