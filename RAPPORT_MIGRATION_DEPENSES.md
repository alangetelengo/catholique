# RAPPORT - MIGRATION DÉPENSES

## Fichiers à adapter

Après le changement du modèle de dépenses, les fichiers suivants doivent être adaptés :

### Contrôleurs principaux (URGENT)
1. ✅ `app/Http/Controllers/FinancialReportController.php` (ligne 226-229) - PARTIELLEMENT CORRIGÉ
2. ❌ `app/Http/Controllers/PopoteSubventionReportController.php` (ligne 240)
3. ❌ `app/Http/Controllers/ChargesFixesReportController.php` (ligne 192)
4. ❌ `app/Services/FinancialStatisticsService.php` (plusieurs lignes)

### API
5. ❌ `app/Http/Controllers/Api/SyncController.php` (lignes 46, 138)

### Vues (MOINS URGENT - affichage seulement)
6. ❌ `resources/views/financial-reports/expenses-by-category.blade.php`
7. ❌ `resources/views/financial-reports/partials/expenses-by-category-report-body.blade.php`
8. ❌ `resources/views/financial-reports/pdf.blade.php`
9. ❌ `resources/views/financial-reports/expenses-by-category-pdf.blade.php`
10. ❌ `resources/views/expenses/index.blade.php`
11. ❌ `resources/views/financial-reports/index.blade.php`
12. ❌ `resources/views/financial-reports/show.blade.php`
13. ❌ `resources/views/dashboard/index.blade.php`

### Seeders (À SUPPRIMER ou ADAPTER)
14. ❌ `database/seeders/ExpenseSeeder.php`

## Solution rapide (pour débloquer l'application)

Option 1: Commenter temporairement les rapports qui utilisent `categorie_charge`
Option 2: Adapter complètement les rapports à la nouvelle logique

## Nouvelle logique proposée

Au lieu de regrouper les dépenses par catégorie de charge (fixe, variable, popote), 
on les regroupe maintenant par **source de revenu** :

- Dépenses financées par "Quête Ordinaire"
- Dépenses financées par "Subvention Carburant"
- Dépenses financées par "Subvention Popote"
- etc.

Souhaitez-vous que je :
A. Désactive temporairement les rapports concernés
B. Adapte complètement tous les rapports (travail important)
C. Crée une solution hybride (garder l'ancien système pour l'historique)
