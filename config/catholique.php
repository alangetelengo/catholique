<?php

return [
    /**
     * Version applicative (sémantique MAJEUR.MINEUR.CORRECTIF).
     * Incrémenter à chaque livraison : tag Git vX.Y.Z + entrée CHANGELOG.md.
     */
    'version' => env('CATHOLIQUE_VERSION', '1.0.0'),

    /**
     * Date de la version courante (mise en production ou release).
     */
    'released_at' => env('CATHOLIQUE_RELEASED_AT', '2026-09-07'),
];
