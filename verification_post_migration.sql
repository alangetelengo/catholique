-- ================================================================
-- SCRIPT DE VÉRIFICATION POST-MIGRATION
-- À exécuter APRÈS la mise à jour pour confirmer que tout est OK
-- ================================================================

SELECT '========================================' AS ' ';
SELECT 'RAPPORT DE VÉRIFICATION POST-MIGRATION' AS ' ';
SELECT '========================================' AS ' ';
SELECT '' AS ' ';

-- ================================================================
-- 1. STRUCTURE DE LA TABLE EXPENSES
-- ================================================================
SELECT '1. STRUCTURE DE LA TABLE EXPENSES' AS ' ';
SELECT '-----------------------------------' AS ' ';

SELECT 
    COLUMN_NAME AS colonne,
    COLUMN_TYPE AS type,
    IS_NULLABLE AS nullable,
    COLUMN_KEY AS cle
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'expenses'
ORDER BY ORDINAL_POSITION;

SELECT '' AS ' ';

-- Vérifier la présence des nouvelles colonnes
SELECT 
    CASE 
        WHEN SUM(CASE WHEN COLUMN_NAME = 'revenue_category_id' THEN 1 ELSE 0 END) > 0 THEN '✓ OK'
        ELSE '✗ ERREUR'
    END AS 'Colonne revenue_category_id',
    CASE 
        WHEN SUM(CASE WHEN COLUMN_NAME = 'revenue_type_id' THEN 1 ELSE 0 END) > 0 THEN '✓ OK'
        ELSE '✗ ERREUR'
    END AS 'Colonne revenue_type_id',
    CASE 
        WHEN SUM(CASE WHEN COLUMN_NAME = 'categorie_charge' THEN 1 ELSE 0 END) = 0 THEN '✓ OK (supprimée)'
        ELSE '✗ ERREUR (existe encore)'
    END AS 'Colonne categorie_charge',
    CASE 
        WHEN SUM(CASE WHEN COLUMN_NAME = 'type_charge' THEN 1 ELSE 0 END) = 0 THEN '✓ OK (supprimée)'
        ELSE '✗ ERREUR (existe encore)'
    END AS 'Colonne type_charge'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'expenses';

SELECT '' AS ' ';

-- ================================================================
-- 2. CONTRAINTES DE CLÉS ÉTRANGÈRES
-- ================================================================
SELECT '2. CONTRAINTES DE CLÉS ÉTRANGÈRES' AS ' ';
SELECT '------------------------------------' AS ' ';

SELECT 
    CONSTRAINT_NAME AS contrainte,
    COLUMN_NAME AS colonne,
    REFERENCED_TABLE_NAME AS table_reference,
    REFERENCED_COLUMN_NAME AS colonne_reference
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'expenses'
AND REFERENCED_TABLE_NAME IS NOT NULL
AND CONSTRAINT_NAME LIKE '%revenue%';

SELECT '' AS ' ';

-- ================================================================
-- 3. CATÉGORIES DE REVENUS
-- ================================================================
SELECT '3. CATÉGORIES DE REVENUS ACTIVES' AS ' ';
SELECT '-----------------------------------' AS ' ';

SELECT 
    p.nom AS paroisse,
    GROUP_CONCAT(rc.code ORDER BY rc.ordre SEPARATOR ', ') AS categories
FROM paroisses p
LEFT JOIN revenue_categories rc ON rc.paroisse_id = p.id AND rc.actif = 1
GROUP BY p.id, p.nom;

SELECT '' AS ' ';

SELECT 
    'Total attendu: 6 catégories par paroisse' AS info,
    CASE 
        WHEN MIN(nb) = 6 AND MAX(nb) = 6 THEN '✓ OK'
        ELSE CONCAT('✗ ERREUR (', MIN(nb), ' à ', MAX(nb), ' catégories)')
    END AS statut
FROM (
    SELECT p.id, COUNT(rc.id) as nb
    FROM paroisses p
    LEFT JOIN revenue_categories rc ON rc.paroisse_id = p.id AND rc.actif = 1
    GROUP BY p.id
) AS counts;

SELECT '' AS ' ';

-- ================================================================
-- 4. CATÉGORIE "FÊTE"
-- ================================================================
SELECT '4. CATÉGORIE FÊTE ET SES TYPES' AS ' ';
SELECT '-----------------------------------' AS ' ';

SELECT 
    p.nom AS paroisse,
    CASE 
        WHEN COUNT(rc.id) > 0 THEN '✓ Existe'
        ELSE '✗ Manquante'
    END AS categorie_fete,
    COUNT(rt.id) AS nb_types_fete
FROM paroisses p
LEFT JOIN revenue_categories rc ON rc.paroisse_id = p.id AND rc.code = 'fete' AND rc.actif = 1
LEFT JOIN revenue_types rt ON rt.revenue_category_id = rc.id AND rt.actif = 1
GROUP BY p.id, p.nom;

