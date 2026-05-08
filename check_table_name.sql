-- Script pour vérifier le nom exact de la table des paroisses en production
-- Exécutez ceci dans phpMyAdmin

-- 1. Lister toutes les tables de la base de données
SHOW TABLES;

-- 2. Chercher les tables contenant "paroisse"
SHOW TABLES LIKE '%paroisse%';

-- 3. Si aucune table trouvée, lister toutes les tables pour voir ce qui existe
SELECT TABLE_NAME 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME;
