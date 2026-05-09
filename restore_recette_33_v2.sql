-- ========================================
-- RESTAURATION RECETTE ID 33 - VERSION CORRIGÉE
-- Montant: 700,000 FCFA - Popote Mensuelle du 10 mars 2026
-- ========================================

-- ANCIEN DUMP (production):
--   revenue_category_id = 4 (popote_subvention - supprimée depuis)
--   revenue_type_id = 13 (popote_mensuelle)

-- NOUVEAUX IDS (base locale):
--   revenue_category_id = 10 (subvention)
--   revenue_type_id = 57 (subvention_popote)

-- Vérifier d'abord si l'ID 33 n'existe pas déjà
SELECT 
    CASE 
        WHEN COUNT(*) = 0 THEN 'OK: ID 33 absent, restauration possible'
        ELSE 'ATTENTION: ID 33 existe déjà!'
    END as verification
FROM revenues 
WHERE id = 33;

-- Restaurer la recette avec les IDs corrects
INSERT INTO revenues (
    id, 
    paroisse_id, 
    revenue_category_id, 
    revenue_type_id, 
    periode_messe, 
    jour_semaine, 
    mois_location, 
    event_id, 
    montant, 
    date_recette, 
    est_recurrent, 
    frequence_recurrence, 
    methode_paiement, 
    reference_paiement, 
    statut, 
    notes, 
    donateur_nom, 
    donateur_telephone, 
    created_by, 
    validated_by, 
    validated_at, 
    created_at, 
    updated_at, 
    deleted_at
) 
VALUES (
    33,                                     -- id
    1,                                      -- paroisse_id (SAINT-ESPRIT DE MOUNGALI)
    10,                                     -- revenue_category_id (Subvention) - CORRIGÉ de 4 à 10
    57,                                     -- revenue_type_id (Subvention Popote) - CORRIGÉ de 13 à 57
    'semaine',                              -- periode_messe
    'mardi',                                -- jour_semaine
    NULL,                                   -- mois_location
    NULL,                                   -- event_id
    700000.00,                              -- montant (700,000 FCFA) - Popote Mensuelle
    '2026-03-10',                           -- date_recette
    0,                                      -- est_recurrent
    NULL,                                   -- frequence_recurrence
    'especes',                              -- methode_paiement
    'REV-20260328003059-JNH1',             -- reference_paiement
    'valide',                               -- statut
    'Popote Mensuelle - restauré depuis dump production',  -- notes
    NULL,                                   -- donateur_nom
    NULL,                                   -- donateur_telephone
    2,                                      -- created_by
    NULL,                                   -- validated_by
    NULL,                                   -- validated_at
    '2026-03-27 23:30:59',                 -- created_at
    '2026-03-27 23:34:51',                 -- updated_at
    NULL                                    -- deleted_at
);

-- Vérifier que la restauration a réussi
SELECT 
    r.id,
    r.montant,
    r.date_recette,
    rc.nom as categorie,
    rt.nom as type,
    r.reference_paiement,
    'Recette restaurée avec succès!' as statut
FROM revenues r
JOIN revenue_categories rc ON r.revenue_category_id = rc.id
JOIN revenue_types rt ON r.revenue_type_id = rt.id
WHERE r.id = 33;
