<?php
/**
 * Plugin Name: Simple AI Page Generator
 * Plugin URI: https://infinityweb.tn/plugins/simple-ai-page-generator
 * Description: An advanced plugin to generate content using various AI APIs (OpenAI, DeepSeek, Gemini, Claude)
 * Version: 2.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Akrem Belkahla
 * Author URI: https://infinityweb.tn
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-content-gen
 * Domain Path: /languages
 *
 * @package Simple_AI_Page_Generator
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// Define plugin constants
if (!defined('SAPG_VERSION')) {
    define('SAPG_VERSION', '2.0.0');
}
if (!defined('SAPG_PLUGIN_FILE')) {
    define('SAPG_PLUGIN_FILE', __FILE__);
}
if (!defined('SAPG_PLUGIN_DIR')) {
    define('SAPG_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('SAPG_PLUGIN_URL')) {
    define('SAPG_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('SAPG_PLUGIN_BASENAME')) {
    define('SAPG_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

/**
 * Autoloader
 */
require_once SAPG_PLUGIN_DIR . 'includes/class-autoloader.php';

$autoloader = new Autoloader(SAPG_PLUGIN_DIR . 'includes');
$autoloader->register();

/**
 * Load configuration
 */
require_once SAPG_PLUGIN_DIR . 'includes/class-config.php';

/**
 * Initialize the plugin
 *
 * @return Plugin Plugin instance.
 */
function sapg_init() {
    return Plugin::get_instance();
}

/**
 * Start the plugin
 */
add_action('plugins_loaded', __NAMESPACE__ . '\sapg_init', 10);

/**
 * Uninstall hook
 */
register_uninstall_hook(__FILE__, __NAMESPACE__ . '\sapg_uninstall');

/**
 * Uninstall function
 *
 * @return void
 */
function sapg_uninstall() {
    // Remove all plugin options
    delete_option(Config::OPTION_NAME);
    
    // Remove transients
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like('_transient_sapg_') . '%',
            $wpdb->esc_like('_transient_timeout_sapg_') . '%'
        )
    );
    
    // Remove post meta
    $wpdb->query(
        "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_sapg_%'"
    );
    
    // Clear scheduled events
    wp_clear_scheduled_hook('sapg_cleanup_logs');
    
    // Remove log directory
    $upload_dir = wp_upload_dir();
    $log_dir = trailingslashit($upload_dir['basedir']) . 'sapg-logs';
    
    if (is_dir($log_dir)) {
        $files = glob($log_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                wp_delete_file($file);
            }
        }
        rmdir($log_dir);
    }
}
