-- ================================================================
-- SCRIPT SIMPLIFIÉ DE MISE À JOUR - VERSION PRODUCTION
-- Date: 2026-05-08
-- À EXÉCUTER ÉTAPE PAR ÉTAPE SI NÉCESSAIRE
-- ================================================================

-- ================================================================
-- ÉTAPE 1 : SUPPRESSION DE TOUTES LES DÉPENSES
-- ================================================================
TRUNCATE TABLE expenses;
SELECT 'Étape 1: Toutes les dépenses ont été supprimées' AS statut;

-- ================================================================
-- ÉTAPE 2 : MODIFICATION DE LA STRUCTURE DE LA TABLE EXPENSES
-- ================================================================

-- Ajouter revenue_category_id (ignorer l'erreur si la colonne existe déjà)
ALTER TABLE expenses 
ADD COLUMN revenue_category_id BIGINT UNSIGNED NULL AFTER paroisse_id;

-- Ajouter revenue_type_id (ignorer l'erreur si la colonne existe déjà)
ALTER TABLE expenses 
ADD COLUMN revenue_type_id BIGINT UNSIGNED NULL AFTER revenue_category_id;

-- Ajouter les contraintes (ignorer l'erreur si elles existent déjà)
ALTER TABLE expenses 
ADD CONSTRAINT expenses_revenue_category_id_foreign 
FOREIGN KEY (revenue_category_id) REFERENCES revenue_categories(id) ON DELETE RESTRICT;

ALTER TABLE expenses 
ADD CONSTRAINT expenses_revenue_type_id_foreign 
FOREIGN KEY (revenue_type_id) REFERENCES revenue_types(id) ON DELETE RESTRICT;

-- Supprimer les anciennes colonnes (ignorer l'erreur si elles n'existent pas)
ALTER TABLE expenses DROP COLUMN categorie_charge;
ALTER TABLE expenses DROP COLUMN type_charge;

SELECT 'Étape 2: Structure de la table expenses mise à jour' AS statut;

-- ================================================================
-- ÉTAPE 3 : NETTOYAGE DES REVENUS INCORRECTS
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

SELECT 'Étape 3: Catégories et types incorrects supprimés' AS statut;

-- ================================================================
-- ÉTAPE 4 : AJOUT DE LA CATÉGORIE "FÊTE"
-- ================================================================

-- Ajouter la catégorie Fête pour chaque paroisse
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

SELECT 'Étape 4: Catégorie Fête ajoutée' AS statut;

-- ================================================================
-- ÉTAPE 5 : AJOUT DES TYPES DE FÊTES
-- ================================================================

-- Fête patronale paroissiale
INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id, rc.paroisse_id, 'fete-paroisse', 'Fête patronale paroissiale', 
    'Anniversaire de la paroisse', 1, 1, NOW(), NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (
    SELECT 1 FROM revenue_types rt 
    WHERE rt.revenue_category_id = rc.id AND rt.code = 'fete-paroisse'
);

-- Repas du doyenné
INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id, rc.paroisse_id, 'repas-doy', 'Repas du doyenné', 
    NULL, 1, 2, NOW(), NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (
    SELECT 1 FROM revenue_types rt 
    WHERE rt.revenue_category_id = rc.id AND rt.code = 'repas-doy'
);

-- Rencontre du doyenné
INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id, rc.paroisse_id, 'renc-doy', 'Rencontre du doyenné', 
    NULL, 1, 3, NOW(), NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (
    SELECT 1 FROM revenue_types rt 
    WHERE rt.revenue_category_id = rc.id AND rt.code = 'renc-doy'
);

-- Repas des natifs
INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id, rc.paroisse_id, 'repas-natif', 'Repas des natifs', 
    NULL, 1, 4, NOW(), NOW()
FROM revenue_categories rc
WHERE rc.code = 'fete'
AND NOT EXISTS (
    SELECT 1 FROM revenue_types rt 
    WHERE rt.revenue_category_id = rc.id AND rt.code = 'repas-natif'
);

SELECT 'Étape 5: Types de fêtes ajoutés' AS statut;

-- ================================================================
-- ÉTAPE 6 : S'ASSURER QUE "SUBVENTION POPOTE" EXISTE
-- ================================================================

INSERT INTO revenue_types (revenue_category_id, paroisse_id, code, nom, description, actif, ordre, created_at, updated_at)
SELECT 
    rc.id, rc.paroisse_id, 'subvention_popote', 'Subvention Popote', 
    'Subvention pour alimentation popote', 1, 9, NOW(), NOW()
FROM revenue_categories rc
WHERE rc.code = 'subvention'
AND NOT EXISTS (
    SELECT 1 FROM revenue_types rt 
    WHERE rt.revenue_category_id = rc.id AND rt.code = 'subvention_popote'
);

SELECT 'Étape 6: Type Subvention Popote vérifié' AS statut;

-- ================================================================
-- VÉRIFICATIONS FINALES
-- ================================================================

-- Catégories par paroisse
SELECT '=== CATÉGORIES PAR PAROISSE ===' AS info;
SELECT 
    p.nom AS paroisse,
    COUNT(DISTINCT rc.id) AS nb_categories
FROM paroisses p
LEFT JOIN revenue_categories rc ON rc.paroisse_id = p.id AND rc.actif = 1
GROUP BY p.id, p.nom;

-- Types par catégorie
SELECT '=== TYPES PAR CATÉGORIE ===' AS info;
SELECT 
    rc.nom AS categorie,
    COUNT(rt.id) AS nb_types
FROM revenue_categories rc
LEFT JOIN revenue_types rt ON rt.revenue_category_id = rc.id AND rt.actif = 1
WHERE rc.actif = 1
GROUP BY rc.id, rc.nom
ORDER BY rc.ordre;

-- Statut des dépenses
SELECT '=== STATUT DES DÉPENSES ===' AS info;
SELECT COUNT(*) AS total_depenses FROM expenses;

-- Statut des revenus
SELECT '=== STATUT DES REVENUS ===' AS info;
SELECT 
    COUNT(*) AS total_revenus,
    SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS revenus_actifs
FROM revenues;

SELECT '✓ SCRIPT TERMINÉ' AS statut;
