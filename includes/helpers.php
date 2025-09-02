<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Charge les dépendances du plugin
 */
function sapg_load_dependencies() {
    require_once plugin_dir_path(__FILE__) . '../includes/admin/admin-pages.php';
    require_once plugin_dir_path(__FILE__) . '../includes/api/api-settings.php';
    require_once plugin_dir_path(__FILE__) . '../includes/generation/content-generation.php';
}

/**
 * Enregistre les hooks du plugin
 */
function sapg_register_hooks() {
    add_action('admin_enqueue_scripts', 'sapg_load_admin_style');
}

/**
 * Charge le style admin
 */
function sapg_load_admin_style() {
    wp_enqueue_style(
        'sapg-admin-style',
        plugins_url('../assets/css/admin-style.css', __FILE__)
    );
}

/**
 * Valide les options du plugin
 */
function sapg_validate_options($options) {
    $validated = array();
    
    if (isset($options['ai_model'])) {
        $validated['ai_model'] = sanitize_text_field($options['ai_model']);
    }
    
    if (isset($options['content_type'])) {
        $validated['content_type'] = sanitize_text_field($options['content_type']);
    }
    
    if (isset($options['word_count'])) {
        $validated['word_count'] = absint($options['word_count']);
    }
    
    return $validated;
}

/**
 * Génère un contenu unique pour éviter les doublons
 */
function sapg_generate_unique_content($content) {
    return wp_unique_post_slug($content, 0, 'publish', 'post', 0);
}

/**
 * Log les erreurs dans un fichier de log
 */
function sapg_log_error($message) {
    if (WP_DEBUG === true) {
        error_log('[AI Content Generator] ' . $message);
    }
}
