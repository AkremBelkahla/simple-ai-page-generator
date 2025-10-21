<?php
/**
 * Main Plugin class for Simple AI Page Generator
 *
 * Core plugin functionality and initialization.
 *
 * @package Simple_AI_Page_Generator
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator;

use Simple_AI_Page_Generator\Core\Logger;
use Simple_AI_Page_Generator\Core\Validator;
use Simple_AI_Page_Generator\Admin\Admin_Controller;
use Simple_AI_Page_Generator\Generator\Content_Generator;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Plugin
 *
 * Main plugin class that orchestrates all functionality.
 *
 * @since 2.0.0
 */
class Plugin {
    
    /**
     * Plugin instance (Singleton)
     *
     * @var Plugin
     */
    private static $instance = null;
    
    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;
    
    /**
     * Validator instance
     *
     * @var Validator
     */
    private $validator;
    
    /**
     * Admin controller instance
     *
     * @var Admin_Controller
     */
    private $admin_controller;
    
    /**
     * Content generator instance
     *
     * @var Content_Generator
     */
    private $content_generator;
    
    /**
     * Plugin options
     *
     * @var array
     */
    private $options;
    
    /**
     * Get plugin instance (Singleton pattern)
     *
     * @return Plugin Plugin instance.
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor (private for Singleton)
     */
    private function __construct() {
        // Load options
        $this->options = get_option(Config::OPTION_NAME, Config::get_default_options());
        
        // Initialize core components
        $this->init_core_components();
        
        // Register hooks
        $this->register_hooks();
    }
    
    /**
     * Initialize core components
     *
     * @return void
     */
    private function init_core_components() {
        // Initialize logger
        $this->logger = new Logger();
        
        // Initialize validator
        $this->validator = new Validator($this->logger);
        
        // Log plugin initialization
        $this->logger->info('Plugin initializing', array(
            'version' => Config::VERSION,
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
        ));
    }
    
