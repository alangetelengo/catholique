<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crée les tables pour la gestion financière de la paroisse :
     * - revenue_categories / revenue_types
     * - revenues : Recettes (quête ordinaire, extraordinaire, location, popote/subvention, procure)
     * - expenses : Dépenses (charges fixes et variables)
     * - financial_reports : Rapports financiers (optionnel, pour historique)
     */
    public function up(): void
    {
        // ============================================
        // TABLE REVENUE_CATEGORIES (Catégories de Recettes)
        // ============================================
        if (! Schema::hasTable('revenue_categories')) {
            Schema::create('revenue_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('code')->unique();
                $table->string('nom');
                $table->text('description')->nullable();
                $table->boolean('actif')->default(true);
                $table->integer('ordre')->default(0);
                $table->timestamps();

                $table->index('code');
                $table->index('actif');
            });
        }

        // ============================================
        // TABLE REVENUE_TYPES (Types de Recettes)
        // ============================================
        if (! Schema::hasTable('revenue_types')) {
            Schema::create('revenue_types', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('revenue_category_id')
                    ->constrained('revenue_categories')
                    ->onDelete('cascade');
                $table->string('code')->unique();
                $table->string('nom');
                $table->text('description')->nullable();
                $table->boolean('actif')->default(true);
                $table->integer('ordre')->default(0);
                $table->timestamps();

                $table->index(['revenue_category_id', 'actif']);
                $table->index('code');
            });
        }

        // ============================================
        // TABLE REVENUES (Recettes)
        // ============================================
        if (! Schema::hasTable('revenues')) {
            Schema::create('revenues', function (Blueprint $table): void {
                $table->id();

            $table->foreignId('paroisse_id')
                ->constrained('paroisses')
                ->onDelete('cascade');

            $table->foreignId('revenue_category_id')
                ->constrained('revenue_categories')
                ->onDelete('restrict');

            $table->foreignId('revenue_type_id')
                ->constrained('revenue_types')
                ->onDelete('restrict');

            $table->enum('periode_messe', ['semaine', 'dimanche'])->nullable();
            $table->enum('jour_semaine', [
                'lundi', 'mardi', 'mercredi', 'jeudi',
                'vendredi', 'samedi', 'dimanche',
            ])->nullable();

            $table->foreignId('event_id')
                ->nullable()
                ->constrained('events')
                ->onDelete('set null');

            $table->decimal('montant', 10, 2);
            $table->date('date_recette');

            $table->boolean('est_recurrent')->default(false);
            $table->enum('frequence_recurrence', ['mensuel', 'trimestriel', 'annuel'])
                ->nullable();

            $table->enum('methode_paiement', [
                'especes',
                'cheque',
                'virement',
                'carte',
                'mobile_money',
            ])->default('especes');
            $table->string('reference_paiement')->nullable();

            $table->enum('statut', ['en_attente', 'valide', 'rejete'])
                ->default('valide');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();

            $table->index(['paroisse_id', 'date_recette']);
            $table->index(['revenue_category_id', 'date_recette']);
            $table->index(['revenue_type_id', 'date_recette']);
            $table->index(['periode_messe', 'date_recette']);
            $table->index(['est_recurrent', 'frequence_recurrence']);
                $table->index('statut');
            });
        }

        // Les catégories et types de recettes sont créés par les seeders uniquement
        // (RevenueCategorySeeder, RevenueTypeSeeder) pour éviter les doublons.
        // Exécuter : php artisan db:seed

        // ============================================
        // TABLE EXPENSES (Dépenses/Charges)
        // ============================================
        if (! Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table): void {
                $table->id();

            $table->foreignId('paroisse_id')
                ->constrained('paroisses')
                ->onDelete('cascade');

            $table->enum('categorie_charge', [
                'charge_fixe',
                'charge_variable',
                'charge_exceptionnelle',
            ])->default('charge_fixe');

            $table->enum('type_charge', [
                'carburant',
                'hosties',
                'internet',
                'maintenance_materiel',
                'gaz',
                'eau',
                'electricite',
                'gardiennage',
                'salaire_ouvrier',
                'autre',
            ]);

            $table->decimal('montant', 10, 2);
            $table->date('date_depense');

            $table->string('facture_reference')->nullable();
            $table->string('fournisseur')->nullable();

            $table->enum('methode_paiement', [
                'especes',
                'cheque',
                'virement',
                'carte',
                'mobile_money',
            ])->default('especes');

            $table->enum('statut', ['en_attente', 'valide', 'rejete'])
                ->default('valide');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();

            $table->index(['paroisse_id', 'date_depense']);
            $table->index(['categorie_charge', 'date_depense']);
            $table->index(['type_charge', 'date_depense']);
                $table->index('statut');
            });
        }

        // ============================================
        // TABLE FINANCIAL_REPORTS (Rapports Financiers)
        // ============================================
        if (! Schema::hasTable('financial_reports')) {
            Schema::create('financial_reports', function (Blueprint $table): void {
                $table->id();

            $table->foreignId('paroisse_id')
                ->constrained('paroisses')
                ->onDelete('cascade');

            $table->enum('periode_type', ['semaine', 'dimanche', 'total']);
            $table->date('date_debut');
            $table->date('date_fin');

            $table->decimal('total_recettes', 10, 2)->default(0);
            $table->decimal('total_depenses', 10, 2)->default(0);
            $table->decimal('solde', 10, 2)->default(0);

            $table->json('details_recettes')->nullable();
            $table->json('details_depenses')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict');

            $table->timestamps();

            $table->index(['paroisse_id', 'date_debut', 'date_fin']);
                $table->index('periode_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('revenues');
        Schema::dropIfExists('revenue_types');
        Schema::dropIfExists('revenue_categories');
    }
};

