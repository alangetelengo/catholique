-- ================================================================
-- SCRIPT DE NETTOYAGE COMPLET - PRODUCTION
-- Date: 2026-05-08
-- Description: 
--   - Supprime tous les rapports financiers (table financial_reports)
--   - Supprime toutes les dépenses
--   - Met à jour la structure de la table expenses
--   - Préserve TOUS les revenus
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ================================================================
-- ÉTAPE 1 : SUPPRESSION DES RAPPORTS FINANCIERS
-- ================================================================
TRUNCATE TABLE financial_reports;
SELECT '✓ Étape 1: Tous les rapports financiers supprimés' AS statut;

-- ================================================================
-- ÉTAPE 2 : SUPPRESSION DE TOUTES LES DÉPENSES
-- ================================================================
TRUNCATE TABLE expenses;
SELECT '✓ Étape 2: Toutes les dépenses supprimées' AS statut;

-- ================================================================
-- ÉTAPE 3 : MODIFICATION DE LA STRUCTURE DE LA TABLE EXPENSES
-- ================================================================

-- Ajouter revenue_category_id
ALTER TABLE expenses 
ADD COLUMN revenue_category_id BIGINT UNSIGNED NULL AFTER paroisse_id;

-- Ajouter revenue_type_id
ALTER TABLE expenses 
ADD COLUMN revenue_type_id BIGINT UNSIGNED NULL AFTER revenue_category_id;

-- Ajouter les contraintes
ALTER TABLE expenses 
ADD CONSTRAINT expenses_revenue_category_id_foreign 
FOREIGN KEY (revenue_category_id) REFERENCES revenue_categories(id) ON DELETE RESTRICT;

ALTER TABLE expenses 
ADD CONSTRAINT expenses_revenue_type_id_foreign 
FOREIGN KEY (revenue_type_id) REFERENCES revenue_types(id) ON DELETE RESTRICT;

-- Supprimer les anciennes colonnes
ALTER TABLE expenses DROP COLUMN categorie_charge;
ALTER TABLE expenses DROP COLUMN type_charge;

SELECT '✓ Étape 3: Structure de la table expenses mise à jour' AS statut;

-- ================================================================
-- ÉTAPE 4 : NETTOYAGE DES CATÉGORIES DE REVENUS
-- ================================================================

-- Supprimer les revenus liés à "popote_subvention"
DELETE FROM revenues 
WHERE revenue_category_id IN (
    SELECT id FROM revenue_categories WHERE code = 'popote_subvention'
);

-- Supprimer les types de "popote_subvention"
DELETE FROM revenue_types 
WHERE revenue_category_id IN (
    SELECT id FROM revenue_categories WHERE code = 'popote_subvention'
);

-- Supprimer la catégorie "popote_subvention"
DELETE FROM revenue_categories WHERE code = 'popote_subvention';

-- Supprimer les types incorrects
DELETE FROM revenue_types WHERE code IN ('subvention_alimentation_popote', 'autres_subventions');

SELECT '✓ Étape 4: Catégories et types incorrects supprimés' AS statut;

-- ================================================================
-- ÉTAPE 5 : AJOUT DE LA CATÉGORIE "FÊTE"
-- ================================================================

INSERT INTO revenue_categories (paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    p.id, 'fete', 'Fête', 'Fêtes de la paroisse', 1, 6, NOW(), NOW()
FROM paroisses p
WHERE NOT EXISTS (
    SELECT 1 FROM revenue_categories rc 
    WHERE rc.paroisse_id = p.id AND rc.code = 'fete'
);

-- Types de fêtes
INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id, rc.paroisse_id, 'fete-paroisse', 'Fête patronale paroissiale', 
    'Anniversaire de la paroisse', 1, 1, NOW(), NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (SELECT 1 FROM revenue_types rt WHERE rt.revenue_category_id = rc.id AND rt.code = 'fete-paroisse');

INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id, rc.paroisse_id, 'repas-doy', 'Repas du doyenné', NULL, 1, 2, NOW(), NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (SELECT 1 FROM revenue_types rt WHERE rt.revenue_category_id = rc.id AND rt.code = 'repas-doy');

INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id, rc.paroisse_id, 'renc-doy', 'Rencontre du doyenné', NULL, 1, 3, NOW(), NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (SELECT 1 FROM revenue_types rt WHERE rt.revenue_category_id = rc.id AND rt.code = 'renc-doy');

INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id, rc.paroisse_id, 'repas-natif', 'Repas des natifs', NULL, 1, 4, NOW(), NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (SELECT 1 FROM revenue_types rt WHERE rt.revenue_category_id = rc.id AND rt.code = 'repas-natif');

SELECT '✓ Étape 5: Catégorie Fête et ses types ajoutés' AS statut;

-- ================================================================
-- ÉTAPE 6 : SUBVENTION POPOTE
-- ================================================================

INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id, rc.paroisse_id, 'subvention_popote', 'Subvention Popote', 
    'Subvention pour alimentation popote', 1, 9, NOW(), NOW()
FROM revenue_categories rc
WHERE rc.code = 'subvention'
AND NOT EXISTS (SELECT 1 FROM revenue_types rt WHERE rt.revenue_category_id = rc.id AND rt.code = 'subvention_popote');

SELECT '✓ Étape 6: Type Subvention Popote vérifié' AS statut;

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- VÉRIFICATIONS FINALES
-- ================================================================

SELECT '========================================' AS ' ';
SELECT 'VÉRIFICATIONS FINALES' AS ' ';
SELECT '========================================' AS ' ';

-- Nombre de rapports financiers
SELECT 'Rapports financiers' AS item, COUNT(*) AS total FROM financial_reports;

-- Nombre de dépenses
SELECT 'Dépenses' AS item, COUNT(*) AS total FROM expenses;

-- Nombre de revenus (doivent être préservés)
SELECT 'Revenus actifs' AS item, COUNT(*) AS total FROM revenues WHERE deleted_at IS NULL;

-- Catégories de revenus
SELECT 'Catégories de revenus' AS item, COUNT(*) AS total FROM revenue_categories WHERE actif = 1;

-- Types de revenus
SELECT 'Types de revenus' AS item, COUNT(*) AS total FROM revenue_types WHERE actif = 1;

SELECT '========================================' AS ' ';
SELECT '✓✓✓ NETTOYAGE TERMINÉ ✓✓✓' AS ' ';
SELECT '========================================' AS ' ';
