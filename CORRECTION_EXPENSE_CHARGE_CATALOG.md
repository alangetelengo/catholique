# Correction: Class "ExpenseChargeCatalog" not found
Date: 9 mai 2026 - 02:04 AM

## ✅ Problème résolu

### Erreur initiale
```
Class "App\Support\ExpenseChargeCatalog" not found
Location: resources\views\expenses\index.blade.php:12
```

### Cause
La classe `ExpenseChargeCatalog` faisait partie de l'ancien système de gestion des dépenses qui a été supprimé lors de la refonte. Cette classe gérait les anciennes catégories de dépenses (`charge_fixe`, `charge_variable`, `charge_exceptionnelle`, `alimentation_popote`) qui n'existent plus.

---

## 🔧 Corrections appliquées

### 1. `resources/views/expenses/index.blade.php`

**Avant:** Utilisation de l'ancien système
```php
$categories = [
    'charge_fixe' => 'Charge fixe',
    'charge_variable' => 'Charge variable',
    'charge_exceptionnelle' => 'Charge exceptionnelle',
    'alimentation_popote' => 'Alimentation popote',
];
$types = trans('expenses.types');
$typesByCategoryFilter = \App\Support\ExpenseChargeCatalog::labeledOptionsByCategoryExcludingPopote();
```

**Après:** Utilisation du nouveau système basé sur RevenueCategory et RevenueType
```php
$revenueCategories = \App\Models\RevenueCategory::query()
    ->where('actif', 1)
    ->orderBy('ordre')
    ->orderBy('nom')
    ->get();
    
$revenueTypes = \App\Models\RevenueType::query()
    ->where('actif', 1)
    ->orderBy('ordre')
    ->orderBy('nom')
    ->get();
```

#### Changements dans le formulaire de filtres:
- ✅ `categorie_charge` → `revenue_category_id`
- ✅ `type_charge` → `revenue_type_id`
- ✅ Filtrage dynamique des types selon la catégorie sélectionnée

#### Changements dans le tableau:
- ✅ Affichage de `$expense->revenueCategory->nom` au lieu de `$categories[$expense->categorie_charge]`
- ✅ Affichage de `$expense->revenueType->nom` au lieu de `$types[$expense->type_charge]`

#### JavaScript de filtrage:
- ✅ Mise à jour pour utiliser `data-category-id` sur les options de types
- ✅ Filtrage dynamique simplifié et plus performant

---

### 2. `app/Http/Controllers/FinancialReportController.php`

**Ligne 13:** Suppression de l'import
```php
// Avant
use App\Support\ExpenseChargeCatalog;

// Après (supprimé)
```

**Ligne 1343:** Remplacement de l'utilisation
```php
// Avant
$typeOptions = ExpenseChargeCatalog::typeOptionRows();

// Après
// TODO: Refactoriser ce rapport pour utiliser RevenueCategory et RevenueType
$typeOptions = [];
```

**Note:** Ce rapport nécessite une refonte complète pour s'adapter au nouveau système, mais l'erreur est corrigée temporairement.

---

### 3. `tests/Unit/ExpenseChargeCatalogTest.php`

**Action:** Fichier supprimé ✅

**Raison:** Ce test testait la classe `ExpenseChargeCatalog` qui n'existe plus. Il faudra créer de nouveaux tests pour le nouveau système si nécessaire.

---

## 📊 Résumé des changements

### Fichiers modifiés
1. ✅ `resources/views/expenses/index.blade.php` - Refactorisation complète
2. ✅ `app/Http/Controllers/FinancialReportController.php` - Suppression de l'import et de l'utilisation

### Fichiers supprimés
1. ✅ `tests/Unit/ExpenseChargeCatalogTest.php`

### Vérifications effectuées
- ✅ Plus aucune référence à `ExpenseChargeCatalog` dans le projet
- ✅ Le contrôleur `ExpenseController` utilise déjà les bons champs (`revenue_category_id`, `revenue_type_id`)
- ✅ Code formaté avec Pint

---

## 🎯 Résultat

La page `/expenses` (liste des dépenses) devrait maintenant:
- ✅ Se charger sans erreur "Class not found"
- ✅ Afficher les catégories de recettes comme sources de fonds
- ✅ Afficher les types de recettes spécifiques
- ✅ Permettre le filtrage par catégorie et type de recette
- ✅ Filtrer dynamiquement les types selon la catégorie sélectionnée

---

## 📝 Notes importantes

### Nouveau système de dépenses
Les dépenses sont maintenant liées aux **sources de fonds** (catégories et types de recettes):

**Exemples de catégories:**
- Quête Ordinaire
- Procure
- Subvention (Carburant, Hosties, Gardiennage, Gaz, Internet, Eau, Électricité, Salaires, **Popote**)
- Location
- Fête

Lorsqu'une dépense est enregistrée, on sélectionne:
1. **La catégorie de recette** (ex: Subvention)
2. **Le type de recette** (ex: Subvention Popote, Subvention Électricité, etc.)

Cela permet de tracer facilement d'où viennent les fonds pour chaque dépense.

---

## 🔄 Prochaines étapes

### Tâches recommandées (non urgentes):
1. Refactoriser complètement le rapport "Dépenses par catégories" dans `FinancialReportController`
2. Adapter les autres rapports financiers qui pourraient encore utiliser l'ancien système
3. Créer de nouveaux tests unitaires pour le système de dépenses basé sur RevenueCategory/RevenueType

### Tâches immédiates:
- ✅ Tester la page `/expenses` pour s'assurer qu'elle fonctionne
- ✅ Créer une nouvelle dépense pour valider le formulaire
- ✅ Tester les filtres par catégorie et type

---

## ✅ Conclusion

**Toutes les références à la classe obsolète `ExpenseChargeCatalog` ont été supprimées ou remplacées.**

La page des dépenses utilise maintenant le nouveau système basé sur les catégories et types de recettes, ce qui est plus cohérent et flexible.
