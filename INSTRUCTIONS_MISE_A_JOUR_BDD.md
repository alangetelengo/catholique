# Instructions de mise à jour de la base de données de production

## ⚠️ IMPORTANT - À LIRE AVANT TOUTE OPÉRATION

### ⚠️ DIFFÉRENCE LOCAL vs PRODUCTION

**ATTENTION :** Ces scripts sont conçus pour la base de données **PRODUCTION** où la table s'appelle `paroisses` (pluriel).

Si vous avez une base de données locale où la table s'appelle `paroisse` (singulier), vous devez modifier tous les scripts en remplaçant `paroisses` par `paroisse`.

### Précautions avant l'exécution

1. **FAIRE UNE SAUVEGARDE COMPLÈTE** de la base de données de production
   ```bash
   mysqldump -u utilisateur -p nom_base > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Tester d'abord sur une copie** de la base de données de production

3. **Prévoir une fenêtre de maintenance** (les utilisateurs ne doivent pas utiliser l'application)

4. **Vérifier les permissions** MySQL nécessaires :
   - ALTER TABLE
   - CREATE TABLE
   - DROP TABLE
   - INSERT
   - DELETE
   - TRUNCATE

## 📋 Scripts disponibles

### 1. `update_production_database.sql` (Recommandé)
Script complet avec vérifications conditionnelles. Plus robuste mais nécessite MySQL 5.7+.

**Utilisation :**
```bash
mysql -u utilisateur -p nom_base < update_production_database.sql
```

### 2. `update_production_simple.sql` (Alternative)
Version simplifiée sans vérifications complexes. À utiliser si la version complète échoue.

**Utilisation :**
```bash
mysql -u utilisateur -p nom_base < update_production_simple.sql
```

**Note :** Certaines commandes peuvent échouer si les colonnes/contraintes existent déjà. C'est normal, continuez.

## 🔄 Ce que font les scripts

### ✅ Actions effectuées

1. **Suppression de toutes les dépenses**
   - TRUNCATE de la table `expenses`
   - ⚠️ Toutes les dépenses seront perdues définitivement

2. **Modification de la table `expenses`**
   - Ajout de `revenue_category_id` (lien vers catégorie de recette)
   - Ajout de `revenue_type_id` (lien vers type de recette)
   - Suppression de `categorie_charge`
   - Suppression de `type_charge`

3. **Nettoyage des catégories de revenus**
   - Suppression de la catégorie "popote_subvention" et ses données
   - Suppression des types incorrects
   - Suppression des catégories orphelines

4. **Ajout de la catégorie "Fête"**
   - Création de la catégorie pour chaque paroisse
   - Ajout de 4 types de fêtes :
     * Fête patronale paroissiale
     * Repas du doyenné
     * Rencontre du doyenné
     * Repas des natifs

5. **Vérification de "Subvention Popote"**
   - S'assure que le type existe dans la catégorie Subvention

### ✅ Données préservées

- **Tous les revenus existants** (aucune donnée de revenu ne sera supprimée)
- **Toutes les paroisses**
- **Tous les utilisateurs**
- **Toutes les autres tables**

## 📊 Résultat attendu

Après l'exécution, vous devriez avoir :

### Catégories de revenus (6)
1. Quête Ordinaire
2. Quête Extraordinaire
3. Location
4. Subvention
5. Procure
6. **Fête** (nouvelle)

### Structure de la table expenses
```sql
- id
- paroisse_id
- revenue_category_id  (NOUVEAU)
- revenue_type_id      (NOUVEAU)
- montant
- date_depense
- jour_semaine
- libelle
- facture_reference
- piece_facture_path
- piece_recu_path
- fournisseur
- methode_paiement
- statut
- notes
- created_by
- validated_by
- validated_at
- created_at
- updated_at
- deleted_at
```

## 🔍 Vérifications post-exécution

### 1. Vérifier la structure de la table expenses
```sql
DESCRIBE expenses;
```

### 2. Vérifier les catégories de revenus
```sql
SELECT code, nom, COUNT(*) as nb_paroisses 
FROM revenue_categories 
WHERE actif = 1 
GROUP BY code, nom;
```
Résultat attendu : 6 catégories pour chaque paroisse

### 3. Vérifier les types de la catégorie "Fête"
```sql
SELECT rt.code, rt.nom 
FROM revenue_types rt
JOIN revenue_categories rc ON rt.revenue_category_id = rc.id
WHERE rc.code = 'fete'
ORDER BY rt.ordre;
```
Résultat attendu : 4 types de fêtes

### 4. Vérifier que les dépenses sont vides
```sql
SELECT COUNT(*) FROM expenses;
```
Résultat attendu : 0

### 5. Vérifier que les revenus sont préservés
```sql
SELECT COUNT(*) as total_revenus FROM revenues WHERE deleted_at IS NULL;
```
Le nombre doit correspondre au nombre de revenus avant la mise à jour

## ⚠️ En cas de problème

### Erreur "Column already exists"
**Solution :** Utilisez `update_production_simple.sql` et ignorez ces erreurs, elles sont normales.

### Erreur "Cannot delete or update a parent row"
**Cause :** Des dépenses référencent encore des catégories/types à supprimer.

**Solution :**
```sql
-- Supprimer d'abord toutes les dépenses
TRUNCATE TABLE expenses;
-- Puis relancer le script
```

### Erreur de syntaxe avec les variables (@)
**Solution :** Utilisez `update_production_simple.sql` au lieu de la version complète.

## 📝 Rollback (en cas d'échec)

Si quelque chose se passe mal :

1. **Arrêter immédiatement**
2. **Restaurer la sauvegarde**
   ```bash
   mysql -u utilisateur -p nom_base < backup_YYYYMMDD_HHMMSS.sql
   ```
3. **Contacter le support technique**

## 📞 Support

En cas de problème, fournir :
- Le message d'erreur complet
- La version de MySQL (`SELECT VERSION();`)
- Les logs d'exécution du script

---

**Date de création :** 2026-05-08  
**Version :** 1.0  
**Auteur :** Script automatisé de migration
