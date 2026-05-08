-- ================================================================
-- SCRIPT DE MISE À JOUR DE LA BASE DE DONNÉES - PRODUCTION
-- Date: 2026-05-08
-- Description: 
--   - Supprime toutes les dépenses
--   - Met à jour la structure de la table expenses
--   - Ajoute/met à jour les catégories et types de revenus
--   - Préserve toutes les données de revenus
-- ================================================================

-- ================================================================
-- ÉTAPE 1 : SUPPRESSION DE TOUTES LES DÉPENSES
-- ================================================================

-- Suppression logique (soft delete) de toutes les dépenses
UPDATE expenses SET deleted_at = NOW() WHERE deleted_at IS NULL;

-- Suppression physique de toutes les dépenses
TRUNCATE TABLE expenses;

-- ================================================================
-- ÉTAPE 2 : MODIFICATION DE LA STRUCTURE DE LA TABLE EXPENSES
-- ================================================================

-- Ajouter les nouvelles colonnes pour lier aux revenus (si elles n'existent pas)
ALTER TABLE expenses 
ADD COLUMN IF NOT EXISTS revenue_category_id BIGINT UNSIGNED NULL AFTER paroisse_id,
ADD COLUMN IF NOT EXISTS revenue_type_id BIGINT UNSIGNED NULL AFTER revenue_category_id;

-- Ajouter les contraintes de clés étrangères (si elles n'existent pas)
SET @fk_exists_category = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'expenses' 
    AND CONSTRAINT_NAME = 'expenses_revenue_category_id_foreign'
);

SET @fk_exists_type = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'expenses' 
    AND CONSTRAINT_NAME = 'expenses_revenue_type_id_foreign'
);

SET @sql_category = IF(@fk_exists_category = 0,
    'ALTER TABLE expenses ADD CONSTRAINT expenses_revenue_category_id_foreign FOREIGN KEY (revenue_category_id) REFERENCES revenue_categories(id) ON DELETE RESTRICT',
    'SELECT "La contrainte expenses_revenue_category_id_foreign existe déjà" as message'
);
PREPARE stmt_category FROM @sql_category;
EXECUTE stmt_category;
DEALLOCATE PREPARE stmt_category;

SET @sql_type = IF(@fk_exists_type = 0,
    'ALTER TABLE expenses ADD CONSTRAINT expenses_revenue_type_id_foreign FOREIGN KEY (revenue_type_id) REFERENCES revenue_types(id) ON DELETE RESTRICT',
    'SELECT "La contrainte expenses_revenue_type_id_foreign existe déjà" as message'
);
PREPARE stmt_type FROM @sql_type;
EXECUTE stmt_type;
DEALLOCATE PREPARE stmt_type;

-- Supprimer les anciennes colonnes (si elles existent)
SET @col_exists_categorie = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'expenses' 
    AND COLUMN_NAME = 'categorie_charge'
);

SET @col_exists_type = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'expenses' 
    AND COLUMN_NAME = 'type_charge'
);

SET @sql_drop_categorie = IF(@col_exists_categorie > 0,
    'ALTER TABLE expenses DROP COLUMN categorie_charge',
    'SELECT "La colonne categorie_charge n''existe pas" as message'
);
PREPARE stmt_drop_categorie FROM @sql_drop_categorie;
EXECUTE stmt_drop_categorie;
DEALLOCATE PREPARE stmt_drop_categorie;

SET @sql_drop_type = IF(@col_exists_type > 0,
    'ALTER TABLE expenses DROP COLUMN type_charge',
    'SELECT "La colonne type_charge n''existe pas" as message'
);
PREPARE stmt_drop_type FROM @sql_drop_type;
EXECUTE stmt_drop_type;
DEALLOCATE PREPARE stmt_drop_type;

-- ================================================================
-- ÉTAPE 3 : NETTOYAGE DES CATÉGORIES DE REVENUS INCORRECTES
-- ================================================================

-- Supprimer les revenus liés à la catégorie "popote_subvention"
DELETE FROM revenues WHERE revenue_category_id IN (
    SELECT id FROM revenue_categories WHERE code = 'popote_subvention'
);

-- Supprimer les types de la catégorie "popote_subvention"
DELETE FROM revenue_types WHERE revenue_category_id IN (
    SELECT id FROM revenue_categories WHERE code = 'popote_subvention'
);

-- Supprimer la catégorie "popote_subvention"
DELETE FROM revenue_categories WHERE code = 'popote_subvention';

-- Supprimer les types de revenus incorrects
DELETE FROM revenue_types WHERE code IN ('subvention_alimentation_popote', 'autres_subventions');

-- Supprimer les catégories orphelines (sans paroisse_id)
DELETE rt FROM revenue_types rt 
WHERE rt.revenue_category_id IN (
    SELECT id FROM revenue_categories WHERE paroisse_id IS NULL
);

