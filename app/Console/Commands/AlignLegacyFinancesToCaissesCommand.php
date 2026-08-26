<?php

namespace App\Console\Commands;

use App\Models\Caisse;
use App\Models\CaisseMouvement;
use App\Models\Expense;
use App\Models\ExpenseFundingSource;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Services\CaisseService;
use App\Support\SubventionMensuelle;
use Database\Seeders\CaisseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AlignLegacyFinancesToCaissesCommand extends Command
{
    protected $signature = 'paroisse:align-legacy-finances-to-caisses
                            {--dry-run : Affiche les actions sans écrire}
                            {--force : Exécute même si des mouvements caisse existent déjà}';

    protected $description = 'Convertit les anciennes recettes subvention / capital vers Banque (mois_capital) + virement vers caisses';

    /**
     * @var array<string, string>
     */
    private array $subventionTypeToCaisse = [
        'subvention_carburant' => 'transport',
        'subvention_hosties' => 'liturgie',
        'subvention_gardiennage' => 'salaires',
        'subvention_gaz' => 'charges',
        'subvention_internet' => 'charges',
        'subvention_eau' => 'charges',
        'subvention_electricite' => 'charges',
        'subvention_salaires' => 'salaires',
        'subvention_popote' => 'alimentation_popote',
    ];

    public function handle(CaisseService $caisseService): int
    {
        if (! Schema::hasTable('caisses') || ! Schema::hasColumn('expense_funding_sources', 'caisse_id')) {
            $this->error('Tables caisses / colonne caisse_id absentes. Lancez d’abord php artisan migrate.');

            return self::FAILURE;
        }

        if (! Schema::hasColumn('revenues', 'mois_capital')) {
            $this->error('Colonne mois_capital absente. Lancez d’abord php artisan migrate.');

            return self::FAILURE;
        }

        $existingMouvements = CaisseMouvement::query()->count();
        if ($existingMouvements > 0 && ! $this->option('force') && ! $this->option('dry-run')) {
            $this->error("{$existingMouvements} mouvements caisse déjà présents. Relancez avec --force pour réaligner.");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $stats = [
            'caisses' => 0,
            'banque_categories' => 0,
            'banque_types' => 0,
            'banque_revenues' => 0,
            'subventions_reclassees' => 0,
            'legacy_reconstruits' => 0,
            'credits_banque' => 0,
            'virements' => 0,
            'funding_sources' => 0,
            'debits_depenses' => 0,
        ];

        DB::transaction(function () use ($caisseService, $dryRun, &$stats): void {
            $legacySnapshot = $this->snapshotLegacyCredits();

            if (! $dryRun && $this->option('force')) {
                CaisseMouvement::query()->delete();
            }

            $stats['caisses'] = $this->ensureCaisses($dryRun);
            $stats['banque_categories'] = $this->normalizeBanqueCategories($dryRun);
            $stats['banque_types'] = $this->normalizeBanqueTypes($dryRun);
            $stats['banque_revenues'] = $this->normalizeBanqueRevenues($dryRun);

            [$reclassified, $virementsFromSub] = $this->reclassifySubventionsAsBanque($caisseService, $dryRun);
            $stats['subventions_reclassees'] = $reclassified;
            $stats['virements'] += $virementsFromSub;

            [$reconstructed, $virementsFromLegacy] = $this->reconstructBanqueFromLegacyCredits(
                $caisseService,
                $legacySnapshot,
                $dryRun
            );
            $stats['legacy_reconstruits'] = $reconstructed;
            $stats['virements'] += $virementsFromLegacy;

            $stats['credits_banque'] = $this->syncBanqueCredits($caisseService, $dryRun);
            [$funding, $debits] = $this->relinkFundingAndSyncDepenses($caisseService, $dryRun);
            $stats['funding_sources'] = $funding;
            $stats['debits_depenses'] = $debits;
            $this->deactivateSubventionCatalog($dryRun);
        });

        $this->table(
            ['Étape', 'Nombre'],
            collect($stats)->map(fn ($v, $k) => [$k, $v])->values()->all()
        );

        if ($dryRun) {
            $this->warn('Dry-run : aucune écriture effectuée.');
        } else {
            $this->info('Alignement terminé.');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array{caisse_id: int, paroisse_id: int, montant: float, date_mouvement: string, libelle: string, notes: ?string, created_by: ?int, mois_capital: string, legacy_id: ?int, marker: string}>
     */
    private function snapshotLegacyCredits(): array
    {
        $rows = [];

        $mouvements = CaisseMouvement::query()
            ->where(function ($q): void {
                $q->where(function ($credit): void {
                    $credit->where('type', CaisseMouvement::TYPE_CREDIT_DIRECT)
                        ->where('libelle', 'like', 'Crédit legacy#%');
                })->orWhere(function ($virement): void {
                    $virement->where('type', CaisseMouvement::TYPE_VIREMENT)
                        ->where('sens', CaisseMouvement::SENS_CREDIT)
                        ->where('libelle', 'like', '%legacy#%');
                });
            })
            ->orderBy('id')
            ->get();

        foreach ($mouvements as $mouvement) {
            $mois = $mouvement->date_mouvement?->format('m') ?? now()->format('m');
            if (preg_match('/\((\d{2})\/(\d{4})\)/', (string) $mouvement->libelle, $matches)) {
                $mois = $matches[1];
            } elseif (preg_match('/\((Janvier|Février|Mars|Avril|Mai|Juin|Juillet|Août|Septembre|Octobre|Novembre|Décembre)\)/u', (string) $mouvement->libelle, $labelMatch)) {
                $mois = array_search($labelMatch[1], SubventionMensuelle::moisOptions(), true) ?: $mois;
            }

            $legacyId = null;
            if (preg_match('/legacy#(\d+)/', (string) $mouvement->libelle, $idMatch)) {
                $legacyId = (int) $idMatch[1];
            }

            $marker = $legacyId
                ? 'legacy#'.$legacyId
                : 'legacy-credit:'.$mouvement->date_mouvement?->format('Y-m-d').':'.$mouvement->montant;

            $rows[] = [
                'caisse_id' => (int) $mouvement->caisse_id,
                'paroisse_id' => (int) $mouvement->paroisse_id,
                'montant' => (float) $mouvement->montant,
                'date_mouvement' => $mouvement->date_mouvement?->format('Y-m-d') ?? now()->toDateString(),
                'libelle' => (string) $mouvement->libelle,
                'notes' => $mouvement->notes,
                'created_by' => $mouvement->created_by ? (int) $mouvement->created_by : null,
                'mois_capital' => $mois,
                'legacy_id' => $legacyId,
                'marker' => $marker,
            ];
        }

        return $rows;
    }

    private function ensureCaisses(bool $dryRun): int
    {
        $paroisseIds = DB::table('paroisses')->pluck('id');
        $count = 0;

        foreach ($paroisseIds as $paroisseId) {
            foreach (CaisseSeeder::definitions() as $definition) {
                $exists = Caisse::query()
                    ->where('paroisse_id', $paroisseId)
                    ->where('code', $definition['code'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $count++;
                if ($dryRun) {
                    continue;
                }

                Caisse::query()->create([
                    'paroisse_id' => $paroisseId,
                    'code' => $definition['code'],
                    'nom' => $definition['nom'],
                    'description' => $definition['description'],
                    'est_tresorerie' => $definition['est_tresorerie'],
                    'actif' => true,
                    'ordre' => $definition['ordre'],
                ]);
            }
        }

        return $count;
    }

    private function normalizeBanqueCategories(bool $dryRun): int
    {
        $query = RevenueCategory::query()
            ->whereIn('code', ['capita', 'capital', 'banque']);

        $count = 0;
        foreach ($query->get() as $category) {
            $count++;
            if ($dryRun) {
                continue;
            }

            $category->update([
                'code' => 'banque',
                'nom' => 'BANQUE (économat diocésain)',
                'description' => 'Versements de la hiérarchie / économat diocésain — trésorerie générale uniquement',
                'actif' => true,
                'ordre' => 0,
            ]);
        }

        return $count;
    }

    private function normalizeBanqueTypes(bool $dryRun): int
    {
        $banqueCategoryIds = RevenueCategory::query()->where('code', 'banque')->pluck('id');
        $types = RevenueType::query()
            ->whereIn('revenue_category_id', $banqueCategoryIds)
            ->orWhereIn('code', ['rev-principal', 'rev_principal', 'revenu_principal'])
            ->get();

        $count = 0;
        foreach ($types as $type) {
            $count++;
            if ($dryRun) {
                continue;
            }

            $categoryId = RevenueCategory::query()
                ->where('paroisse_id', $type->paroisse_id)
                ->where('code', 'banque')
                ->value('id')
                ?? $banqueCategoryIds->first();

            $type->update([
                'code' => 'revenu_principal',
                'nom' => 'Revenu principal',
                'revenue_category_id' => $categoryId ?: $type->revenue_category_id,
                'actif' => true,
            ]);
        }

        return $count;
    }

    private function normalizeBanqueRevenues(bool $dryRun): int
    {
        $banqueCategoryIds = RevenueCategory::query()->where('code', 'banque')->pluck('id');
        $revenues = Revenue::withTrashed()
            ->whereIn('revenue_category_id', $banqueCategoryIds)
            ->get();

        $count = 0;
        foreach ($revenues as $revenue) {
            $count++;
            if ($dryRun) {
                continue;
            }

            $mois = $revenue->mois_capital;
            if (! $mois && $revenue->date_recette) {
                $mois = $revenue->date_recette->format('m');
            }

            if ($revenue->trashed() && (float) $revenue->montant > 0) {
                $revenue->restore();
            }

            $revenue->update([
                'mois_capital' => $mois,
                'mois_location' => null,
            ]);
        }

        return $count;
    }

    private function syncBanqueCredits(CaisseService $caisseService, bool $dryRun): int
    {
        $revenues = Revenue::query()
            ->with(['category', 'type'])
            ->whereHas('category', fn ($q) => $q->where('code', 'banque'))
            ->where('statut', 'valide')
            ->get();

        $count = 0;
        foreach ($revenues as $revenue) {
            $count++;
            if ($dryRun) {
                continue;
            }

            $caisseService->syncCreditFromBanqueRevenue($revenue);
        }

        return $count;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function reclassifySubventionsAsBanque(CaisseService $caisseService, bool $dryRun): array
    {
        $reclassified = 0;
        $virements = 0;

        $subventionRevenues = Revenue::withTrashed()
            ->with('type')
            ->whereHas('category', fn ($q) => $q->where('code', SubventionMensuelle::CATEGORY_CODE))
            ->where('statut', 'valide')
            ->whereNull('deleted_at')
            ->get();

        foreach ($subventionRevenues as $revenue) {
            $typeCode = $revenue->type?->code ?? '';
            $caisseCode = $this->subventionTypeToCaisse[$typeCode] ?? 'divers';
            $caisse = Caisse::query()
                ->where('paroisse_id', $revenue->paroisse_id)
                ->where('code', $caisseCode)
                ->first();

            $banque = $this->resolveBanqueCatalog((int) $revenue->paroisse_id);
            if (! $banque || ! $caisse) {
                $this->warn("Impossible de reclasser la recette #{$revenue->id} (catalogue Banque ou caisse manquant).");

                continue;
            }

            $reclassified++;
            if ($dryRun) {
                continue;
            }

            $moisCapital = $this->moisCapitalFromSubventionRevenue($revenue);

            $revenue->update([
                'revenue_category_id' => $banque['category_id'],
                'revenue_type_id' => $banque['type_id'],
                'mois_capital' => $moisCapital,
                'mois_location' => null,
            ]);

            if (Schema::hasColumn('revenues', 'mois_subvention')) {
                DB::table('revenues')->where('id', $revenue->id)->update(['mois_subvention' => null]);
            }

            $caisseService->syncCreditFromBanqueRevenue($revenue->fresh(['category', 'type']));

            $alreadyVirement = CaisseMouvement::query()
                ->where('caisse_id', $caisse->id)
                ->where('type', CaisseMouvement::TYPE_VIREMENT)
                ->where('sens', CaisseMouvement::SENS_CREDIT)
                ->where('libelle', 'like', '%legacy#'.$revenue->id.'%')
                ->exists();

            if (! $alreadyVirement && (float) $revenue->montant > 0) {
                $this->createVirementBypassingSoldeCheck(
                    $caisse,
                    (float) $revenue->montant,
                    $revenue->date_recette?->format('Y-m-d') ?? now()->toDateString(),
                    sprintf(
                        'Virement legacy#%d — capital vers %s (%s)',
                        $revenue->id,
                        $caisse->nom,
                        SubventionMensuelle::formatMoisCapital($moisCapital)
                    ),
                    $revenue->notes,
                    $revenue->created_by ? (int) $revenue->created_by : null
                );
                $virements++;
            }
        }

        return [$reclassified, $virements];
    }

    /**
     * @param  list<array{caisse_id: int, paroisse_id: int, montant: float, date_mouvement: string, libelle: string, notes: ?string, created_by: ?int, mois_capital: string, legacy_id: ?int, marker: string}>  $legacySnapshot
     * @return array{0: int, 1: int}
     */
    private function reconstructBanqueFromLegacyCredits(
        CaisseService $caisseService,
        array $legacySnapshot,
        bool $dryRun
    ): array {
        $reconstructed = 0;
        $virements = 0;

        if ($legacySnapshot === []) {
            return [0, 0];
        }

        foreach ($legacySnapshot as $row) {
            $banque = $this->resolveBanqueCatalog($row['paroisse_id']);
            $caisse = Caisse::query()->find($row['caisse_id']);

            if (! $banque || ! $caisse) {
                continue;
            }

            $marker = $row['marker'];

            $revenue = Revenue::query()
                ->where('paroisse_id', $row['paroisse_id'])
                ->whereHas('category', fn ($q) => $q->where('code', 'banque'))
                ->where('notes', 'like', '%'.$marker.'%')
                ->first();

            $createdNow = false;
            if (! $revenue) {
                $reconstructed++;
                $createdNow = true;
                if ($dryRun) {
                    continue;
                }

                $revenue = Revenue::query()->create([
                    'paroisse_id' => $row['paroisse_id'],
                    'revenue_category_id' => $banque['category_id'],
                    'revenue_type_id' => $banque['type_id'],
                    'periode_messe' => null,
                    'jour_semaine' => null,
                    'mois_capital' => $row['mois_capital'],
                    'montant' => $row['montant'],
                    'date_recette' => $row['date_mouvement'],
                    'methode_paiement' => 'especes',
                    'reference_paiement' => 'REV-LEGACY-'.Str::upper(Str::random(8)),
                    'statut' => 'valide',
                    'notes' => trim(($row['notes'] ?? '').' [reconstruit depuis '.$marker.']'),
                    'created_by' => $row['created_by'],
                ]);
            }

            if ($dryRun) {
                if (! $createdNow) {
                    $virements++;
                }

                continue;
            }

            $caisseService->syncCreditFromBanqueRevenue($revenue->fresh(['category', 'type']));

            $alreadyVirement = CaisseMouvement::query()
                ->where('caisse_id', $caisse->id)
                ->where('type', CaisseMouvement::TYPE_VIREMENT)
                ->where('sens', CaisseMouvement::SENS_CREDIT)
                ->where('libelle', 'like', '%'.$marker.'%')
                ->exists();

            if (! $alreadyVirement && $row['montant'] > 0) {
                $this->createVirementBypassingSoldeCheck(
                    $caisse,
                    $row['montant'],
                    $row['date_mouvement'],
                    sprintf(
                        'Virement %s — capital vers %s (%s)',
                        $marker,
                        $caisse->nom,
                        SubventionMensuelle::formatMoisCapital($row['mois_capital'])
                    ),
                    $row['notes'],
                    $row['created_by']
                );
                $virements++;
            }
        }

        return [$reconstructed, $virements];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function relinkFundingAndSyncDepenses(CaisseService $caisseService, bool $dryRun): array
    {
        $fundingUpdated = 0;

        $sources = ExpenseFundingSource::query()
            ->with(['expense', 'revenue.type', 'revenueType'])
            ->where(function ($q): void {
                $q->whereNotNull('revenue_id')
                    ->orWhere(function ($inner): void {
                        $inner->whereNull('caisse_id')->whereNotNull('revenue_type_id');
                    });
            })
            ->get();

        foreach ($sources as $source) {
            if ($source->caisse_id) {
                continue;
            }

            $typeCode = $source->revenue?->type?->code
                ?? $source->revenueType?->code;

            $paroisseId = (int) ($source->expense?->paroisse_id ?? 0);
            $caisseId = $this->resolveCaisseIdFromLegacyType($typeCode, $paroisseId);

            if (! $caisseId) {
                continue;
            }

            $fundingUpdated++;
            if ($dryRun) {
                continue;
            }

            $source->update([
                'caisse_id' => $caisseId,
                'revenue_id' => null,
                'revenue_type_id' => null,
            ]);
        }

        $debits = 0;
        if (! $dryRun) {
            $expenseIds = ExpenseFundingSource::query()
                ->whereNotNull('caisse_id')
                ->pluck('expense_id')
                ->unique()
                ->filter();

            foreach ($expenseIds as $expenseId) {
                $expense = Expense::query()->with('fundingSources')->find($expenseId);
                if ($expense) {
                    $caisseService->syncDepenseMouvements($expense, []);
                    $debits++;
                }
            }
        }

        return [$fundingUpdated, $debits];
    }

    /**
     * @return array{category_id: int, type_id: int}|null
     */
    private function resolveBanqueCatalog(int $paroisseId): ?array
    {
        $categoryId = RevenueCategory::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', 'banque')
            ->value('id');

        if (! $categoryId) {
            return null;
        }

        $typeId = RevenueType::query()
            ->where('paroisse_id', $paroisseId)
            ->where('revenue_category_id', $categoryId)
            ->where('code', 'revenu_principal')
            ->value('id');

        if (! $typeId) {
            $type = RevenueType::query()->create([
                'paroisse_id' => $paroisseId,
                'revenue_category_id' => $categoryId,
                'code' => 'revenu_principal',
                'nom' => 'Revenu principal',
                'description' => 'Capital / revenu principal de l’économat diocésain',
                'actif' => true,
                'ordre' => 1,
            ]);
            $typeId = $type->id;
        }

        return [
            'category_id' => (int) $categoryId,
            'type_id' => (int) $typeId,
        ];
    }

    private function moisCapitalFromSubventionRevenue(Revenue $revenue): string
    {
        $raw = null;
        if (Schema::hasColumn('revenues', 'mois_subvention')) {
            $raw = $revenue->getAttributes()['mois_subvention'] ?? null;
        }

        if (is_string($raw) && preg_match('/^(\d{4})-(\d{2})$/', $raw, $matches)) {
            return $matches[2];
        }

        if (is_string($raw) && preg_match('/^\d{2}$/', $raw)) {
            return $raw;
        }

        return $revenue->date_recette?->format('m') ?? now()->format('m');
    }

    private function createVirementBypassingSoldeCheck(
        Caisse $destination,
        float $montant,
        string $dateMouvement,
        string $libelle,
        ?string $notes = null,
        ?int $createdBy = null
    ): void {
        $tresorerie = Caisse::query()
            ->where('paroisse_id', $destination->paroisse_id)
            ->where('code', Caisse::CODE_TRESORERIE)
            ->firstOrFail();

        $debit = CaisseMouvement::query()->create([
            'paroisse_id' => $tresorerie->paroisse_id,
            'caisse_id' => $tresorerie->id,
            'type' => CaisseMouvement::TYPE_VIREMENT,
            'sens' => CaisseMouvement::SENS_DEBIT,
            'montant' => $montant,
            'date_mouvement' => $dateMouvement,
            'libelle' => $libelle,
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
            'libelle' => $libelle,
            'notes' => $notes,
            'contrepartie_caisse_id' => $tresorerie->id,
            'contrepartie_mouvement_id' => $debit->id,
            'created_by' => $createdBy,
        ]);

        $debit->update(['contrepartie_mouvement_id' => $credit->id]);
    }

    private function resolveCaisseIdFromLegacyType(?string $typeCode, int $paroisseId): ?int
    {
        if (! $typeCode || $paroisseId <= 0) {
            return null;
        }

        $caisseCode = $this->subventionTypeToCaisse[$typeCode] ?? null;
        if (! $caisseCode) {
            return null;
        }

        return Caisse::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', $caisseCode)
            ->value('id');
    }

    private function deactivateSubventionCatalog(bool $dryRun): void
    {
        if ($dryRun) {
            return;
        }

        RevenueCategory::query()
            ->where('code', SubventionMensuelle::CATEGORY_CODE)
            ->update(['actif' => false]);

        RevenueType::query()
            ->where('code', 'like', 'subvention_%')
            ->update(['actif' => false]);
    }
}
