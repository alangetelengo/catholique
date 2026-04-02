<?php

namespace App\Helpers;

use App\Models\Configuration;
use Illuminate\Support\Facades\Cache;

/**
 * Helper pour la gestion de la configuration de la paroisse
 * Utilise la charte de couleurs officielle de l'Église catholique par défaut
 */
class ParoisseConfig
{
    /**
     * Récupère une configuration pour une paroisse donnée
     *
     * Les valeurs par défaut utilisent la charte de couleurs officielle de l'Église catholique :
     * - Primaire (#003366) : Bleu marine - Couleur de la Vierge Marie
     * - Secondaire (#FFD700) : Or - Couleur papale et symbolique
     * - Succès (#2D5016) : Vert foncé - Espérance et croissance
     * - Info (#4A90E2) : Bleu clair - Confiance et sérénité
     * - Avertissement (#FF8C00) : Orange - Vigilance
     * - Danger (#DC143C) : Rouge - Sang du Christ, sacrifice
     */
    public static function get(?int $paroisseId, string $cle, mixed $default = null): mixed
    {
        // Charte de couleurs officielle de l'Église catholique par défaut
        $defaults = [
            'titre_paroisse' => 'Paroisse',
            'nom_paroisse' => 'SAINT-ESPRIT DE MOUNGALI',
            'logo_path' => '/images/logo-paroisse.svg',
            'preloader_logo_path' => null, // Si null, utilise logo_path (logo paramétrable pour le preloader)
            // Couleurs officielles de l'Église catholique
            'couleur_primaire' => '#003366',        // Bleu marine - Vierge Marie
            'couleur_secondaire' => '#FFD700',     // Or - Papal
            'couleur_succes' => '#2D5016',         // Vert foncé - Espérance
            'couleur_info' => '#4A90E2',          // Bleu clair - Confiance
            'couleur_avertissement' => '#FF8C00',  // Orange - Vigilance
            'couleur_danger' => '#DC143C',         // Rouge - Sang du Christ
            // Couleurs des boutons d'ajout
            'couleur_bouton_ajout' => '#FFEA00',   // Jaune vif pour les boutons d'ajout
            'couleur_bouton_ajout_hover' => '#FFD200', // Jaune plus foncé au survol
            // Couleur des titres de pages (card-header)
            'couleur_titre_page' => '#003366',     // Utilise la couleur primaire par défaut
            // Couleurs des actions du tableau
            'couleur_action_voir' => '#4A90E2',    // Bleu clair (info) pour voir
            'couleur_action_modifier' => '#FF8C00', // Orange (warning) pour modifier
            'couleur_action_supprimer' => '#DC143C', // Rouge (danger) pour supprimer
            // Téléphone / pays (paramétrable)
            'phone_country' => 'CG', // Congo (par défaut)
            'phone_dial_code' => '+242',
            // Format Congo: 9 chiffres, avec préfixe optionnel (+242 / 242 / 0), espaces/tirets tolérés
            'phone_regex' => '/^(\+242|242|0)?[ \-]?[0-9]{9}$/',
            'monnaie' => 'FCFA',
            'format_date' => 'd/m/Y',
            'format_heure' => 'H:i',
            'langue' => 'fr',
            // Loader (écran de chargement)
            'loader_actif' => true,
            'loader_duree_min' => 10,
            'loader_afficher_logo' => true,
            'loader_style' => 'logo_spinner',
            'loader_position' => 'centre',
            'loader_couleur_fond' => '#003366',
            'loader_couleur_texte' => '#FFFFFF',
            // Page de connexion (paroisse_id null = global)
            'login_bg_image' => '',
        ];

        // Essayer de récupérer depuis la base de données
        try {
            $cacheKey = "config_{$paroisseId}_{$cle}";

            return Cache::remember($cacheKey, 3600, function () use ($paroisseId, $cle, $defaults, $default) {
                $value = Configuration::getValue($paroisseId, $cle);

                return $value !== null ? $value : ($defaults[$cle] ?? $default);
            });
        } catch (\Exception $e) {
            // En cas d'erreur (table non créée, etc.), retourner les valeurs par défaut
            return $defaults[$cle] ?? $default;
        }
    }

    /**
     * Formate un montant selon le standard de l'application : "35 000 FCFA"
     * Espace comme séparateur de milliers, pas de décimales, monnaie paramétrable.
     */
    public static function formatMontant(?float $montant, ?int $paroisseId = null): string
    {
        if ($montant === null) {
            return '—';
        }
        $monnaie = self::get($paroisseId, 'monnaie', 'FCFA');
        return number_format(round($montant, 0), 0, ',', ' ') . ' ' . $monnaie;
    }

