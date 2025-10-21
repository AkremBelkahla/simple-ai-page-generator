<?php
/**
 * Validator class for Simple AI Page Generator
 *
 * Provides strict input validation and sanitization.
 *
 * @package Simple_AI_Page_Generator
 * @subpackage Core
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator\Core;

use Simple_AI_Page_Generator\Config;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Validator
 *
 * Handles all input validation and sanitization operations.
 *
 * @since 2.0.0
 */
class Validator {
    
    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;
    
    /**
     * Constructor
     *
     * @param Logger $logger Logger instance.
     */
    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }
    
    /**
     * Validate and sanitize text input
     *
     * @param mixed  $input Input to validate.
     * @param string $field_name Field name for error messages.
     * @param bool   $required Whether field is required.
     * @param int    $max_length Maximum length allowed.
     * @return string|false Sanitized text or false on failure.
     */
    public function validate_text($input, $field_name = 'text', $required = false, $max_length = 255) {
        if ($required && empty($input)) {
            $this->logger->warning("Validation failed: {$field_name} is required but empty");
            return false;
        }
        
        if (empty($input)) {
            return '';
        }
        
        // Sanitize
        $sanitized = sanitize_text_field($input);
        
        // Check length
        if (strlen($sanitized) > $max_length) {
            $this->logger->warning("Validation failed: {$field_name} exceeds maximum length of {$max_length}");
            return false;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate and sanitize textarea input
     *
     * @param mixed  $input Input to validate.
     * @param string $field_name Field name for error messages.
     * @param bool   $required Whether field is required.
     * @param int    $max_length Maximum length allowed.
     * @return string|false Sanitized textarea or false on failure.
     */
    public function validate_textarea($input, $field_name = 'textarea', $required = false, $max_length = 10000) {
        if ($required && empty($input)) {
            $this->logger->warning("Validation failed: {$field_name} is required but empty");
            return false;
        }
        
        if (empty($input)) {
            return '';
        }
        
        // Sanitize
        $sanitized = sanitize_textarea_field($input);
        
        // Check length
        if (strlen($sanitized) > $max_length) {
            $this->logger->warning("Validation failed: {$field_name} exceeds maximum length of {$max_length}");
            return false;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate and sanitize email
     *
     * @param mixed  $input Input to validate.
     * @param string $field_name Field name for error messages.
     * @param bool   $required Whether field is required.
     * @return string|false Sanitized email or false on failure.
     */
    public function validate_email($input, $field_name = 'email', $required = false) {
        if ($required && empty($input)) {
            $this->logger->warning("Validation failed: {$field_name} is required but empty");
            return false;
        }
        
        if (empty($input)) {
            return '';
        }
        
        // Sanitize and validate
        $sanitized = sanitize_email($input);
        
        if (!is_email($sanitized)) {
            $this->logger->warning("Validation failed: {$field_name} is not a valid email");
            return false;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate and sanitize URL
     *
     * @param mixed  $input Input to validate.
     * @param string $field_name Field name for error messages.
     * @param bool   $required Whether field is required.
     * @return string|false Sanitized URL or false on failure.
     */
    public function validate_url($input, $field_name = 'url', $required = false) {
        if ($required && empty($input)) {
            $this->logger->warning("Validation failed: {$field_name} is required but empty");
            return false;
        }
        
        if (empty($input)) {
            return '';
        }
        
        // Sanitize
        $sanitized = esc_url_raw($input);
        
        // Validate
        if (!filter_var($sanitized, FILTER_VALIDATE_URL)) {
            $this->logger->warning("Validation failed: {$field_name} is not a valid URL");
            return false;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate and sanitize integer
     *
     * @param mixed  $input Input to validate.
     * @param string $field_name Field name for error messages.
     * @param bool   $required Whether field is required.
     * @param int    $min Minimum value allowed.
     * @param int    $max Maximum value allowed.
     * @return int|false Sanitized integer or false on failure.
     */
    public function validate_int($input, $field_name = 'integer', $required = false, $min = null, $max = null) {
        if ($required && ($input === '' || $input === null)) {
            $this->logger->warning("Validation failed: {$field_name} is required but empty");
            return false;
        }
        
        if ($input === '' || $input === null) {
            return 0;
        }
        
        // Sanitize
        $sanitized = intval($input);
        
        // Check min/max
        if ($min !== null && $sanitized < $min) {
            $this->logger->warning("Validation failed: {$field_name} is below minimum value of {$min}");
            return false;
        }
        
        if ($max !== null && $sanitized > $max) {
            $this->logger->warning("Validation failed: {$field_name} exceeds maximum value of {$max}");
            return false;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate API key format
     *
     * @param mixed  $input Input to validate.
     * @param string $field_name Field name for error messages.
     * @param bool   $required Whether field is required.
     * @return string|false Sanitized API key or false on failure.
     */
    public function validate_api_key($input, $field_name = 'api_key', $required = false) {
        if ($required && empty($input)) {
            $this->logger->warning("Validation failed: {$field_name} is required but empty");
            return false;
        }
        
        if (empty($input)) {
            return '';
        }
        
        // Sanitize
        $sanitized = trim(sanitize_text_field($input));
        
        // Validate format (alphanumeric, hyphens, underscores, dots)
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $sanitized)) {
            $this->logger->warning("Validation failed: {$field_name} contains invalid characters");
            return false;
        }
        
        // Check minimum length
        if (strlen($sanitized) < 10) {
            $this->logger->warning("Validation failed: {$field_name} is too short");
            return false;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate AI model selection
     *
     * @param mixed  $input Input to validate.
     * @param string $field_name Field name for error messages.
     * @return string|false Valid model identifier or false on failure.
     */
    public function validate_ai_model($input, $field_name = 'ai_model') {
        $sanitized = sanitize_text_field($input);
        
        if (!array_key_exists($sanitized, Config::SUPPORTED_MODELS)) {
            $this->logger->warning("Validation failed: {$field_name} '{$sanitized}' is not a supported model");
            return false;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate word count
     *
     * @param mixed  $input Input to validate.
     * @param string $field_name Field name for error messages.
     * @return int|false Valid word count or false on failure.
     */
    public function validate_word_count($input, $field_name = 'word_count') {
        $sanitized = intval($input);
        
        if (!Config::is_valid_word_count($sanitized)) {
            $this->logger->warning("Validation failed: {$field_name} '{$sanitized}' is not a valid option");
            return false;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate content type
     *
     * @param mixed  $input Input to validate.
     * @param string $field_name Field name for error messages.
     * @return string|false Valid content type or false on failure.
     */
    public function validate_content_type($input, $field_name = 'content_type') {
        $sanitized = sanitize_text_field($input);
        
        if (!Config::is_valid_content_type($sanitized)) {
            $this->logger->warning("Validation failed: {$field_name} '{$sanitized}' is not a valid content type");
            return false;
        }
        
        return $sanitized;
    }
    
    /**
     * Validate post status
     *
     * @param mixed  $input Input to validate.
     * @param string $field_name Field name for error messages.
     * @return string|false Valid post status or false on failure.
     */
    public function validate_post_status($input, $field_name = 'post_status') {
        $sanitized = sanitize_text_field($input);
        
        if (!Config::is_valid_post_status($sanitized)) {
            $this->logger->warning("Validation failed: {$field_name} '{$sanitized}' is not a valid post status");
            return false;
        }
        
        return $sanitized;
    }
    
    /**
     * Verify nonce
     *
     * @param string $nonce Nonce value.
     * @param string $action Nonce action.
     * @return bool True if valid.
     */
    public function verify_nonce($nonce, $action) {
        $valid = wp_verify_nonce($nonce, $action);
        
        if (!$valid) {
            $this->logger->warning("Nonce verification failed for action: {$action}");
        }
        
        return (bool) $valid;
    }
    
    /**
     * Check user capability
     *
     * @param string $capability Capability to check.
     * @return bool True if user has capability.
     */
    public function check_capability($capability = null) {
        if ($capability === null) {
            $capability = Config::get_required_capability();
        }
        
        $has_cap = current_user_can($capability);
        
        if (!$has_cap) {
            $user = wp_get_current_user();
            $this->logger->warning("User {$user->user_login} (ID: {$user->ID}) lacks required capability: {$capability}");
        }
        
        return $has_cap;
    }
    
    /**
     * Sanitize array of values
     *
     * @param array  $input Array to sanitize.
     * @param string $type Type of sanitization to apply.
     * @return array Sanitized array.
     */
    public function sanitize_array(array $input, $type = 'text') {
        $sanitized = array();
        
        foreach ($input as $key => $value) {
            $clean_key = sanitize_key($key);
            
            if (is_array($value)) {
                $sanitized[$clean_key] = $this->sanitize_array($value, $type);
            } else {
                switch ($type) {
                    case 'text':
                        $sanitized[$clean_key] = sanitize_text_field($value);
                        break;
                    case 'textarea':
                        $sanitized[$clean_key] = sanitize_textarea_field($value);
                        break;
                    case 'email':
                        $sanitized[$clean_key] = sanitize_email($value);
                        break;
                    case 'url':
                        $sanitized[$clean_key] = esc_url_raw($value);
                        break;
                    case 'int':
                        $sanitized[$clean_key] = intval($value);
                        break;
                    default:
                        $sanitized[$clean_key] = sanitize_text_field($value);
                }
            }
        }
        
        return $sanitized;
    }
}
