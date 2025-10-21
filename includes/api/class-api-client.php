<?php
/**
 * Base API Client class for Simple AI Page Generator
 *
 * Abstract class for AI API integrations.
 *
 * @package Simple_AI_Page_Generator
 * @subpackage API
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator\API;

use Simple_AI_Page_Generator\Config;
use Simple_AI_Page_Generator\Core\Logger;
use WP_Error;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract Class API_Client
 *
 * Base class for all AI API clients.
 *
 * @since 2.0.0
 */
abstract class API_Client {
    
    /**
     * API key
     *
     * @var string
     */
    protected $api_key;
    
    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger;
    
    /**
     * API endpoint
     *
     * @var string
     */
    protected $endpoint;
    
    /**
     * Model identifier
     *
     * @var string
     */
    protected $model;
    
    /**
     * Cache prefix
     *
     * @var string
     */
    protected $cache_prefix = 'sapg_api_';
    
    /**
     * Constructor
     *
     * @param string $api_key API key.
     * @param Logger $logger Logger instance.
     */
    public function __construct($api_key, Logger $logger) {
        $this->api_key = $api_key;
        $this->logger = $logger;
    }
    
    /**
     * Generate content
     *
     * @param string $prompt Content prompt.
     * @param int    $word_count Target word count.
     * @param array  $options Additional options.
     * @return string|WP_Error Generated content or error.
     */
    abstract public function generate_content($prompt, $word_count, array $options = array());
    
    /**
     * Test API connection
     *
     * @return bool|WP_Error True if successful, WP_Error on failure.
     */
    abstract public function test_connection();
    
    /**
     * Make API request
     *
     * @param string $url API URL.
     * @param array  $args Request arguments.
     * @return array|WP_Error Response body or error.
     */
    protected function make_request($url, array $args = array()) {
        // Set default timeout
        if (!isset($args['timeout'])) {
            $args['timeout'] = Config::API_TIMEOUT;
        }
        
        // Log request (without sensitive data)
        $this->logger->debug('Making API request', array(
            'url' => $url,
            'method' => isset($args['method']) ? $args['method'] : 'POST',
        ));
        
        // Make request
        $response = wp_remote_post($url, $args);
        
        // Check for errors
        if (is_wp_error($response)) {
            $this->logger->error('API request failed', array(
                'error' => $response->get_error_message(),
                'url' => $url,
            ));
            return $response;
        }
        
        // Get response code
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Check response code
        if ($response_code < 200 || $response_code >= 300) {
            $error_message = sprintf(
                'API returned error code %d',
                $response_code
            );
            
            $this->logger->error($error_message, array(
                'response_code' => $response_code,
                'response_body' => wp_remote_retrieve_body($response),
            ));
            
            return new WP_Error('api_error', $error_message);
        }
        
        // Parse JSON response
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Failed to parse API response', array(
                'json_error' => json_last_error_msg(),
            ));
            return new WP_Error('json_error', 'Failed to parse API response');
        }
        
        return $body;
    }
    
    /**
     * Get cached response
     *
     * @param string $cache_key Cache key.
     * @return mixed|false Cached data or false.
     */
    protected function get_cached_response($cache_key) {
        $options = get_option(Config::OPTION_NAME, Config::get_default_options());
        
        if (!isset($options['cache_enabled']) || !$options['cache_enabled']) {
            return false;
        }
        
        $cached = get_transient($this->cache_prefix . $cache_key);
        
        if ($cached !== false) {
            $this->logger->debug('Using cached API response', array('cache_key' => $cache_key));
        }
        
        return $cached;
    }
    
    /**
     * Set cached response
     *
     * @param string $cache_key Cache key.
     * @param mixed  $data Data to cache.
     * @param int    $expiration Cache expiration in seconds.
     * @return bool True on success.
     */
    protected function set_cached_response($cache_key, $data, $expiration = null) {
        $options = get_option(Config::OPTION_NAME, Config::get_default_options());
        
        if (!isset($options['cache_enabled']) || !$options['cache_enabled']) {
            return false;
        }
        
        if ($expiration === null) {
            $expiration = Config::CACHE_EXPIRATION;
        }
        
        $result = set_transient($this->cache_prefix . $cache_key, $data, $expiration);
        
        if ($result) {
            $this->logger->debug('Cached API response', array(
                'cache_key' => $cache_key,
                'expiration' => $expiration,
            ));
        }
        
        return $result;
    }
    
    /**
     * Generate cache key
     *
     * @param string $prompt Prompt text.
     * @param int    $word_count Word count.
     * @param array  $options Additional options.
     * @return string Cache key.
     */
    protected function generate_cache_key($prompt, $word_count, array $options = array()) {
        $data = array(
            'model' => $this->model,
            'prompt' => $prompt,
            'word_count' => $word_count,
            'options' => $options,
        );
        
        return md5(wp_json_encode($data));
    }
    
    /**
     * Calculate max tokens from word count
     *
     * @param int $word_count Target word count.
     * @return int Estimated max tokens.
     */
    protected function calculate_max_tokens($word_count) {
        // Rough estimation: 1 word ≈ 1.3 tokens
        // Add 20% buffer for formatting
        return (int) ceil($word_count * 1.3 * 1.2);
    }
    
    /**
     * Build system message for content generation
     *
     * @return string System message.
     */
    protected function build_system_message() {
        return apply_filters(
            'sapg_api_system_message',
            'You are an expert content writer specializing in creating high-quality, SEO-optimized content for WordPress websites. Generate well-structured content with proper HTML formatting including headings (h2, h3), paragraphs (p), lists (ul, ol, li), and emphasis (strong, em) where appropriate.'
        );
    }
    
    /**
     * Validate API key format
     *
     * @return bool True if valid.
     */
    protected function validate_api_key() {
        if (empty($this->api_key)) {
            $this->logger->error('API key is empty');
            return false;
        }
        
        if (strlen($this->api_key) < 10) {
            $this->logger->error('API key is too short');
            return false;
        }
        
        return true;
    }
}