DELETE FROM revenue_categories WHERE paroisse_id IS NULL;

-- Supprimer les catégories non standards
DELETE rt FROM revenue_types rt 
WHERE rt.revenue_category_id IN (
    SELECT id FROM revenue_categories WHERE code NOT IN ('quete_ordinaire', 'quete_extraordinaire', 'location', 'subvention', 'procure', 'fete')
);

DELETE FROM revenue_categories WHERE code NOT IN ('quete_ordinaire', 'quete_extraordinaire', 'location', 'subvention', 'procure', 'fete');

-- ================================================================
-- ÉTAPE 4 : AJOUT DE LA CATÉGORIE "FÊTE" (SI ELLE N'EXISTE PAS)
-- ================================================================

-- Pour chaque paroisse, ajouter la catégorie "Fête" si elle n'existe pas
INSERT INTO revenue_categories (paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    p.id,
    'fete',
    'Fête',
    'Fêtes de la paroisse',
    1,
    6,
    NOW(),
    NOW()
FROM paroisses p
WHERE NOT EXISTS (
    SELECT 1 FROM revenue_categories rc 
    WHERE rc.paroisse_id = p.id AND rc.code = 'fete'
);

-- ================================================================
-- ÉTAPE 5 : AJOUT DES TYPES DE FÊTES (SI ILS N'EXISTENT PAS)
-- ================================================================

-- Type 1: Fête patronale paroissiale
INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id,
    rc.paroisse_id,
    'fete-paroisse',
    'Fête patronale paroissiale',
    'Anniversaire de la paroisse',
    1,
    1,
    NOW(),
    NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (
    SELECT 1 FROM revenue_types rt 
    WHERE rt.revenue_category_id = rc.id AND rt.code = 'fete-paroisse'
);

-- Type 2: Repas du doyenné
INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id,
    rc.paroisse_id,
    'repas-doy',
    'Repas du doyenné',
    NULL,
    1,
    2,
    NOW(),
    NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (
    SELECT 1 FROM revenue_types rt 
    WHERE rt.revenue_category_id = rc.id AND rt.code = 'repas-doy'
);

-- Type 3: Rencontre du doyenné
INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id,
    rc.paroisse_id,
    'renc-doy',
    'Rencontre du doyenné',
    NULL,
    1,
    3,
    NOW(),
    NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (
    SELECT 1 FROM revenue_types rt 
    WHERE rt.revenue_category_id = rc.id AND rt.code = 'renc-doy'
);

-- Type 4: Repas des natifs
INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id,
    rc.paroisse_id,
    'repas-natif',
    'Repas des natifs',
    NULL,
    1,
    4,
    NOW(),
    NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (
    SELECT 1 FROM revenue_types rt 
    WHERE rt.revenue_category_id = rc.id AND rt.code = 'repas-natif'
);

-- ================================================================
-- ÉTAPE 6 : MISE À JOUR DES TYPES DE SUBVENTION
-- ================================================================

-- S'assurer que "Subvention Popote" existe
INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id,
    rc.paroisse_id,
    'subvention_popote',
    'Subvention Popote',
    'Subvention pour alimentation popote',
    1,
    9,
    NOW(),
    NOW()
FROM revenue_categories rc
WHERE rc.code = 'subvention'
AND NOT EXISTS (
    SELECT 1 FROM revenue_types rt 
    WHERE rt.revenue_category_id = rc.id AND rt.code = 'subvention_popote'
);

-- ================================================================
-- ÉTAPE 7 : VÉRIFICATION FINALE
-- ================================================================

-- Afficher le nombre de catégories actives par paroisse
SELECT 
    p.nom AS paroisse,
    COUNT(DISTINCT rc.id) AS nb_categories
FROM paroisses p
LEFT JOIN revenue_categories rc ON rc.paroisse_id = p.id AND rc.actif = 1
GROUP BY p.id, p.nom;

-- Afficher le nombre de types de revenus par catégorie
SELECT 
    rc.nom AS categorie,
    COUNT(rt.id) AS nb_types
FROM revenue_categories rc
LEFT JOIN revenue_types rt ON rt.revenue_category_id = rc.id AND rt.actif = 1
WHERE rc.actif = 1
GROUP BY rc.id, rc.nom
ORDER BY rc.ordre;

-- Vérifier la structure de la table expenses
DESCRIBE expenses;

-- Afficher le statut des dépenses
SELECT 
    COUNT(*) AS total_depenses,
    SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS depenses_actives,
    SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS depenses_supprimees
FROM expenses;

-- Afficher le statut des revenus
SELECT 
    COUNT(*) AS total_revenus,
    SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS revenus_actifs,
    SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS revenus_supprimes
FROM revenues;

-- ================================================================
-- FIN DU SCRIPT
-- ================================================================

SELECT '✓ SCRIPT TERMINÉ AVEC SUCCÈS' AS statut;
