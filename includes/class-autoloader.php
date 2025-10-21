<?php
/**
 * Autoloader for Simple AI Page Generator
 *
 * Automatically loads classes following PSR-4 naming convention.
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
 * Class Autoloader
 *
 * Handles automatic loading of plugin classes.
 *
 * @since 2.0.0
 */
class Autoloader {
    
    /**
     * Namespace prefix
     *
     * @var string
     */
    private $namespace_prefix = 'Simple_AI_Page_Generator\\';
    
    /**
     * Base directory for the namespace prefix
     *
     * @var string
     */
    private $base_dir;
    
    /**
     * Constructor
     *
     * @param string $base_dir Base directory for class files.
     */
    public function __construct($base_dir) {
        $this->base_dir = trailingslashit($base_dir);
    }
    
    /**
     * Register the autoloader
     *
     * @return void
     */
    public function register() {
        spl_autoload_register(array($this, 'load_class'));
    }
    
    /**
     * Load a class file
     *
     * @param string $class The fully-qualified class name.
     * @return void
     */
    private function load_class($class) {
        // Check if the class uses the namespace prefix
        $len = strlen($this->namespace_prefix);
        if (strncmp($this->namespace_prefix, $class, $len) !== 0) {
            return;
        }
        
        // Get the relative class name
        $relative_class = substr($class, $len);
        
        // Convert namespace separators to directory separators
        $relative_class = str_replace('\\', DIRECTORY_SEPARATOR, $relative_class);
        
        // Convert class name to file name (Class_Name -> class-class-name.php)
        $file_parts = explode(DIRECTORY_SEPARATOR, $relative_class);
        $file_name = array_pop($file_parts);
        $file_name = 'class-' . strtolower(str_replace('_', '-', $file_name)) . '.php';
        
        // Reconstruct the path
        $path_parts = array_map(function($part) {
            return strtolower(str_replace('_', '-', $part));
        }, $file_parts);
        
        $file = $this->base_dir . implode(DIRECTORY_SEPARATOR, $path_parts);
        if (!empty($path_parts)) {
            $file .= DIRECTORY_SEPARATOR;
        }
        $file .= $file_name;
        
        // Load the file if it exists
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