    /**
     * Register WordPress hooks
     *
     * @return void
     */
    private function register_hooks() {
        // Initialization hooks
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_init', array($this, 'init_admin'));
            add_action('admin_menu', array($this, 'register_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        }
        
        // AJAX hooks
        add_action('wp_ajax_sapg_test_api_key', array($this, 'ajax_test_api_key'));
        add_action('wp_ajax_sapg_generate_content', array($this, 'ajax_generate_content'));
        
        // Plugin action links
        add_filter('plugin_action_links_' . SAPG_PLUGIN_BASENAME, array($this, 'add_action_links'));
        
        // Activation/Deactivation hooks
        register_activation_hook(SAPG_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(SAPG_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Scheduled events
        add_action('sapg_cleanup_logs', array($this, 'cleanup_old_logs'));
    }
    
    /**
     * Load plugin text domain for translations
     *
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            Config::TEXT_DOMAIN,
            false,
            dirname(SAPG_PLUGIN_BASENAME) . '/languages/'
        );
        
        $this->logger->debug('Text domain loaded');
    }
    
    /**
     * Initialize plugin
     *
     * @return void
     */
    public function init() {
        // Check system requirements
        if (!$this->check_requirements()) {
            return;
        }
        
        // Initialize content generator
        $this->content_generator = new Content_Generator($this->logger, $this->validator);
        
        // Fire action for extensions
        do_action('sapg_init', $this);
        
        $this->logger->debug('Plugin initialized');
    }
    
    /**
     * Initialize admin components
     *
     * @return void
     */
    public function init_admin() {
        // Initialize admin controller
        $this->admin_controller = new Admin_Controller($this->logger, $this->validator);
        
        // Register settings
        $this->register_settings();
        
        $this->logger->debug('Admin components initialized');
    }
    
    /**
     * Register admin menu
     *
     * @return void
     */
    public function register_admin_menu() {
        if ($this->admin_controller) {
            $this->admin_controller->register_menu();
        }
    }
    
    /**
     * Register plugin settings
     *
     * @return void
     */
    private function register_settings() {
        register_setting(
            'sapg_options_group',
            Config::OPTION_NAME,
            array(
                'sanitize_callback' => array($this, 'sanitize_options'),
                'default' => Config::get_default_options(),
            )
        );
    }
    
    /**
     * Sanitize plugin options
     *
     * @param array $input Raw input options.
     * @return array Sanitized options.
     */
    public function sanitize_options($input) {
        $sanitized = array();
        
        // Sanitize AI model
        if (isset($input['ai_model'])) {
            $model = $this->validator->validate_ai_model($input['ai_model']);
            $sanitized['ai_model'] = $model !== false ? $model : Config::get_default_options()['ai_model'];
        }
        
        // Sanitize content type
        if (isset($input['content_type'])) {
            $type = $this->validator->validate_content_type($input['content_type']);
            $sanitized['content_type'] = $type !== false ? $type : Config::get_default_options()['content_type'];
        }
        
        // Sanitize word count
        if (isset($input['word_count'])) {
            $count = $this->validator->validate_word_count($input['word_count']);
            $sanitized['word_count'] = $count !== false ? $count : Config::get_default_options()['word_count'];
        }
        
        // Sanitize post status
        if (isset($input['post_status'])) {
            $status = $this->validator->validate_post_status($input['post_status']);
            $sanitized['post_status'] = $status !== false ? $status : Config::get_default_options()['post_status'];
        }
        
        // Sanitize API keys
        $api_keys = array('openai_key', 'deepseek_key', 'gemini_key', 'anthropic_key');
        foreach ($api_keys as $key) {
            if (isset($input[$key])) {
                $api_key = $this->validator->validate_api_key($input[$key]);
                $sanitized[$key] = $api_key !== false ? $api_key : '';
            }
        }
        
        // Sanitize boolean options
        $sanitized['enable_logging'] = isset($input['enable_logging']) ? (bool) $input['enable_logging'] : true;
        $sanitized['cache_enabled'] = isset($input['cache_enabled']) ? (bool) $input['cache_enabled'] : true;
        
        // Sanitize log level
        if (isset($input['log_level'])) {
            $sanitized['log_level'] = $this->validator->validate_text($input['log_level']);
        }
        
        // Apply filters for extensions
        $sanitized = apply_filters('sapg_sanitize_options', $sanitized, $input);
        
        $this->logger->info('Plugin options updated');
        
        return $sanitized;
    }
    
    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook.
     * @return void
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'sapg') === false) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'sapg-admin-style',
            SAPG_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            Config::VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'sapg-admin-script',
            SAPG_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery'),
            Config::VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('sapg-admin-script', 'sapgData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sapg_ajax'),
            'i18n' => array(
                'generating' => __('Generating content...', Config::TEXT_DOMAIN),
                'success' => __('Content generated successfully!', Config::TEXT_DOMAIN),
                'error' => __('An error occurred. Please try again.', Config::TEXT_DOMAIN),
            ),
        ));
    }
    
    /**
     * Add plugin action links
     *
     * @param array $links Existing action links.
     * @return array Modified action links.
     */
    public function add_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=sapg-content-generator'),
            __('Settings', Config::TEXT_DOMAIN)
        );
        
        array_unshift($links, $settings_link);
        
        return $links;
    }
    
    /**
     * AJAX handler for testing API key
     *
     * @return void
     */
    public function ajax_test_api_key() {
        // Verify nonce
        if (!$this->validator->verify_nonce($_POST['nonce'] ?? '', 'sapg_ajax')) {
            wp_send_json_error(__('Security check failed', Config::TEXT_DOMAIN));
        }
        
        // Check capability
        if (!$this->validator->check_capability()) {
            wp_send_json_error(__('Insufficient permissions', Config::TEXT_DOMAIN));
        }
        
        // Get parameters
        $api = $this->validator->validate_ai_model($_POST['api'] ?? '');
        $key = $this->validator->validate_api_key($_POST['key'] ?? '');
        
        if ($api === false || $key === false) {
            wp_send_json_error(__('Invalid parameters', Config::TEXT_DOMAIN));
        }
        
        // Test API
        $result = $this->content_generator->test_api($api, $key);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success(__('API connection successful', Config::TEXT_DOMAIN));
    }
    
    /**
     * AJAX handler for generating content
     *
     * @return void
     */
    public function ajax_generate_content() {
        // Verify nonce
        if (!$this->validator->verify_nonce($_POST['nonce'] ?? '', 'sapg_ajax')) {
            wp_send_json_error(__('Security check failed', Config::TEXT_DOMAIN));
        }
        
        // Check capability
        if (!$this->validator->check_capability()) {
            wp_send_json_error(__('Insufficient permissions', Config::TEXT_DOMAIN));
        }
        
        // Validate and sanitize input
        $title = $this->validator->validate_text($_POST['title'] ?? '', 'title', false, 200);
        $model = $this->validator->validate_ai_model($_POST['model'] ?? '');
        $word_count = $this->validator->validate_word_count($_POST['word_count'] ?? 500);
        $content_type = $this->validator->validate_content_type($_POST['content_type'] ?? 'post');
        $post_status = $this->validator->validate_post_status($_POST['post_status'] ?? 'draft');
        
        if ($model === false || $word_count === false || $content_type === false || $post_status === false) {
            wp_send_json_error(__('Invalid parameters', Config::TEXT_DOMAIN));
        }
        
        // Generate content
        $result = $this->content_generator->generate_and_create_post(
            $title,
            $model,
            $word_count,
            $content_type,
            $post_status
        );
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success(array(
            'post_id' => $result,
            'edit_url' => get_edit_post_link($result, 'raw'),
        ));
    }
    
    /**
     * Plugin activation
     *
     * @return void
     */
    public function activate() {
        // Add default options
        if (!get_option(Config::OPTION_NAME)) {
            add_option(Config::OPTION_NAME, Config::get_default_options());
        }
        
        // Schedule log cleanup
        if (!wp_next_scheduled('sapg_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'sapg_cleanup_logs');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        $this->logger->info('Plugin activated');
    }
    
    /**
     * Plugin deactivation
     *
     * @return void
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('sapg_cleanup_logs');
        
        // Clear transients
        $this->clear_cache();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        $this->logger->info('Plugin deactivated');
    }
    
    /**
     * Clean up old log files
     *
     * @return void
     */
    public function cleanup_old_logs() {
        $this->logger->clear_old_logs(30);
        $this->logger->info('Old log files cleaned up');
    }
    
    /**
     * Clear plugin cache
     *
     * @return void
     */
    private function clear_cache() {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_sapg_api_') . '%'
            )
        );
        
        $this->logger->debug('Cache cleared');
    }
    
    /**
     * Check system requirements
     *
     * @return bool True if requirements are met.
     */
    private function check_requirements() {
        // Check PHP version
        if (version_compare(PHP_VERSION, Config::MIN_PHP_VERSION, '<')) {
            add_action('admin_notices', function() {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    sprintf(
                        __('Simple AI Page Generator requires PHP %s or higher. You are running PHP %s.', Config::TEXT_DOMAIN),
                        Config::MIN_PHP_VERSION,
                        PHP_VERSION
                    )
                );
            });
            
            $this->logger->error('PHP version requirement not met', array(
                'required' => Config::MIN_PHP_VERSION,
                'current' => PHP_VERSION,
            ));
            
            return false;
        }
        
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), Config::MIN_WP_VERSION, '<')) {
            add_action('admin_notices', function() {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    sprintf(
                        __('Simple AI Page Generator requires WordPress %s or higher.', Config::TEXT_DOMAIN),
                        Config::MIN_WP_VERSION
                    )
                );
            });
            
            $this->logger->error('WordPress version requirement not met', array(
                'required' => Config::MIN_WP_VERSION,
                'current' => get_bloginfo('version'),
            ));
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Get logger instance
     *
     * @return Logger Logger instance.
     */
    public function get_logger() {
        return $this->logger;
    }
    
    /**
     * Get validator instance
     *
     * @return Validator Validator instance.
     */
    public function get_validator() {
        return $this->validator;
    }
    
    /**
     * Get content generator instance
     *
     * @return Content_Generator Content generator instance.
     */
    public function get_content_generator() {
        return $this->content_generator;
    }
}