SELECT '' AS ' ';

-- Détail des types de fêtes
SELECT 
    rt.code AS code_type,
    rt.nom AS nom_type,
    COUNT(*) AS nb_paroisses
FROM revenue_types rt
JOIN revenue_categories rc ON rt.revenue_category_id = rc.id
WHERE rc.code = 'fete'
AND rt.actif = 1
GROUP BY rt.code, rt.nom
ORDER BY rt.ordre;

SELECT '' AS ' ';

-- ================================================================
-- 5. TYPE "SUBVENTION POPOTE"
-- ================================================================
SELECT '5. TYPE SUBVENTION POPOTE' AS ' ';
SELECT '-----------------------------------' AS ' ';

SELECT 
    p.nom AS paroisse,
    CASE 
        WHEN COUNT(rt.id) > 0 THEN '✓ Existe'
        ELSE '✗ Manquant'
    END AS subvention_popote
FROM paroisses p
LEFT JOIN revenue_categories rc ON rc.paroisse_id = p.id AND rc.code = 'subvention' AND rc.actif = 1
LEFT JOIN revenue_types rt ON rt.revenue_category_id = rc.id AND rt.code = 'subvention_popote' AND rt.actif = 1
GROUP BY p.id, p.nom;

SELECT '' AS ' ';

-- ================================================================
-- 6. VÉRIFICATION DES DONNÉES SUPPRIMÉES
-- ================================================================
SELECT '6. VÉRIFICATION DES SUPPRESSIONS' AS ' ';
SELECT '--------------------------------------' AS ' ';

-- Catégorie popote_subvention ne doit plus exister
SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN '✓ OK (catégorie popote_subvention supprimée)'
        ELSE CONCAT('✗ ERREUR (', COUNT(*), ' catégorie(s) popote_subvention encore présente(s))')
    END AS 'Catégorie popote_subvention'
FROM revenue_categories
WHERE code = 'popote_subvention';

-- Types incorrects ne doivent plus exister
SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN '✓ OK (types incorrects supprimés)'
        ELSE CONCAT('✗ ERREUR (', COUNT(*), ' type(s) incorrect(s) encore présent(s))')
    END AS 'Types incorrects'
FROM revenue_types
WHERE code IN ('subvention_alimentation_popote', 'autres_subventions');

SELECT '' AS ' ';

-- ================================================================
-- 7. COMPTEURS DE DONNÉES
-- ================================================================
SELECT '7. COMPTEURS DE DONNÉES' AS ' ';
SELECT '-----------------------------------' AS ' ';

SELECT 
    'Dépenses' AS table_name,
    COUNT(*) AS total,
    CASE 
        WHEN COUNT(*) = 0 THEN '✓ OK (table vide comme attendu)'
        ELSE CONCAT('⚠ ATTENTION (', COUNT(*), ' dépense(s) présente(s))')
    END AS statut
FROM expenses

UNION ALL

SELECT 
    'Revenus actifs' AS table_name,
    COUNT(*) AS total,
    '✓ Préservés' AS statut
FROM revenues
WHERE deleted_at IS NULL

UNION ALL

SELECT 
    'Catégories de revenus' AS table_name,
    COUNT(*) AS total,
    '✓ OK' AS statut
FROM revenue_categories
WHERE actif = 1

UNION ALL

SELECT 
    'Types de revenus' AS table_name,
    COUNT(*) AS total,
    '✓ OK' AS statut
FROM revenue_types
WHERE actif = 1;

SELECT '' AS ' ';

-- ================================================================
-- 8. RÉSUMÉ FINAL
-- ================================================================
SELECT '8. RÉSUMÉ FINAL' AS ' ';
SELECT '-----------------------------------' AS ' ';

SELECT 
    CASE 
        WHEN (
            -- Nouvelles colonnes existent
            (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' 
             AND COLUMN_NAME IN ('revenue_category_id', 'revenue_type_id')) = 2
            -- Anciennes colonnes n'existent plus
            AND (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' 
                 AND COLUMN_NAME IN ('categorie_charge', 'type_charge')) = 0
            -- Catégorie Fête existe pour toutes les paroisses
            AND (SELECT COUNT(DISTINCT p.id) FROM paroisses p) = 
                (SELECT COUNT(DISTINCT rc.paroisse_id) FROM revenue_categories rc WHERE rc.code = 'fete' AND rc.actif = 1)
            -- Dépenses sont vides
            AND (SELECT COUNT(*) FROM expenses) = 0
        ) THEN '✓✓✓ MIGRATION RÉUSSIE ✓✓✓'
        ELSE '✗✗✗ MIGRATION INCOMPLÈTE - Vérifier les détails ci-dessus ✗✗✗'
    END AS 'STATUT GLOBAL';

SELECT '' AS ' ';
SELECT '========================================' AS ' ';
SELECT 'FIN DU RAPPORT DE VÉRIFICATION' AS ' ';
SELECT '========================================' AS ' ';
