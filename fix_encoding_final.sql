-- ============================================================
-- SCRIPT DE CORRECTION DES CARACTÈRES CASSÉS (ENCODAGE)
-- À appliquer en production via phpMyAdmin
-- ============================================================

-- 1. Correction des noms de types de recettes
UPDATE revenue_types SET nom = 'Quête Cardinal' WHERE id = 20 OR code = 'quet-card';
UPDATE revenue_types SET nom = 'Deuxième Quête' WHERE id = 26 OR code = 'deux-quet';
UPDATE revenue_types SET nom = 'Quêtes Impérées' WHERE id = 32 OR code = 'quet-imp';

-- 2. Correction de la description "Intention de Messes"
UPDATE revenue_types 
SET description = 'Une intention de messe est une demande adressée à un prêtre pour qu\'il porte une prière particulière (pour un défunt, un malade, une action de grâce, ou une intention personnelle) au cours de l\'Eucharistie. C\'est un acte de foi et une tradition ancienne permettant d\'associer un événement de vie à la prière de l\'Église.'
WHERE id = 23 OR code = 'int-messes';

-- 3. Correction générale de tous les "??" restants (si importation avec mauvais encodage)
UPDATE revenue_types 
SET nom = REPLACE(nom, '??', 'ê'),
    description = REPLACE(description, '??', 'ê')
WHERE nom LIKE '%??%' OR description LIKE '%??%';

-- Vérification finale
SELECT 
    'Types avec problèmes restants' as verification,
    COUNT(*) as count 
FROM revenue_types 
WHERE nom LIKE '%??%' OR description LIKE '%??%';

-- Afficher les types corrigés
SELECT id, code, nom, LEFT(description, 100) as description_preview
FROM revenue_types 
WHERE id IN (20, 23, 26, 32)
ORDER BY id;
