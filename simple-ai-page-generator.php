<?php
/**
 * Plugin Name: Simple AI Page Generator
 * Description: An advanced plugin to generate content using various AI APIs (OpenAI, DeepSeek, Gemini)
 * Version: 1.1.0
 * Author: Akrem Belkahla
 * Author URI: https://infinityweb.tn
 * Text Domain: ai-content-gen
 */

// Security: prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SAPG_VERSION', '1.1.0');
define('SAPG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SAPG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SAPG_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Simple_AI_Page_Generator {
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Plugin options
     */
    private $options;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Load options
        $this->options = get_option('sapg_options', array());
        
        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load dependencies
        $this->load_dependencies();
        
        // Load translations
        load_plugin_textdomain('ai-content-gen', false, dirname(SAPG_PLUGIN_BASENAME) . '/languages/');
        
        // Register hooks
        $this->register_hooks();
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core files
        require_once SAPG_PLUGIN_DIR . 'includes/helpers/helpers.php';
        require_once SAPG_PLUGIN_DIR . 'includes/security/security.php';
        
        // Admin and API
        require_once SAPG_PLUGIN_DIR . 'includes/admin/admin-pages.php';
        require_once SAPG_PLUGIN_DIR . 'includes/api/api-settings.php';
        
        // AI integrations
        require_once SAPG_PLUGIN_DIR . 'includes/generation/content-generation.php';
        require_once SAPG_PLUGIN_DIR . 'includes/ai/claude-integration.php';
    }
    
    /**
     * Register plugin hooks
     */
    private function register_hooks() {
        // Admin hooks
        add_action('admin_menu', 'sapg_add_admin_page');
        add_action('admin_init', 'sapg_settings_init');
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX hooks
        add_action('wp_ajax_sapg_test_api_key', 'sapg_test_api_key');
        
        // Add settings link to plugins page
        add_filter('plugin_action_links_' . SAPG_PLUGIN_BASENAME, array($this, 'add_settings_link'));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'sapg') !== false) {
            // CSS
            wp_enqueue_style(
                'sapg-admin-style',
                SAPG_PLUGIN_URL . 'assets/css/admin-style.css',
                array(),
                SAPG_VERSION
            );
            
            // JavaScript
            wp_enqueue_script(
                'sapg-admin-script',
                SAPG_PLUGIN_URL . 'assets/js/api-settings.js',
                array('jquery'),
                SAPG_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script('sapg-admin-script', 'sapg_nonce', wp_create_nonce('sapg_test_api'));
        }
    }
    
    /**
     * Add settings link to plugins page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=sapg-content-generator">' . __('Settings', 'ai-content-gen') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Default options
        $default_options = array(
            'ai_model' => 'openai',
            'content_type' => 'post',
            'word_count' => 500,
            'openai_key' => '',
            'deepseek_key' => '',
            'gemini_key' => '',
            'huggingface_key' => '',
            'deepai_key' => '',
            'anthropic_key' => ''
        );
        
        // Add options if they don't exist
        if (!get_option('sapg_options')) {
            add_option('sapg_options', $default_options);
        }
        
        // Create necessary database tables if needed
        // $this->create_tables();
        
        // Clear cache
        wp_cache_flush();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%sapg_api_cache_%'");
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin uninstall
     * Called by uninstall.php
     */
    public static function uninstall() {
        // Remove all plugin options
        delete_option('sapg_options');
        
        // Remove any custom tables
        // self::remove_tables();
        
        // Remove transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%sapg_api_cache_%'");
    }
}

// Initialize the plugin
function sapg_init_plugin() {
    return Simple_AI_Page_Generator::get_instance();
}

// Start the plugin
$sapg_plugin = sapg_init_plugin();

/**
 * Initialize settings
 */
function sapg_settings_init() {
    // Register setting
    register_setting('sapg_options_group', 'sapg_options', 'sapg_options_validate');
    
    // Main section
    add_settings_section(
        'sapg_main_section',
        __('Generation Settings', 'ai-content-gen'),
        'sapg_section_text',
        'sapg-content-generator'
    );

    // Fields
    add_settings_field(
        'sapg_ai_model',
        __('AI Model', 'ai-content-gen'),
        'sapg_ai_model_input',
        'sapg-content-generator',
        'sapg_main_section'
    );

    add_settings_field(
        'sapg_content_type', 
        __('Content Type', 'ai-content-gen'),
        'sapg_content_type_input',
        'sapg-content-generator',
        'sapg_main_section'
    );

    add_settings_field(
        'sapg_word_count',
        __('Word Count', 'ai-content-gen'),
        'sapg_word_count_input',
        'sapg-content-generator',
        'sapg_main_section'
    );
}

/**
 * Validate options
 */
function sapg_options_validate($input) {
    $newinput = array();
    
    // Validate fields
    $newinput['ai_model'] = sanitize_text_field($input['ai_model'] ?? 'openai');
    $newinput['content_type'] = sanitize_text_field($input['content_type'] ?? 'post');
    
    // Validate word count
    $allowed_values = [100, 300, 500, 1000];
    $newinput['word_count'] = in_array($input['word_count'] ?? 500, $allowed_values) 
        ? $input['word_count'] 
        : 500; // Default value
    
    // Apply filters for additional validation
    return apply_filters('sapg_options_validate', $newinput, $input);
}

/**
 * Section text
 */
function sapg_section_text() {
    echo '<p>' . __('Configure the parameters to generate your content', 'ai-content-gen') . '</p>';
}

/**
 * AI model field
 */
function sapg_ai_model_input() {
    $options = get_option('sapg_options');
    $models = [
        'deepseek' => !empty($options['deepseek_key']),
        'openai' => !empty($options['openai_key']),
        'gemini' => !empty($options['gemini_key']),
        'anthropic' => !empty($options['anthropic_key']),
        'huggingface' => !empty($options['huggingface_key']),
        'deepai' => !empty($options['deepai_key'])
    ];
    
    echo "<select name='sapg_options[ai_model]' id='sapg_ai_model' class='regular-text'>";
    foreach ($models as $model => $enabled) {
        $selected = ($options['ai_model'] ?? '') === $model ? 'selected' : '';
        $disabled = !$enabled ? 'disabled' : '';
        echo "<option value='$model' $selected $disabled>" . ucfirst($model) . "</option>";
    }
    echo "</select>";
    
    if (!array_filter($models)) {
        echo '<p class="description">' . __('No API configured. Please configure at least one API in the settings.', 'ai-content-gen') . '</p>';
    }
}

/**
 * Content type field
 */
function sapg_content_type_input() {
    $options = get_option('sapg_options');
    $selected = $options['content_type'] ?? 'post';
    
    echo "<select name='sapg_options[content_type]' id='sapg_content_type' class='regular-text'>";
    echo "<option value='post' " . selected($selected, 'post', false) . ">" . __('Post', 'ai-content-gen') . "</option>";
    echo "<option value='page' " . selected($selected, 'page', false) . ">" . __('Page', 'ai-content-gen') . "</option>";
    echo "</select>";
}

/**
 * Word count field
 */
function sapg_word_count_input() {
    $options = get_option('sapg_options');
    $selected = $options['word_count'] ?? 500;
    
    echo "<select name='sapg_options[word_count]' id='sapg_word_count' class='regular-text'>";
    foreach ([100, 300, 500, 1000] as $value) {
        $selected_attr = selected($selected, $value, false);
        echo "<option value='$value' $selected_attr>$value " . __('words', 'ai-content-gen') . "</option>";
    }
    echo "</select>";
}
