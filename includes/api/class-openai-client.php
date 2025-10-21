<?php
/**
 * OpenAI API Client for Simple AI Page Generator
 *
 * Handles OpenAI API integration.
 *
 * @package Simple_AI_Page_Generator
 * @subpackage API
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator\API;

use Simple_AI_Page_Generator\Config;
use WP_Error;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class OpenAI_Client
 *
 * OpenAI API client implementation.
 *
 * @since 2.0.0
 */
class OpenAI_Client extends API_Client {
    
    /**
     * Constructor
     *
     * @param string $api_key API key.
     * @param \Simple_AI_Page_Generator\Core\Logger $logger Logger instance.
     */
    public function __construct($api_key, $logger) {
        parent::__construct($api_key, $logger);
        
        $config = Config::get_model_config('openai');
        $this->endpoint = $config['endpoint'];
        $this->model = $config['model'];
    }
    
    /**
     * Generate content using OpenAI API
     *
     * @param string $prompt Content prompt.
     * @param int    $word_count Target word count.
     * @param array  $options Additional options.
     * @return string|WP_Error Generated content or error.
     */
    public function generate_content($prompt, $word_count, array $options = array()) {
        // Validate API key
        if (!$this->validate_api_key()) {
            return new WP_Error('invalid_api_key', __('Invalid OpenAI API key', Config::TEXT_DOMAIN));
        }
        
        // Check cache
        $cache_key = $this->generate_cache_key($prompt, $word_count, $options);
        $cached = $this->get_cached_response($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Prepare request
        $max_tokens = $this->calculate_max_tokens($word_count);
        $temperature = isset($options['temperature']) ? floatval($options['temperature']) : 0.7;
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'model' => $this->model,
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => $this->build_system_message(),
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt,
                    ),
                ),
                'max_tokens' => $max_tokens,
                'temperature' => $temperature,
            )),
        );
        
        // Make request
        $this->logger->info('Generating content with OpenAI', array(
            'word_count' => $word_count,
            'model' => $this->model,
        ));
        
        $response = $this->make_request($this->endpoint, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Extract content
        if (!isset($response['choices'][0]['message']['content'])) {
            $this->logger->error('Invalid OpenAI response structure', array('response' => $response));
            return new WP_Error('invalid_response', __('Invalid response from OpenAI API', Config::TEXT_DOMAIN));
        }
        
        $content = $response['choices'][0]['message']['content'];
        
        // Cache the response
        $this->set_cached_response($cache_key, $content);
        
        $this->logger->info('Content generated successfully with OpenAI');
        
        return $content;
    }
    
    /**
     * Test OpenAI API connection
     *
     * @return bool|WP_Error True if successful, WP_Error on failure.
     */
    public function test_connection() {
        if (!$this->validate_api_key()) {
            return new WP_Error('invalid_api_key', __('Invalid OpenAI API key', Config::TEXT_DOMAIN));
        }
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'model' => $this->model,
                'messages' => array(
                    array('role' => 'user', 'content' => 'Test'),
                ),
                'max_tokens' => 5,
            )),
            'timeout' => 15,
        );
        
        $response = $this->make_request($this->endpoint, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['choices'])) {
            return new WP_Error('test_failed', __('OpenAI API test failed', Config::TEXT_DOMAIN));
        }
        
        return true;
    }
}
