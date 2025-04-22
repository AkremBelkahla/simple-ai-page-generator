<?php
/**
 * Helpers functions for Simple AI Page Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

function sapg_load_dependencies() {
    require_once plugin_dir_path(__FILE__) . '../admin/admin-pages.php';
    require_once plugin_dir_path(__FILE__) . '../api/api-settings.php';
    require_once plugin_dir_path(__FILE__) . '../generation/content-generation.php';
}

function sapg_register_hooks() {
    // Register hooks here
}

function sapg_load_admin_style() {
    wp_enqueue_style(
        'sapg-admin-style',
        plugins_url('../assets/css/admin-style.css', __FILE__)
    );
}
