<?php
/**
 * Plugin Name: Coming Soon Sage Redirect
 * Description: Redirige vers la page coming soon si l'accès n'est pas partagé via Woo.
 */

add_action('plugins_loaded', function () {
    // 1. Vérifier si la fonction ou la classe du plugin dépendant existe
    // Remplace 'ma_fonction_de_partage_existe' par une fonction réelle de ton premier plugin
    if (!function_exists('has_woo_share_access') || !is_shop()) {
        return; 
    }

    // 2. Ton code de redirection ici
    if (!has_woo_share_access() && is_shop()) {
        wp_redirect( '/' );
        exit;
    }
}, 11); // On met une priorité 11 pour être sûr que les plugins soient chargés
