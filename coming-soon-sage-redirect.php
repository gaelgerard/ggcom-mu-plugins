<?php
/**
 * Plugin Name: Coming Soon Sage Redirect
 * Description: Redirige vers la page d'accueil si l'accès n'est pas autorisé via Woo.
 */

add_action('template_redirect', function () {
    // 1. On vérifie si la fonction de vérification existe
    // On s'assure aussi qu'on est sur la page boutique (is_shop)
    if (!function_exists('has_woo_share_access') || !is_shop()) {
        return; 
    }

    // 2. Si l'utilisateur n'a pas l'accès, on redirige vers l'accueil
    if (!has_woo_share_access()) {
        wp_safe_redirect(home_url('/'));
        exit;
    }
});
