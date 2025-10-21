<?php
/**
 * Admin Controller for Simple AI Page Generator
 *
 * Handles all admin interface functionality.
 *
 * @package Simple_AI_Page_Generator
 * @subpackage Admin
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator\Admin;

use Simple_AI_Page_Generator\Config;
use Simple_AI_Page_Generator\Core\Logger;
use Simple_AI_Page_Generator\Core\Validator;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Admin_Controller
 *
 * Manages admin pages and settings.
 *
 * @since 2.0.0
 */
class Admin_Controller {
    
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
     * Constructor
     *
     * @param Logger    $logger Logger instance.
     * @param Validator $validator Validator instance.
     */
    public function __construct(Logger $logger, Validator $validator) {
        $this->logger = $logger;
        $this->validator = $validator;
    }
    
    /**
     * Register admin menu
     *
     * @return void
     */
    public function register_menu() {
        add_menu_page(
            __('AI Content Generator', Config::TEXT_DOMAIN),
            __('AI Generator', Config::TEXT_DOMAIN),
            Config::get_required_capability(),
            'sapg-content-generator',
            array($this, 'render_main_page'),
            'dashicons-edit',
            21
        );
        
        add_submenu_page(
            'sapg-content-generator',
            __('Settings', Config::TEXT_DOMAIN),
            __('Settings', Config::TEXT_DOMAIN),
            Config::get_required_capability(),
            'sapg-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'sapg-content-generator',
            __('Statistics', Config::TEXT_DOMAIN),
            __('Statistics', Config::TEXT_DOMAIN),
            Config::get_required_capability(),
            'sapg-statistics',
            array($this, 'render_statistics_page')
        );
    }
    
    /**
     * Render main generator page
     *
     * @return void
     */
    public function render_main_page() {
        if (!$this->validator->check_capability()) {
            wp_die(__('You do not have sufficient permissions to access this page.', Config::TEXT_DOMAIN));
        }
        
        $options = get_option(Config::OPTION_NAME, Config::get_default_options());
        
        // Check if any API is configured
        $has_api = false;
        foreach (array_keys(Config::SUPPORTED_MODELS) as $model) {
            if (!empty($options[$model . '_key'])) {
                $has_api = true;
                break;
            }
        }
        
        include SAPG_PLUGIN_DIR . 'templates/admin/main-page.php';
    }
    
    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings_page() {
        if (!$this->validator->check_capability()) {
            wp_die(__('You do not have sufficient permissions to access this page.', Config::TEXT_DOMAIN));
        }
        
        // Register settings sections
        $this->register_settings_sections();
        
        include SAPG_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }
    
    /**
     * Render statistics page
     *
     * @return void
     */
    public function render_statistics_page() {
        if (!$this->validator->check_capability()) {
            wp_die(__('You do not have sufficient permissions to access this page.', Config::TEXT_DOMAIN));
        }
        
        include SAPG_PLUGIN_DIR . 'templates/admin/statistics-page.php';
    }
    
    /**
     * Register settings sections and fields
     *
     * @return void
     */
    private function register_settings_sections() {
        // API Settings Section
        add_settings_section(
            'sapg_api_section',
            __('API Configuration', Config::TEXT_DOMAIN),
            array($this, 'render_api_section_description'),
            'sapg-settings'
        );
        
        // Add API key fields
        foreach (Config::SUPPORTED_MODELS as $model_id => $model_config) {
            add_settings_field(
                'sapg_' . $model_id . '_key',
                $model_config['name'] . ' ' . __('API Key', Config::TEXT_DOMAIN),
                array($this, 'render_api_key_field'),
                'sapg-settings',
                'sapg_api_section',
                array(
                    'model_id' => $model_id,
                    'model_name' => $model_config['name'],
                    'docs_url' => $model_config['docs'],
                )
            );
        }
        
        // General Settings Section
        add_settings_section(
            'sapg_general_section',
            __('General Settings', Config::TEXT_DOMAIN),
            array($this, 'render_general_section_description'),
            'sapg-settings'
        );
        
        add_settings_field(
            'sapg_default_model',
            __('Default AI Model', Config::TEXT_DOMAIN),
            array($this, 'render_model_field'),
            'sapg-settings',
            'sapg_general_section'
        );
        
        add_settings_field(
            'sapg_default_word_count',
            __('Default Word Count', Config::TEXT_DOMAIN),
            array($this, 'render_word_count_field'),
            'sapg-settings',
            'sapg_general_section'
        );
        
        add_settings_field(
            'sapg_cache_enabled',
            __('Enable Caching', Config::TEXT_DOMAIN),
            array($this, 'render_cache_field'),
            'sapg-settings',
            'sapg_general_section'
        );
        
        add_settings_field(
            'sapg_enable_logging',
            __('Enable Logging', Config::TEXT_DOMAIN),
            array($this, 'render_logging_field'),
            'sapg-settings',
            'sapg_general_section'
        );
    }
    
