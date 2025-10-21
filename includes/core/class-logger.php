<?php
/**
 * Logger class for Simple AI Page Generator
 *
 * Provides comprehensive logging functionality following PSR-3 standards.
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
 * Class Logger
 *
 * Handles all logging operations for the plugin.
 *
 * @since 2.0.0
 */
class Logger {
    
    /**
     * Log file path
     *
     * @var string
     */
    private $log_file;
    
    /**
     * Current log level
     *
     * @var string
     */
    private $log_level;
    
    /**
     * Whether logging is enabled
     *
     * @var bool
     */
    private $enabled;
    
    /**
     * Constructor
     */
    public function __construct() {
        $options = get_option(Config::OPTION_NAME, Config::get_default_options());
        
        $this->enabled = isset($options['enable_logging']) ? (bool) $options['enable_logging'] : true;
        $this->log_level = isset($options['log_level']) ? $options['log_level'] : 'info';
        
        // Set log file path
        $upload_dir = wp_upload_dir();
        $log_dir = trailingslashit($upload_dir['basedir']) . 'sapg-logs';
        
        // Create log directory if it doesn't exist
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
            
            // Add .htaccess to protect logs
            $htaccess_file = $log_dir . '/.htaccess';
            if (!file_exists($htaccess_file)) {
                file_put_contents($htaccess_file, "Deny from all\n");
            }
            
            // Add index.php to prevent directory listing
            $index_file = $log_dir . '/index.php';
            if (!file_exists($index_file)) {
                file_put_contents($index_file, "<?php\n// Silence is golden.\n");
            }
        }
        
        $this->log_file = $log_dir . '/sapg-' . date('Y-m-d') . '.log';
    }
    
    /**
     * Log an emergency message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function emergency($message, array $context = array()) {
        $this->log('emergency', $message, $context);
    }
    
    /**
     * Log an alert message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function alert($message, array $context = array()) {
        $this->log('alert', $message, $context);
    }
    
    /**
     * Log a critical message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function critical($message, array $context = array()) {
        $this->log('critical', $message, $context);
    }
    
    /**
     * Log an error message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function error($message, array $context = array()) {
        $this->log('error', $message, $context);
    }
    
    /**
     * Log a warning message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function warning($message, array $context = array()) {
        $this->log('warning', $message, $context);
    }
    
    /**
     * Log a notice message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function notice($message, array $context = array()) {
        $this->log('notice', $message, $context);
    }
    
    /**
     * Log an info message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function info($message, array $context = array()) {
        $this->log('info', $message, $context);
    }
    
    /**
     * Log a debug message
     *
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function debug($message, array $context = array()) {
        $this->log('debug', $message, $context);
    }
    
    /**
     * Main logging method
     *
     * @param string $level   Log level.
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return void
     */
    public function log($level, $message, array $context = array()) {
        // Check if logging is enabled
        if (!$this->enabled) {
            return;
        }
        
        // Check if this log level should be recorded
        if (!$this->should_log($level)) {
            return;
        }
        
        // Format the log entry
        $log_entry = $this->format_log_entry($level, $message, $context);
        
        // Write to file
        $this->write_to_file($log_entry);
        
        // Also log to error_log if WP_DEBUG is enabled
        if (Config::is_debug_enabled()) {
            error_log('[SAPG] ' . $log_entry);
        }
        
        // Fire action for external logging systems
        do_action('sapg_log', $level, $message, $context);
    }
    
    /**
     * Check if a log level should be recorded
     *
     * @param string $level Log level to check.
     * @return bool True if should log.
     */
    private function should_log($level) {
        $current_level_value = Config::LOG_LEVELS[$this->log_level] ?? 6;
        $message_level_value = Config::LOG_LEVELS[$level] ?? 7;
        
        return $message_level_value <= $current_level_value;
    }
    
    /**
     * Format a log entry
     *
     * @param string $level   Log level.
     * @param string $message Log message.
     * @param array  $context Additional context data.
     * @return string Formatted log entry.
     */
    private function format_log_entry($level, $message, array $context = array()) {
        $timestamp = current_time('Y-m-d H:i:s');
        $level_upper = strtoupper($level);
        
        // Get user info
        $user = wp_get_current_user();
        $user_info = $user->ID ? sprintf('[User:%d:%s]', $user->ID, $user->user_login) : '[User:Guest]';
        
        // Format context
        $context_str = '';
        if (!empty($context)) {
            $context_str = ' | Context: ' . wp_json_encode($context);
        }
        
        return sprintf(
            "[%s] [%s] %s %s%s\n",
            $timestamp,
            $level_upper,
            $user_info,
            $message,
            $context_str
        );
    }
    
    /**
     * Write log entry to file
     *
     * @param string $log_entry Formatted log entry.
     * @return void
     */
    private function write_to_file($log_entry) {
        // Use WordPress filesystem API
        global $wp_filesystem;
        
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
        
        // Append to log file
        if ($wp_filesystem) {
            $existing_content = '';
            if ($wp_filesystem->exists($this->log_file)) {
                $existing_content = $wp_filesystem->get_contents($this->log_file);
            }
            $wp_filesystem->put_contents($this->log_file, $existing_content . $log_entry, FS_CHMOD_FILE);
        } else {
            // Fallback to standard PHP file operations
            file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Get log file path
     *
     * @return string Log file path.
     */
    public function get_log_file() {
        return $this->log_file;
    }
    
    /**
     * Clear old log files
     *
     * @param int $days Number of days to keep logs.
     * @return void
     */
    public function clear_old_logs($days = 30) {
        $upload_dir = wp_upload_dir();
        $log_dir = trailingslashit($upload_dir['basedir']) . 'sapg-logs';
        
        if (!is_dir($log_dir)) {
            return;
        }
        
        $files = glob($log_dir . '/sapg-*.log');
        $cutoff_time = time() - ($days * DAY_IN_SECONDS);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_time) {
                wp_delete_file($file);
            }
        }
    }
}
