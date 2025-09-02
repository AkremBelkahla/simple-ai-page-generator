<?php
/**
 * Fichier de désinstallation pour Simple AI Page Generator
 * 
 * Ce fichier est exécuté lorsque le plugin est désinstallé via l'interface d'administration WordPress.
 */

// Sécurité : empêcher l'accès direct
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Vérifier si la classe principale existe
if (file_exists(dirname(__FILE__) . '/simple-ai-page-generator.php')) {
    require_once dirname(__FILE__) . '/simple-ai-page-generator.php';
    
    // Appeler la méthode de désinstallation statique
    if (class_exists('Simple_AI_Page_Generator')) {
        Simple_AI_Page_Generator::uninstall();
    }
} else {
    // Nettoyage manuel si le fichier principal n'est pas disponible
    
    // Supprimer les options
    delete_option('sapg_options');
    
    // Supprimer les transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%sapg_api_cache_%'");
    
    // Supprimer les métadonnées des posts générés
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_sapg_generated'");
}
