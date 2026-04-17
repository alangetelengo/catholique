-- =============================================================================
-- Diagnostic + correction ciblée des types de dépenses (logique "types libres")
-- -----------------------------------------------------------------------------
-- Contexte:
-- - Hors "alimentation_popote", le type est désormais libre (tous les types sauf
--   "alimentation" sont autorisés).
-- - Pour "alimentation_popote", le type DOIT rester "alimentation".
--
-- Usage recommandé:
-- 1) Exécuter d'abord les SELECT de diagnostic.
-- 2) Définir vos filtres (paroisse, période, IDs, ancien type -> nouveau type).
-- 3) Exécuter les UPDATE dans une transaction.
-- 4) Vérifier avant COMMIT.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- A) DIAGNOSTIC GLOBAL
-- -----------------------------------------------------------------------------

-- A1. Répartition par catégorie / type (vue d'ensemble)
SELECT
    categorie_charge,
    type_charge,
    COUNT(*) AS nb_lignes,
    SUM(montant) AS total_montant
FROM expenses
GROUP BY categorie_charge, type_charge
ORDER BY categorie_charge, nb_lignes DESC, type_charge;

-- A2. Incohérences résiduelles à corriger (doit être 0 idéalement)
-- Règle actuelle: popote => type_charge = alimentation
SELECT
    id,
    paroisse_id,
    date_depense,
    categorie_charge,
    type_charge,
    montant,
    libelle,
    fournisseur
FROM expenses
WHERE categorie_charge = 'alimentation_popote'
  AND type_charge <> 'alimentation'
ORDER BY date_depense DESC, id DESC;

-- -----------------------------------------------------------------------------
-- B) CIBLAGE DES LIGNES A RECLASSER (A ADAPTER)
-- -----------------------------------------------------------------------------
-- Astuce: copiez/collez ce SELECT et ajustez les filtres pour votre besoin réel.

SELECT
    id,
    paroisse_id,
    date_depense,
    categorie_charge,
    type_charge,
    montant,
    libelle,
    fournisseur,
    notes
FROM expenses
WHERE 1 = 1
  -- AND paroisse_id = 1
  -- AND date_depense BETWEEN '2026-01-01' AND '2026-12-31'
  -- AND categorie_charge IN ('charge_fixe', 'charge_variable', 'charge_exceptionnelle')
  -- AND type_charge IN ('electricite', 'blanchisserie')
ORDER BY date_depense DESC, id DESC;

-- -----------------------------------------------------------------------------
-- C) CORRECTIONS CIBLEES (EXEMPLES)
-- -----------------------------------------------------------------------------
-- IMPORTANT:
-- - Décommentez/ajustez uniquement les blocs que vous souhaitez exécuter.
-- - Faites d'abord un backup de la base.
-- - Vérifiez toujours avec un SELECT avant COMMIT.

START TRANSACTION;

-- C1. Exemple: reclasser des lignes "electricite" de fixe -> variable
-- UPDATE expenses
-- SET categorie_charge = 'charge_variable'
-- WHERE 1 = 1
--   AND paroisse_id = 1
--   AND date_depense BETWEEN '2026-01-01' AND '2026-12-31'
--   AND categorie_charge = 'charge_fixe'
--   AND type_charge = 'electricite';

-- C2. Exemple: reclasser "blanchisserie" de fixe -> variable
-- UPDATE expenses
-- SET categorie_charge = 'charge_variable'
-- WHERE 1 = 1
--   AND paroisse_id = 1
--   AND date_depense BETWEEN '2026-01-01' AND '2026-12-31'
--   AND categorie_charge = 'charge_fixe'
--   AND type_charge = 'blanchisserie';

-- C3. Exemple inverse: passer un type vers charge_fixe
-- UPDATE expenses
-- SET categorie_charge = 'charge_fixe'
-- WHERE 1 = 1
--   AND paroisse_id = 1
--   AND date_depense BETWEEN '2026-01-01' AND '2026-12-31'
--   AND categorie_charge = 'charge_variable'
--   AND type_charge IN ('internet', 'eau');

-- C4. Correction stricte popote (recommandé: laisser actif)
UPDATE expenses
SET type_charge = 'alimentation'
WHERE categorie_charge = 'alimentation_popote'
  AND type_charge <> 'alimentation';

-- -----------------------------------------------------------------------------
-- D) VERIFICATION AVANT COMMIT
-- -----------------------------------------------------------------------------

-- D1. Contrôle popote
SELECT COUNT(*) AS popote_type_invalide
FROM expenses
WHERE categorie_charge = 'alimentation_popote'
  AND type_charge <> 'alimentation';

-- D2. Contrôle ciblé post-update (à ajuster selon vos filtres)
SELECT
    categorie_charge,
    type_charge,
    COUNT(*) AS nb_lignes,
    SUM(montant) AS total_montant
FROM expenses
WHERE 1 = 1
  -- AND paroisse_id = 1
  -- AND date_depense BETWEEN '2026-01-01' AND '2026-12-31'
GROUP BY categorie_charge, type_charge
ORDER BY categorie_charge, nb_lignes DESC, type_charge;

-- Si tout est OK:
COMMIT;
-- Sinon:
-- ROLLBACK;

