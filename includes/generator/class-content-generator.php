<?php
/**
 * Content Generator class for Simple AI Page Generator
 *
 * Orchestrates content generation using various AI APIs.
 *
 * @package Simple_AI_Page_Generator
 * @subpackage Generator
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator\Generator;

use Simple_AI_Page_Generator\Config;
use Simple_AI_Page_Generator\Core\Logger;
use Simple_AI_Page_Generator\Core\Validator;
use Simple_AI_Page_Generator\API\OpenAI_Client;
use WP_Error;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Content_Generator
 *
 * Manages content generation workflow.
 *
 * @since 2.0.0
 */
class Content_Generator {
    
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
     * Plugin options
     *
     * @var array
     */
    private $options;
    
    /**
     * API clients cache
     *
     * @var array
     */
    private $api_clients = array();
    
    /**
     * Constructor
     *
     * @param Logger    $logger Logger instance.
     * @param Validator $validator Validator instance.
     */
    public function __construct(Logger $logger, Validator $validator) {
        $this->logger = $logger;
        $this->validator = $validator;
        $this->options = get_option(Config::OPTION_NAME, Config::get_default_options());
    }
    
    /**
     * Generate content and create post
     *
     * @param string $title Post title.
     * @param string $model AI model to use.
     * @param int    $word_count Target word count.
     * @param string $content_type Content type (post/page).
     * @param string $post_status Post status.
     * @return int|WP_Error Post ID on success, WP_Error on failure.
     */
    public function generate_and_create_post($title, $model, $word_count, $content_type, $post_status) {
        $this->logger->info('Starting content generation', array(
            'title' => $title,
            'model' => $model,
            'word_count' => $word_count,
            'content_type' => $content_type,
        ));
        
        // Get API client
        $client = $this->get_api_client($model);
        
        if (is_wp_error($client)) {
            return $client;
        }
        
        // Build prompt
        $prompt = $this->build_prompt($title, $content_type, $word_count);
        
        // Generate content
        $content = $client->generate_content($prompt, $word_count);
        
        if (is_wp_error($content)) {
            $this->logger->error('Content generation failed', array(
                'error' => $content->get_error_message(),
            ));
            return $content;
        }
        
        // Create post
        $post_id = $this->create_post($title, $content, $content_type, $post_status, $model, $word_count);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        $this->logger->info('Content generated and post created successfully', array(
            'post_id' => $post_id,
        ));
        
        // Fire action for extensions
        do_action('sapg_content_generated', $post_id, $model, $word_count);
        
        return $post_id;
    }
    
    /**
     * Get API client for specified model
     *
     * @param string $model Model identifier.
     * @return object|WP_Error API client instance or error.
     */
    private function get_api_client($model) {
        // Check if client is already instantiated
        if (isset($this->api_clients[$model])) {
            return $this->api_clients[$model];
        }
        
        // Get API key
        $key_name = $model . '_key';
        $api_key = isset($this->options[$key_name]) ? $this->options[$key_name] : '';
        
        if (empty($api_key)) {
            return new WP_Error(
                'missing_api_key',
                sprintf(__('API key for %s is not configured', Config::TEXT_DOMAIN), $model)
            );
        }
        
        // Instantiate appropriate client
        switch ($model) {
            case 'openai':
                $this->api_clients[$model] = new OpenAI_Client($api_key, $this->logger);
                break;
                
            // Add other API clients here
            // case 'deepseek':
            //     $this->api_clients[$model] = new DeepSeek_Client($api_key, $this->logger);
            //     break;
            
            default:
                return new WP_Error(
                    'unsupported_model',
                    sprintf(__('Model %s is not supported', Config::TEXT_DOMAIN), $model)
                );
        }
        
        return $this->api_clients[$model];
    }
    
    /**
     * Build content generation prompt
     *
     * @param string $title Content title.
     * @param string $content_type Content type.
     * @param int    $word_count Target word count.
     * @return string Generated prompt.
     */
    private function build_prompt($title, $content_type, $word_count) {
        $type_label = $content_type === 'post' ? __('blog post', Config::TEXT_DOMAIN) : __('page', Config::TEXT_DOMAIN);
        
        $prompt = sprintf(
            __('Write a comprehensive %s of approximately %d words', Config::TEXT_DOMAIN),
            $type_label,
            $word_count
        );
        
        if (!empty($title)) {
            $prompt .= sprintf(__(' about: %s', Config::TEXT_DOMAIN), $title);
        }
        
        $prompt .= '. ' . __(
            'Structure the content with proper HTML formatting including headings (h2, h3), paragraphs, lists, and emphasis where appropriate. Make it engaging, informative, and SEO-friendly.',
            Config::TEXT_DOMAIN
        );
        
        // Allow filtering of prompt
        return apply_filters('sapg_generation_prompt', $prompt, $title, $content_type, $word_count);
    }
    
