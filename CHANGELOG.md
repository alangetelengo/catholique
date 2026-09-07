# Changelog

Toutes les évolutions notables de l’application paroisse catholique sont documentées ici.
Format inspiré de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/) et [Versionnement sémantique](https://semver.org/lang/fr/).

## [1.0.0] - 2026-09-07

### Ajouté

- Versionnement applicatif (`config/catholique.php`, footer, login, `php artisan catholique:version`)
- Rapports financiers (dépenses, recettes, popote, statistiques) avec visionneuse PDF / impression
- Création rapide de curé depuis le formulaire paroisse (sans rechargement)
- Alignement visuel du shell sur le style GED / cosud (vert)

### Corrigé

- Colonnes paroisse : `cure_id` / `diocese` en ASCII (évite les noms de colonnes corrompus)
- Prénom optionnel à la création rapide de membre / curé

### Notes

- Première version formalisée pour la livraison.
- Version affichée : footer, page de connexion, `php artisan catholique:version`.
