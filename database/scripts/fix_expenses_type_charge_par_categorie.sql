-- =============================================================================
-- Cohérence expenses : type_charge selon categorie_charge
-- Aligné sur config/expenses.php → types_by_categorie
-- MySQL : exécuter après sauvegarde / sur une copie de la base.
-- =============================================================================

-- 1) Diagnostic : lignes incohérentes (à examiner avant UPDATE)
SELECT
    id,
    categorie_charge,
    type_charge,
    montant,
    date_depense
FROM expenses
WHERE
    (categorie_charge = 'charge_fixe' AND type_charge NOT IN (
        'electricite', 'eau', 'gaz', 'internet', 'salaire_ouvrier',
        'poubelles_sacs', 'fournitures_bureau', 'blanchisserie', 'conseil'
    ))
    OR (categorie_charge = 'charge_variable' AND type_charge NOT IN (
        'carburant', 'hosties', 'maintenance_materiel', 'gardiennage',
        'deplacement_ouvrier', 'plomberie', 'activites_pastorales'
    ))
    OR (categorie_charge = 'charge_exceptionnelle' AND type_charge <> 'autre')
    OR (categorie_charge = 'alimentation_popote' AND type_charge <> 'alimentation')
ORDER BY categorie_charge, id;

-- 2) Corrections (valeurs par défaut si le type ne correspond pas à la catégorie)
--    - charge_fixe   → electricite (poste fixe générique)
--    - charge_variable → carburant (premier type variable de la config)
--    - charge_exceptionnelle → autre (seul type autorisé)
--    - alimentation_popote → alimentation (obligatoire)

START TRANSACTION;

UPDATE expenses
SET type_charge = 'electricite'
WHERE categorie_charge = 'charge_fixe'
  AND type_charge NOT IN (
        'electricite', 'eau', 'gaz', 'internet', 'salaire_ouvrier',
        'poubelles_sacs', 'fournitures_bureau', 'blanchisserie', 'conseil'
    );

UPDATE expenses
SET type_charge = 'carburant'
WHERE categorie_charge = 'charge_variable'
  AND type_charge NOT IN (
        'carburant', 'hosties', 'maintenance_materiel', 'gardiennage',
        'deplacement_ouvrier', 'plomberie', 'activites_pastorales'
    );

UPDATE expenses
SET type_charge = 'autre'
WHERE categorie_charge = 'charge_exceptionnelle'
  AND type_charge <> 'autre';

UPDATE expenses
SET type_charge = 'alimentation'
WHERE categorie_charge = 'alimentation_popote'
  AND type_charge <> 'alimentation';

COMMIT;

-- 3) Vérification : doit retourner 0 ligne
SELECT COUNT(*) AS lignes_encore_incoherentes
FROM expenses
WHERE
    (categorie_charge = 'charge_fixe' AND type_charge NOT IN (
        'electricite', 'eau', 'gaz', 'internet', 'salaire_ouvrier',
        'poubelles_sacs', 'fournitures_bureau', 'blanchisserie', 'conseil'
    ))
    OR (categorie_charge = 'charge_variable' AND type_charge NOT IN (
        'carburant', 'hosties', 'maintenance_materiel', 'gardiennage',
        'deplacement_ouvrier', 'plomberie', 'activites_pastorales'
    ))
    OR (categorie_charge = 'charge_exceptionnelle' AND type_charge <> 'autre')
    OR (categorie_charge = 'alimentation_popote' AND type_charge <> 'alimentation');
