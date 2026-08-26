<?php

use App\Models\Caisse;
use App\Models\CaisseMouvement;
use App\Models\Expense;
use App\Models\ExpenseFundingSource;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use Database\Seeders\CaisseSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

    public function up(): void
    {
        Schema::table('revenues', function (Blueprint $table): void {
            if (! Schema::hasColumn('revenues', 'mois_capital')) {
                $table->string('mois_capital', 2)->nullable()->after('mois_location');
            }
        });

        $this->ensureCaisses();
        $this->normalizeBanqueCatalog();
        $this->normalizeBanqueRevenues();
        $this->creditBanqueToTresorerie();
        $this->convertSubventionsToCaisses();
        $this->deactivateSubventionCatalog();

        Schema::table('revenues', function (Blueprint $table): void {
            if (Schema::hasColumn('revenues', 'mois_subvention')) {
                if (Schema::hasIndex('revenues', 'revenues_popote_mois_idx')) {
                    $table->dropIndex('revenues_popote_mois_idx');
                }

                $table->dropColumn('mois_subvention');
            }
        });
    }

    public function down(): void
    {
        Schema::table('revenues', function (Blueprint $table): void {
            if (! Schema::hasColumn('revenues', 'mois_subvention')) {
                $table->string('mois_subvention', 7)->nullable()->after('mois_location');
                $table->index(['paroisse_id', 'revenue_type_id', 'mois_subvention'], 'revenues_popote_mois_idx');
            }

            if (Schema::hasColumn('revenues', 'mois_capital')) {
                $table->dropColumn('mois_capital');
            }
        });
    }

    private function ensureCaisses(): void
    {
        if (! Schema::hasTable('caisses')) {
            return;
        }

        $paroisseIds = DB::table('paroisses')->pluck('id');
        foreach ($paroisseIds as $paroisseId) {
            foreach (CaisseSeeder::definitions() as $definition) {
                Caisse::query()->firstOrCreate(
                    [
                        'paroisse_id' => $paroisseId,
                        'code' => $definition['code'],
                    ],
                    [
                        'nom' => $definition['nom'],
                        'description' => $definition['description'],
                        'est_tresorerie' => $definition['est_tresorerie'],
                        'actif' => true,
                        'ordre' => $definition['ordre'],
                    ]
                );
            }
        }
    }

    private function normalizeBanqueCatalog(): void
    {
        RevenueCategory::query()
            ->whereIn('code', ['capita', 'capital', 'banque'])
            ->update([
                'code' => 'banque',
                'nom' => 'BANQUE (économat diocésain)',
                'description' => 'Versements de la hiérarchie / économat diocésain — trésorerie générale uniquement',
                'actif' => true,
                'ordre' => 0,
            ]);

        $banqueIds = RevenueCategory::query()->where('code', 'banque')->pluck('id', 'paroisse_id');

        foreach (RevenueType::query()->whereIn('code', ['rev-principal', 'rev_principal', 'revenu_principal'])->get() as $type) {
            $categoryId = $banqueIds[$type->paroisse_id] ?? $banqueIds->first();
            $type->update([
                'code' => 'revenu_principal',
                'nom' => 'Revenu principal',
                'revenue_category_id' => $categoryId ?: $type->revenue_category_id,
                'actif' => true,
            ]);
        }
    }

    private function normalizeBanqueRevenues(): void
    {
        $banqueCategoryIds = RevenueCategory::query()->where('code', 'banque')->pluck('id');

        Revenue::withTrashed()
            ->whereIn('revenue_category_id', $banqueCategoryIds)
            ->get()
            ->each(function (Revenue $revenue): void {
                if ($revenue->trashed()) {
                    $revenue->restore();
                }

                $mois = $revenue->mois_capital;
                if (! $mois && $revenue->date_recette) {
                    $mois = $revenue->date_recette->format('m');
                }

                $revenue->update([
                    'mois_capital' => $mois,
                    'mois_location' => null,
                ]);
            });
    }

    private function creditBanqueToTresorerie(): void
    {
        if (! Schema::hasTable('caisse_mouvements')) {
            return;
        }

        $revenues = Revenue::query()
            ->with(['category', 'type'])
            ->whereHas('category', fn ($q) => $q->where('code', 'banque'))
            ->where('statut', 'valide')
            ->get();

        foreach ($revenues as $revenue) {
            $tresorerie = Caisse::query()
                ->where('paroisse_id', $revenue->paroisse_id)
                ->where('code', Caisse::CODE_TRESORERIE)
                ->first();

            if (! $tresorerie) {
                continue;
            }

            $exists = CaisseMouvement::query()
                ->where('revenue_id', $revenue->id)
                ->where('type', CaisseMouvement::TYPE_CREDIT_RECETTE)
                ->exists();

            if ($exists) {
                continue;
            }

            $moisLabel = $revenue->mois_capital
                ? match ($revenue->mois_capital) {
                    '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
                    '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
                    '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre',
                    default => $revenue->mois_capital,
                }
            : '';

            CaisseMouvement::query()->create([
                'paroisse_id' => $revenue->paroisse_id,
                'caisse_id' => $tresorerie->id,
                'type' => CaisseMouvement::TYPE_CREDIT_RECETTE,
                'sens' => CaisseMouvement::SENS_CREDIT,
                'montant' => $revenue->montant,
                'date_mouvement' => $revenue->date_recette?->format('Y-m-d') ?? now()->toDateString(),
                'libelle' => 'Recette Banque — '.($revenue->type?->nom ?? 'Revenu principal')
                    .($moisLabel !== '' ? ' ('.$moisLabel.')' : ''),
                'revenue_id' => $revenue->id,
                'revenue_type_id' => $revenue->revenue_type_id,
                'created_by' => $revenue->created_by,
            ]);
        }
    }

    private function convertSubventionsToCaisses(): void
    {
        if (! Schema::hasTable('caisse_mouvements') || ! Schema::hasColumn('expense_funding_sources', 'caisse_id')) {
            return;
        }

        $subventionCategoryIds = RevenueCategory::query()->where('code', 'subvention')->pluck('id');
        if ($subventionCategoryIds->isEmpty()) {
            return;
        }

        $revenues = Revenue::query()
            ->with('type')
            ->whereIn('revenue_category_id', $subventionCategoryIds)
            ->where('statut', 'valide')
            ->get();

        $revenueToCaisseId = [];

        foreach ($revenues as $revenue) {
            $typeCode = $revenue->type?->code ?? '';
            $caisseCode = $this->subventionTypeToCaisse[$typeCode] ?? 'divers';
            $caisse = Caisse::query()
                ->where('paroisse_id', $revenue->paroisse_id)
                ->where('code', $caisseCode)
                ->first();

            $banqueCategoryId = RevenueCategory::query()
                ->where('paroisse_id', $revenue->paroisse_id)
                ->where('code', 'banque')
                ->value('id');

            $banqueTypeId = $banqueCategoryId
                ? RevenueType::query()
                    ->where('paroisse_id', $revenue->paroisse_id)
                    ->where('revenue_category_id', $banqueCategoryId)
                    ->where('code', 'revenu_principal')
                    ->value('id')
                : null;

            if (! $caisse || ! $banqueCategoryId || ! $banqueTypeId) {
                continue;
            }

            $revenueToCaisseId[$revenue->id] = $caisse->id;

            $moisRaw = Schema::hasColumn('revenues', 'mois_subvention')
                ? ($revenue->getAttributes()['mois_subvention'] ?? null)
                : null;
            $moisCapital = is_string($moisRaw) && preg_match('/^(\d{4})-(\d{2})$/', $moisRaw, $m)
                ? $m[2]
                : ($revenue->date_recette?->format('m') ?? now()->format('m'));

            $moisLabel = match ($moisCapital) {
                '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
                '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
                '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre',
                default => $moisCapital,
            };

            $revenue->update([
                'revenue_category_id' => $banqueCategoryId,
                'revenue_type_id' => $banqueTypeId,
                'mois_capital' => $moisCapital,
                'mois_location' => null,
            ]);

            $tresorerie = Caisse::query()
                ->where('paroisse_id', $revenue->paroisse_id)
                ->where('code', Caisse::CODE_TRESORERIE)
                ->first();

            if (! $tresorerie) {
                continue;
            }

            $existsCredit = CaisseMouvement::query()
                ->where('revenue_id', $revenue->id)
                ->where('type', CaisseMouvement::TYPE_CREDIT_RECETTE)
                ->exists();

            if (! $existsCredit) {
                CaisseMouvement::query()->create([
                    'paroisse_id' => $revenue->paroisse_id,
                    'caisse_id' => $tresorerie->id,
                    'type' => CaisseMouvement::TYPE_CREDIT_RECETTE,
                    'sens' => CaisseMouvement::SENS_CREDIT,
                    'montant' => $revenue->montant,
                    'date_mouvement' => $revenue->date_recette?->format('Y-m-d') ?? now()->toDateString(),
                    'libelle' => 'Recette Banque — Revenu principal ('.$moisLabel.')',
                    'revenue_id' => $revenue->id,
                    'revenue_type_id' => $banqueTypeId,
                    'created_by' => $revenue->created_by,
                ]);
            }

            $existsVirement = CaisseMouvement::query()
                ->where('caisse_id', $caisse->id)
                ->where('type', CaisseMouvement::TYPE_VIREMENT)
                ->where('sens', CaisseMouvement::SENS_CREDIT)
                ->where('libelle', 'like', '%legacy#'.$revenue->id.'%')
                ->exists();

            if (! $existsVirement && (float) $revenue->montant > 0) {
                $libelle = sprintf(
                    'Virement legacy#%d — capital vers %s (%s)',
                    $revenue->id,
                    $caisse->nom,
                    $moisLabel
                );

                $debit = CaisseMouvement::query()->create([
                    'paroisse_id' => $tresorerie->paroisse_id,
                    'caisse_id' => $tresorerie->id,
                    'type' => CaisseMouvement::TYPE_VIREMENT,
                    'sens' => CaisseMouvement::SENS_DEBIT,
                    'montant' => $revenue->montant,
                    'date_mouvement' => $revenue->date_recette?->format('Y-m-d') ?? now()->toDateString(),
                    'libelle' => $libelle,
                    'notes' => $revenue->notes,
                    'contrepartie_caisse_id' => $caisse->id,
                    'created_by' => $revenue->created_by,
                ]);

                $credit = CaisseMouvement::query()->create([
                    'paroisse_id' => $caisse->paroisse_id,
                    'caisse_id' => $caisse->id,
                    'type' => CaisseMouvement::TYPE_VIREMENT,
                    'sens' => CaisseMouvement::SENS_CREDIT,
                    'montant' => $revenue->montant,
                    'date_mouvement' => $revenue->date_recette?->format('Y-m-d') ?? now()->toDateString(),
                    'libelle' => $libelle,
                    'notes' => $revenue->notes,
                    'contrepartie_caisse_id' => $tresorerie->id,
                    'contrepartie_mouvement_id' => $debit->id,
                    'created_by' => $revenue->created_by,
                ]);

                $debit->update(['contrepartie_mouvement_id' => $credit->id]);
            }
        }

        $sources = ExpenseFundingSource::query()
            ->whereNotNull('revenue_id')
            ->whereIn('revenue_id', array_keys($revenueToCaisseId))
            ->get();

        $expenseIds = [];
        foreach ($sources as $source) {
            $caisseId = $revenueToCaisseId[$source->revenue_id] ?? null;
            if (! $caisseId) {
                continue;
            }

            $source->update([
                'caisse_id' => $caisseId,
                'revenue_id' => null,
                'revenue_type_id' => null,
            ]);
            $expenseIds[$source->expense_id] = true;
        }

        foreach (array_keys($expenseIds) as $expenseId) {
            $expense = Expense::query()->with('fundingSources')->find($expenseId);
            if (! $expense) {
                continue;
            }

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
                    'libelle' => $expense->libelle,
                    'expense_id' => $expense->id,
                    'expense_funding_source_id' => $source->id,
                    'created_by' => $expense->created_by,
                ]);
            }
        }
    }

    private function deactivateSubventionCatalog(): void
    {
        RevenueCategory::query()->where('code', 'subvention')->update(['actif' => false]);
        RevenueType::query()->where('code', 'like', 'subvention_%')->update(['actif' => false]);
    }
};
