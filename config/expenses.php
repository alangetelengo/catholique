<?php

/**
 * Liste des codes `type_charge` (ENUM colonne expenses.type_charge).
 * À tenir synchronisée avec les migrations MySQL.
 *
 * @var list<string>
 */
return [
    'type_charge_codes' => [
        'carburant',
        'hosties',
        'internet',
        'maintenance_materiel',
        'gaz',
        'eau',
        'electricite',
        'gardiennage',
        'salaire_ouvrier',
        'autre',
        'alimentation',
        'poubelles_sacs',
        'fournitures_bureau',
        'deplacement_ouvrier',
        'plomberie',
        'conseil',
        'blanchisserie',
        'activites_pastorales',
    ],

    /**
     * Types autorisés par catégorie de charge (hors incohérences en base).
     * Chaque code doit exister dans `type_charge_codes` ; chaque code n’apparaît qu’une fois au total.
     *
     * @var array<string, list<string>>
     */
    'types_by_categorie' => [
        'charge_fixe' => [
            'electricite',
            'eau',
            'gaz',
            'internet',
            'salaire_ouvrier',
            'poubelles_sacs',
            'fournitures_bureau',
            'blanchisserie',
            'conseil',
        ],
        'charge_variable' => [
            'carburant',
            'hosties',
            'maintenance_materiel',
            'gardiennage',
            'deplacement_ouvrier',
            'plomberie',
            'activites_pastorales',
        ],
        'charge_exceptionnelle' => [
            'autre',
        ],
        'alimentation_popote' => [
            'alimentation',
        ],
    ],
];
