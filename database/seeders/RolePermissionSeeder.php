<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vider le cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Libellés français des permissions (affichage utilisateur)
        $libelles = [
            'view_dashboard' => 'Voir le tableau de bord',
            'view_members' => 'Voir les membres',
            'create_members' => 'Créer des membres',
            'edit_members' => 'Modifier les membres',
            'delete_members' => 'Supprimer des membres',
            'export_members' => 'Exporter les membres',
            'import_members' => 'Importer les membres',
            'view_baptisms' => 'Voir les baptêmes',
            'create_baptisms' => 'Créer des baptêmes',
            'edit_baptisms' => 'Modifier les baptêmes',
            'delete_baptisms' => 'Supprimer des baptêmes',
            'view_confirmations' => 'Voir les confirmations',
            'create_confirmations' => 'Créer des confirmations',
            'edit_confirmations' => 'Modifier les confirmations',
            'delete_confirmations' => 'Supprimer des confirmations',
            'view_communions' => 'Voir les communions',
            'create_communions' => 'Créer des communions',
            'edit_communions' => 'Modifier les communions',
            'delete_communions' => 'Supprimer des communions',
            'view_marriages' => 'Voir les mariages',
            'create_marriages' => 'Créer des mariages',
            'edit_marriages' => 'Modifier les mariages',
            'delete_marriages' => 'Supprimer des mariages',
            'view_funerals' => 'Voir les obsèques',
            'create_funerals' => 'Créer des obsèques',
            'edit_funerals' => 'Modifier les obsèques',
            'delete_funerals' => 'Supprimer des obsèques',
            'view_events' => 'Voir les événements',
            'create_events' => 'Créer des événements',
            'edit_events' => 'Modifier les événements',
            'delete_events' => 'Supprimer des événements',
            'manage_event_participants' => 'Gérer les participants aux événements',
            'view_groups' => 'Voir les groupes',
            'create_groups' => 'Créer des groupes',
            'edit_groups' => 'Modifier les groupes',
            'delete_groups' => 'Supprimer des groupes',
            'manage_group_members' => 'Gérer les membres des groupes',
            'view_revenues' => 'Voir les recettes',
            'create_revenues' => 'Créer des recettes',
            'edit_revenues' => 'Modifier les recettes',
            'delete_revenues' => 'Supprimer des recettes',
            'validate_revenues' => 'Valider les recettes',
            'view_expenses' => 'Voir les dépenses',
            'create_expenses' => 'Créer des dépenses',
            'edit_expenses' => 'Modifier les dépenses',
            'delete_expenses' => 'Supprimer des dépenses',
            'validate_expenses' => 'Valider les dépenses',
            'view_financial_reports' => 'Voir les rapports financiers',
            'generate_financial_reports' => 'Générer des rapports financiers',
            'view_configuration' => 'Voir la configuration',
            'edit_configuration' => 'Modifier la configuration',
            'manage_users' => 'Gérer les utilisateurs',
            'manage_roles' => 'Gérer les rôles',
            'manage_permissions' => 'Gérer les permissions',
            'manage_paroisses' => 'Gérer les paroisses',
        ];

        // ============================================
        // CRÉATION DES PERMISSIONS
        // ============================================

        foreach ($libelles as $name => $libelle) {
            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['libelle_permission' => $libelle]
            );
        }

        // ============================================
        // CRÉATION DES RÔLES
        // ============================================

        // Super Admin - Toutes les permissions
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['libelle_role' => 'Super administrateur']
        );
        $superAdmin->syncPermissions(Permission::all());

        // Paroisse Admin - Presque toutes les permissions sauf gestion globale
        $paroisseAdmin = Role::firstOrCreate(
            ['name' => 'paroisse_admin', 'guard_name' => 'web'],
            ['libelle_role' => 'Administrateur de paroisse']
        );
        $paroisseAdmin->syncPermissions([
            'view_dashboard',
            'view_members', 'create_members', 'edit_members', 'delete_members', 'export_members', 'import_members',
            'view_baptisms', 'create_baptisms', 'edit_baptisms', 'delete_baptisms',
            'view_confirmations', 'create_confirmations', 'edit_confirmations', 'delete_confirmations',
            'view_communions', 'create_communions', 'edit_communions', 'delete_communions',
            'view_marriages', 'create_marriages', 'edit_marriages', 'delete_marriages',
            'view_funerals', 'create_funerals', 'edit_funerals', 'delete_funerals',
            'view_events', 'create_events', 'edit_events', 'delete_events', 'manage_event_participants',
            'view_groups', 'create_groups', 'edit_groups', 'delete_groups', 'manage_group_members',
            'view_revenues', 'create_revenues', 'edit_revenues', 'delete_revenues', 'validate_revenues',
            'view_expenses', 'create_expenses', 'edit_expenses', 'delete_expenses', 'validate_expenses',
            'view_financial_reports', 'generate_financial_reports',
            'view_configuration', 'edit_configuration',
            'manage_users',
            'manage_paroisses',
            'manage_roles',      // Ajouter, modifier, supprimer des rôles
            'manage_permissions', // Ajouter, modifier, supprimer des permissions
        ]);

        // Paroisse Secrétaire - Création et modification, pas de suppression ni validation financière
        $secretaire = Role::firstOrCreate(
            ['name' => 'paroisse_secretaire', 'guard_name' => 'web'],
            ['libelle_role' => 'Secrétaire de paroisse']
        );
        $secretaire->syncPermissions([
            'view_dashboard',
            'view_members', 'create_members', 'edit_members', 'export_members',
            'view_baptisms', 'create_baptisms', 'edit_baptisms',
            'view_confirmations', 'create_confirmations', 'edit_confirmations',
            'view_communions', 'create_communions', 'edit_communions',
            'view_marriages', 'create_marriages', 'edit_marriages',
            'view_funerals', 'create_funerals', 'edit_funerals',
            'view_events', 'create_events', 'edit_events', 'manage_event_participants',
            'view_groups', 'create_groups', 'edit_groups', 'manage_group_members',
            'view_revenues', 'create_revenues', 'edit_revenues',
            'view_expenses', 'create_expenses', 'edit_expenses',
            'view_financial_reports',
            'view_configuration',
        ]);

        // Paroisse Trésorier - Toutes les permissions financières
        $tresorier = Role::firstOrCreate(
            ['name' => 'paroisse_tresorier', 'guard_name' => 'web'],
            ['libelle_role' => 'Trésorier de paroisse']
        );
        $tresorier->syncPermissions([
            'view_dashboard',
            'view_members', 'export_members',
            'view_events',
            'view_revenues', 'create_revenues', 'edit_revenues', 'delete_revenues', 'validate_revenues',
            'view_expenses', 'create_expenses', 'edit_expenses', 'delete_expenses', 'validate_expenses',
            'view_financial_reports', 'generate_financial_reports',
            'view_configuration',
        ]);

        // Paroisse Lecteur - Consultation seule
        $lecteur = Role::firstOrCreate(
            ['name' => 'paroisse_lecteur', 'guard_name' => 'web'],
            ['libelle_role' => 'Lecteur (consultation)']
        );
        $lecteur->syncPermissions([
            'view_dashboard',
            'view_members',
            'view_baptisms',
            'view_confirmations',
            'view_communions',
            'view_marriages',
            'view_funerals',
            'view_events',
            'view_groups',
            'view_revenues',
            'view_expenses',
            'view_financial_reports',
            'view_configuration',
        ]);

        $this->command->info('Rôles et permissions créés avec succès !');
    }
}