    /**
     * Create WordPress post with generated content
     *
     * @param string $title Post title.
     * @param string $content Post content.
     * @param string $content_type Content type.
     * @param string $post_status Post status.
     * @param string $model AI model used.
     * @param int    $word_count Word count.
     * @return int|WP_Error Post ID on success, WP_Error on failure.
     */
    private function create_post($title, $content, $content_type, $post_status, $model, $word_count) {
        // Prepare post data
        $post_data = array(
            'post_title' => !empty($title) ? $title : sprintf(
                __('Generated Content - %s', Config::TEXT_DOMAIN),
                current_time('Y-m-d H:i:s')
            ),
            'post_content' => wp_kses_post($content),
            'post_status' => $post_status,
            'post_type' => $content_type,
            'post_author' => get_current_user_id(),
        );
        
        // Allow filtering of post data
        $post_data = apply_filters('sapg_post_data', $post_data, $model, $word_count);
        
        // Insert post
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            $this->logger->error('Failed to create post', array(
                'error' => $post_id->get_error_message(),
            ));
            return $post_id;
        }
        
        // Add post meta
        $this->add_post_meta($post_id, $model, $word_count);
        
        return $post_id;
    }
    
    /**
     * Add metadata to generated post
     *
     * @param int    $post_id Post ID.
     * @param string $model AI model used.
     * @param int    $word_count Word count.
     * @return void
     */
    private function add_post_meta($post_id, $model, $word_count) {
        $meta_data = array(
            '_sapg_generated' => true,
            '_sapg_model' => sanitize_text_field($model),
            '_sapg_word_count' => intval($word_count),
            '_sapg_generated_date' => current_time('mysql'),
            '_sapg_version' => Config::VERSION,
        );
        
        foreach ($meta_data as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
        
        $this->logger->debug('Post metadata added', array('post_id' => $post_id));
    }
    
    /**
     * Test API connection
     *
     * @param string $model Model identifier.
     * @param string $api_key API key to test.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function test_api($model, $api_key) {
        $this->logger->info('Testing API connection', array('model' => $model));
        
        // Instantiate client with provided key
        switch ($model) {
            case 'openai':
                $client = new OpenAI_Client($api_key, $this->logger);
                break;
                
            // Add other API clients here
            
            default:
                return new WP_Error(
                    'unsupported_model',
                    sprintf(__('Model %s is not supported', Config::TEXT_DOMAIN), $model)
                );
        }
        
        // Test connection
        $result = $client->test_connection();
        
        if (is_wp_error($result)) {
            $this->logger->warning('API test failed', array(
                'model' => $model,
                'error' => $result->get_error_message(),
            ));
        } else {
            $this->logger->info('API test successful', array('model' => $model));
        }
        
        return $result;
    }
    
    /**
     * Get generation statistics
     *
     * @return array Statistics data.
     */
    public function get_statistics() {
        global $wpdb;
        
        $stats = array(
            'total_generated' => 0,
            'by_model' => array(),
            'by_type' => array(),
        );
        
        // Count total generated posts
        $stats['total_generated'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_sapg_generated' AND meta_value = '1'"
        );
        
        // Count by model
        $models = $wpdb->get_results(
            "SELECT meta_value as model, COUNT(*) as count 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_sapg_model' 
            GROUP BY meta_value"
        );
        
        foreach ($models as $model) {
            $stats['by_model'][$model->model] = intval($model->count);
        }
        
        // Count by content type
        $types = $wpdb->get_results(
            "SELECT p.post_type, COUNT(*) as count 
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE pm.meta_key = '_sapg_generated' AND pm.meta_value = '1'
            GROUP BY p.post_type"
        );
        
        foreach ($types as $type) {
            $stats['by_type'][$type->post_type] = intval($type->count);
        }
        
        return $stats;
    }
}
