<?php
/**
 * Configuration class for Simple AI Page Generator
 *
 * Centralized configuration management for the plugin.
 *
 * @package Simple_AI_Page_Generator
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Config
 *
 * Manages plugin configuration and constants.
 *
 * @since 2.0.0
 */
class Config {
    
    /**
     * Plugin version
     *
     * @var string
     */
    const VERSION = '2.0.0';
    
    /**
     * Minimum PHP version required
     *
     * @var string
     */
    const MIN_PHP_VERSION = '7.4';
    
    /**
     * Minimum WordPress version required
     *
     * @var string
     */
    const MIN_WP_VERSION = '5.8';
    
    /**
     * Plugin text domain
     *
     * @var string
     */
    const TEXT_DOMAIN = 'ai-content-gen';
    
    /**
     * Option name in database
     *
     * @var string
     */
    const OPTION_NAME = 'sapg_options';
    
    /**
     * Cache expiration time (in seconds)
     *
     * @var int
     */
    const CACHE_EXPIRATION = 3600; // 1 hour
    
    /**
     * Maximum content generation timeout (in seconds)
     *
     * @var int
     */
    const API_TIMEOUT = 60;
    
    /**
     * Supported AI models
     *
     * @var array
     */
    const SUPPORTED_MODELS = array(
        'openai' => array(
            'name' => 'OpenAI',
            'model' => 'gpt-3.5-turbo',
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'docs' => 'https://platform.openai.com/docs/api-reference',
        ),
        'deepseek' => array(
            'name' => 'DeepSeek',
            'model' => 'deepseek-chat',
            'endpoint' => 'https://api.deepseek.com/v1/chat/completions',
            'docs' => 'https://platform.deepseek.com',
        ),
        'gemini' => array(
            'name' => 'Google Gemini',
            'model' => 'gemini-pro',
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent',
            'docs' => 'https://ai.google.dev/docs',
        ),
        'anthropic' => array(
            'name' => 'Claude (Anthropic)',
            'model' => 'claude-3-sonnet-20240229',
            'endpoint' => 'https://api.anthropic.com/v1/messages',
            'docs' => 'https://docs.anthropic.com/claude/reference',
        ),
    );
    
    /**
     * Default word count options
     *
     * @var array
     */
    const WORD_COUNT_OPTIONS = array(100, 300, 500, 1000, 2000);
    
    /**
     * Allowed content types
     *
     * @var array
     */
    const CONTENT_TYPES = array('post', 'page');
    
    /**
     * Allowed post statuses
     *
     * @var array
     */
    const POST_STATUSES = array('publish', 'draft', 'pending');
    
    /**
     * Log levels
     *
     * @var array
     */
    const LOG_LEVELS = array(
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7,
    );
    
    /**
     * Get default options
     *
     * @return array Default plugin options.
     */
    public static function get_default_options() {
        return array(
            'ai_model' => 'openai',
            'content_type' => 'post',
            'word_count' => 500,
            'post_status' => 'draft',
            'enable_logging' => true,
            'log_level' => 'info',
            'cache_enabled' => true,
            'openai_key' => '',
            'deepseek_key' => '',
            'gemini_key' => '',
            'anthropic_key' => '',
        );
    }
    
    /**
     * Get plugin capability requirement
     *
     * @return string Required capability.
     */
    public static function get_required_capability() {
        return apply_filters('sapg_required_capability', 'manage_options');
    }
    
    /**
     * Check if debug mode is enabled
     *
     * @return bool True if debug mode is enabled.
     */
    public static function is_debug_enabled() {
        return defined('WP_DEBUG') && WP_DEBUG;
    }
    
    /**
     * Get API configuration for a specific model
     *
     * @param string $model Model identifier.
     * @return array|null Model configuration or null if not found.
     */
    public static function get_model_config($model) {
        return isset(self::SUPPORTED_MODELS[$model]) ? self::SUPPORTED_MODELS[$model] : null;
    }
    
    /**
     * Validate word count
     *
     * @param int $word_count Word count to validate.
     * @return bool True if valid.
     */
    public static function is_valid_word_count($word_count) {
        return in_array((int) $word_count, self::WORD_COUNT_OPTIONS, true);
    }
    
    /**
     * Validate content type
     *
     * @param string $content_type Content type to validate.
     * @return bool True if valid.
     */
    public static function is_valid_content_type($content_type) {
        return in_array($content_type, self::CONTENT_TYPES, true);
    }
    
    /**
     * Validate post status
     *
     * @param string $status Post status to validate.
     * @return bool True if valid.
     */
    public static function is_valid_post_status($status) {
        return in_array($status, self::POST_STATUSES, true);
    }
}