    /**
     * Formate un montant pour affichage (sans la monnaie) : "35 000"
     * Utile pour les champs de saisie pré-remplis.
     */
    public static function formatMontantSaisie(?float $montant): string
    {
        if ($montant === null || $montant === 0.0) {
            return '';
        }
        return number_format(round($montant, 0), 0, ',', ' ');
    }

    /**
     * Récupère toutes les couleurs de la charte graphique
     * Utilise les couleurs officielles de l'Église catholique par défaut
     */
    public static function getCouleurs(?int $paroisseId = null): array
    {
        return [
            'primary' => self::get($paroisseId, 'couleur_primaire', '#003366'),        // Bleu marine
            'secondary' => self::get($paroisseId, 'couleur_secondaire', '#FFD700'),    // Or
            'success' => self::get($paroisseId, 'couleur_succes', '#2D5016'),         // Vert foncé
            'info' => self::get($paroisseId, 'couleur_info', '#4A90E2'),              // Bleu clair
            'warning' => self::get($paroisseId, 'couleur_avertissement', '#FF8C00'),  // Orange
            'danger' => self::get($paroisseId, 'couleur_danger', '#DC143C'),           // Rouge
        ];
    }

    /**
     * Génère les variables CSS pour les couleurs
     * Les couleurs sont basées sur la charte officielle de l'Église catholique
     */
    public static function getCssVariables(?int $paroisseId = null): string
    {
        $couleurs = self::getCouleurs($paroisseId);
        $primary = $couleurs['primary'];

        // Calculer les variations de la couleur primaire
        $primaryRgb = self::hexToRgb($primary);

        // Récupérer les couleurs personnalisées pour les boutons, titres et actions
        $couleurBoutonAjout = self::get($paroisseId, 'couleur_bouton_ajout', '#FFEA00');
        $couleurBoutonAjoutHover = self::get($paroisseId, 'couleur_bouton_ajout_hover', '#FFD200');
        $couleurTitrePage = self::get($paroisseId, 'couleur_titre_page', $primary);
        $couleurActionVoir = self::get($paroisseId, 'couleur_action_voir', $couleurs['info']);
        $couleurActionModifier = self::get($paroisseId, 'couleur_action_modifier', $couleurs['warning']);
        $couleurActionSupprimer = self::get($paroisseId, 'couleur_action_supprimer', $couleurs['danger']);

        // Calculer les variations de la couleur titre page
        $titrePageRgb = self::hexToRgb($couleurTitrePage);
        $titrePageDark = self::darken($couleurTitrePage, 30);

        return "
            --primary: {$primary};
            --secondary: {$couleurs['secondary']};
            --success: {$couleurs['success']};
            --info: {$couleurs['info']};
            --warning: {$couleurs['warning']};
            --danger: {$couleurs['danger']};
            --primary-hover: ".self::darken($primary, 10).';
            --primary-dark: '.self::darken($primary, 30).";
            --rgba-primary-1: rgba({$primaryRgb}, 0.1);
            --rgba-primary-2: rgba({$primaryRgb}, 0.2);
            --rgba-primary-3: rgba({$primaryRgb}, 0.3);
            --rgba-primary-4: rgba({$primaryRgb}, 0.4);
            --rgba-primary-5: rgba({$primaryRgb}, 0.5);
            --rgba-primary-6: rgba({$primaryRgb}, 0.6);
            --rgba-primary-7: rgba({$primaryRgb}, 0.7);
            --rgba-primary-8: rgba({$primaryRgb}, 0.8);
            --rgba-primary-9: rgba({$primaryRgb}, 0.9);
            --bouton-ajout: {$couleurBoutonAjout};
            --bouton-ajout-hover: {$couleurBoutonAjoutHover};
            --titre-page: {$couleurTitrePage};
            --titre-page-dark: {$titrePageDark};
            --rgba-titre-page: rgba({$titrePageRgb}, 0.1);
            --action-voir: {$couleurActionVoir};
            --action-modifier: {$couleurActionModifier};
            --action-supprimer: {$couleurActionSupprimer};
            --loader-bg: ".self::get($paroisseId, 'loader_couleur_fond', '#003366').";
            --loader-text: ".self::get($paroisseId, 'loader_couleur_texte', '#FFFFFF').";
        ";
    }

    /**
     * Convertit une couleur hex en RGB
     */
    private static function hexToRgb(string $hex): string
    {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "$r, $g, $b";
    }

    /**
     * Assombrit une couleur hex
     */
    private static function darken(string $hex, int $percent): string
    {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, $r - ($r * $percent / 100)));
        $g = max(0, min(255, $g - ($g * $percent / 100)));
        $b = max(0, min(255, $b - ($b * $percent / 100)));

        return '#'.str_pad(dechex($r), 2, '0', STR_PAD_LEFT).
                   str_pad(dechex($g), 2, '0', STR_PAD_LEFT).
                   str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}