    /**
     * Render API section description
     *
     * @return void
     */
    public function render_api_section_description() {
        echo '<p>' . esc_html__('Configure your API keys for different AI services. At least one API key is required.', Config::TEXT_DOMAIN) . '</p>';
    }
    
    /**
     * Render general section description
     *
     * @return void
     */
    public function render_general_section_description() {
        echo '<p>' . esc_html__('Configure default settings for content generation.', Config::TEXT_DOMAIN) . '</p>';
    }
    
    /**
     * Render API key field
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_api_key_field($args) {
        $options = get_option(Config::OPTION_NAME, Config::get_default_options());
        $field_name = $args['model_id'] . '_key';
        $value = isset($options[$field_name]) ? $options[$field_name] : '';
        
        printf(
            '<input type="password" id="sapg_%s" name="%s[%s]" value="%s" class="regular-text" autocomplete="off" />',
            esc_attr($field_name),
            esc_attr(Config::OPTION_NAME),
            esc_attr($field_name),
            esc_attr($value)
        );
        
        printf(
            ' <button type="button" class="button sapg-toggle-password" data-target="sapg_%s">%s</button>',
            esc_attr($field_name),
            esc_html__('Show/Hide', Config::TEXT_DOMAIN)
        );
        
        if (!empty($value)) {
            printf(
                ' <button type="button" class="button sapg-test-api" data-api="%s">%s</button>',
                esc_attr($args['model_id']),
                esc_html__('Test Connection', Config::TEXT_DOMAIN)
            );
        }
        
        printf(
            '<p class="description"><a href="%s" target="_blank">%s</a></p>',
            esc_url($args['docs_url']),
            sprintf(esc_html__('Get your %s API key', Config::TEXT_DOMAIN), esc_html($args['model_name']))
        );
    }
    
    /**
     * Render model selection field
     *
     * @return void
     */
    public function render_model_field() {
        $options = get_option(Config::OPTION_NAME, Config::get_default_options());
        $selected = isset($options['ai_model']) ? $options['ai_model'] : 'openai';
        
        echo '<select name="' . esc_attr(Config::OPTION_NAME) . '[ai_model]" id="sapg_ai_model" class="regular-text">';
        
        foreach (Config::SUPPORTED_MODELS as $model_id => $model_config) {
            $has_key = !empty($options[$model_id . '_key']);
            $disabled = !$has_key ? 'disabled' : '';
            
            printf(
                '<option value="%s" %s %s>%s%s</option>',
                esc_attr($model_id),
                selected($selected, $model_id, false),
                $disabled,
                esc_html($model_config['name']),
                !$has_key ? ' (' . esc_html__('Not configured', Config::TEXT_DOMAIN) . ')' : ''
            );
        }
        
        echo '</select>';
        echo '<p class="description">' . esc_html__('Select the default AI model for content generation.', Config::TEXT_DOMAIN) . '</p>';
    }
    
    /**
     * Render word count field
     *
     * @return void
     */
    public function render_word_count_field() {
        $options = get_option(Config::OPTION_NAME, Config::get_default_options());
        $selected = isset($options['word_count']) ? $options['word_count'] : 500;
        
        echo '<select name="' . esc_attr(Config::OPTION_NAME) . '[word_count]" id="sapg_word_count" class="regular-text">';
        
        foreach (Config::WORD_COUNT_OPTIONS as $count) {
            printf(
                '<option value="%d" %s>%d %s</option>',
                $count,
                selected($selected, $count, false),
                $count,
                esc_html__('words', Config::TEXT_DOMAIN)
            );
        }
        
        echo '</select>';
        echo '<p class="description">' . esc_html__('Default word count for generated content.', Config::TEXT_DOMAIN) . '</p>';
    }
    
    /**
     * Render cache field
     *
     * @return void
     */
    public function render_cache_field() {
        $options = get_option(Config::OPTION_NAME, Config::get_default_options());
        $checked = isset($options['cache_enabled']) ? $options['cache_enabled'] : true;
        
        printf(
            '<label><input type="checkbox" name="%s[cache_enabled]" value="1" %s /> %s</label>',
            esc_attr(Config::OPTION_NAME),
            checked($checked, true, false),
            esc_html__('Cache API responses to improve performance and reduce costs', Config::TEXT_DOMAIN)
        );
    }
    
    /**
     * Render logging field
     *
     * @return void
     */
    public function render_logging_field() {
        $options = get_option(Config::OPTION_NAME, Config::get_default_options());
        $checked = isset($options['enable_logging']) ? $options['enable_logging'] : true;
        
        printf(
            '<label><input type="checkbox" name="%s[enable_logging]" value="1" %s /> %s</label>',
            esc_attr(Config::OPTION_NAME),
            checked($checked, true, false),
            esc_html__('Enable detailed logging for debugging', Config::TEXT_DOMAIN)
        );
    }
}
