<?php
/**
 * Fonction utilitaire unique pour vérifier l'accès.
 * On utilise un paramètre $check_option pour éviter la boucle infinie dans le filtre.
 */
function has_woo_share_access($check_option = true) {
    // Si on demande la vérification de l'option (hors du filtre)
    if ($check_option && get_option('woocommerce_coming_soon') !== 'yes') {
        return true;
    }

    // 1. Les admins et les gestionnaires WC ont toujours accès
    if (current_user_can('manage_woocommerce')) {
        return true;
    }

    // 2. Préparation des variables
    $private_link_enabled = get_option('woocommerce_private_link') === 'yes';
    $wc_private_key       = get_option('woocommerce_share_key');

    // Si le partage n'est pas activé ou si la clé est vide, on refuse l'accès
    if (!$private_link_enabled || empty($wc_private_key)) {
        return false;
    }

    // 3. Récupération de la valeur (priorité au GET, puis au COOKIE)
    $provided_key = '';
    if (isset($_GET['woo-share'])) {
        $provided_key = $_GET['woo-share'];
    } elseif (isset($_COOKIE['woo-share'])) {
        $provided_key = $_COOKIE['woo-share'];
    }

    // 4. Vérification sécurisée
    if (!empty($provided_key) && hash_equals($wc_private_key, $provided_key)) {
        return true;
    }

    return false;
}
/**
 * Installation manuelle du cookie woo-share
 */
add_action('init', function() {
    // 1. On vérifie si on a le paramètre GET
    if (isset($_GET['woo-share'])) {
        $wc_private_key = get_option('woocommerce_share_key');
        $provided_key   = $_GET['woo-share'];

        if (!empty($wc_private_key) && hash_equals($wc_private_key, $provided_key)) {
            
            // On définit le cookie
            // Note: On utilise une expiration longue (1 mois)
            $expiry = time() + (DAY_IN_SECONDS * 30);
            
            // On tente le setcookie
            setcookie('woo-share', $provided_key, $expiry, COOKIEPATH, COOKIE_DOMAIN, false, true);
            
            // FORCE : On injecte aussi dans la session actuelle
            $_COOKIE['woo-share'] = $provided_key;
            
            // TRÈS IMPORTANT : On redirige pour "fixer" le cookie dans le navigateur
            // Sans redirection, le cookie n'est parfois pas lu lors de la même session
            if (!is_admin()) {
                wp_safe_redirect(remove_query_arg('woo-share'));
                exit;
            }
        }
    }
}, 1);
/**
 * 1. Filtrer l'option pour débloquer le Mini-Cart et les pages Panier
 */
add_filter('option_woocommerce_coming_soon', function($value) {
    if ($value !== 'yes') {
        return $value;
    }

    // On appelle la fonction en lui disant de NE PAS vérifier l'option elle-même
    // pour éviter la boucle infinie (Fatal Error)
    if (has_woo_share_access(false)) {
        return 'no';
    }

    return $value;
});
