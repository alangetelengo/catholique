-- ========================================
-- VÉRIFICATION DES RECETTES 
-- Comparaison entre dump production et BD locale
-- ========================================

-- 1. Compte total des recettes (incluant supprimées)
SELECT 
    'Total recettes (avec deleted)' as verification,
    COUNT(*) as count 
FROM revenues;

-- 2. Compte des recettes actives (non supprimées)
SELECT 
    'Total recettes actives' as verification,
    COUNT(*) as count 
FROM revenues 
WHERE deleted_at IS NULL;

-- 3. Répartition par paroisse
SELECT 
    p.nom as paroisse,
    COUNT(*) as total_recettes,
    COUNT(CASE WHEN r.deleted_at IS NULL THEN 1 END) as actives,
    COUNT(CASE WHEN r.deleted_at IS NOT NULL THEN 1 END) as supprimees
FROM revenues r
JOIN paroisses p ON r.paroisse_id = p.id
GROUP BY p.id, p.nom
ORDER BY p.nom;

-- 4. Répartition par catégorie
SELECT 
    rc.nom as categorie,
    COUNT(*) as total,
    SUM(r.montant) as montant_total
FROM revenues r
JOIN revenue_categories rc ON r.revenue_category_id = rc.id
WHERE r.deleted_at IS NULL
GROUP BY rc.id, rc.nom
ORDER BY rc.nom;

-- 5. Dates min/max des recettes
SELECT 
    MIN(date_recette) as premiere_recette,
    MAX(date_recette) as derniere_recette,
    DATEDIFF(MAX(date_recette), MIN(date_recette)) as jours_couverts
FROM revenues
WHERE deleted_at IS NULL;

-- 6. IDs min/max
SELECT 
    MIN(id) as min_id,
    MAX(id) as max_id,
    MAX(id) - MIN(id) + 1 as plage_ids,
    COUNT(*) as total_records,
    (MAX(id) - MIN(id) + 1) - COUNT(*) as ids_manquants
FROM revenues;

-- 7. Dernières recettes enregistrées (par date de création)
SELECT 
    id,
    date_recette,
    montant,
    created_at,
    deleted_at
FROM revenues
ORDER BY created_at DESC
LIMIT 10;
