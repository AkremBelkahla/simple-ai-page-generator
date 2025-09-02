<?php
/**
 * Fonctionnalités de sécurité pour Simple AI Page Generator
 */

// Sécurité : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de sécurité pour le plugin
 */
class SAPG_Security {
    /**
     * Constructeur
     */
    public function __construct() {
        // Ajouter les hooks de sécurité
        add_action('init', [$this, 'init_security']);
        add_filter('sapg_sanitize_api_key', [$this, 'sanitize_api_key']);
        add_filter('sapg_validate_request', [$this, 'validate_request'], 10, 2);
    }
    
    /**
     * Initialiser les fonctionnalités de sécurité
     */
    public function init_security() {
        // Vérifier les permissions pour les actions admin
        add_action('admin_init', [$this, 'check_admin_permissions']);
        
        // Protéger contre les attaques CSRF
        add_action('admin_init', [$this, 'add_nonce_fields']);
    }
    
    /**
     * Vérifier les permissions admin
     */
    public function check_admin_permissions() {
        // Vérifier si on est sur une page du plugin
        $screen = get_current_screen();
        if (!$screen) return;
        
        if (strpos($screen->id, 'sapg') !== false) {
            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'ai-content-gen'));
            }
        }
    }
    
    /**
     * Ajouter des champs nonce aux formulaires
     */
    public function add_nonce_fields() {
        // Ajouté automatiquement dans les formulaires via wp_nonce_field()
    }
    
    /**
     * Sanitize API key
     */
    public function sanitize_api_key($key) {
        // Nettoyer la clé API
        $key = trim(sanitize_text_field($key));
        
        // Vérifier le format de base (pas de validation spécifique par API)
        if (!empty($key) && !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $key)) {
            add_settings_error(
                'sapg_options',
                'invalid_api_key',
                __('La clé API contient des caractères non autorisés.', 'ai-content-gen')
            );
            return '';
        }
        
        return $key;
    }
    
    /**
     * Valider une requête
     */
    public function validate_request($valid, $action) {
        // Vérifier le nonce
        if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], $action)) {
            return false;
        }
        
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        return $valid;
    }
    
    /**
     * Protéger les clés API dans la base de données
     */
    public static function encrypt_api_key($key) {
        if (empty($key)) return '';
        
        // Utiliser la fonction de hachage de WordPress pour créer une clé de chiffrement unique par site
        $salt = wp_salt('auth');
        $encrypted = '';
        
        // Méthode simple de chiffrement (pour une sécurité plus robuste, utiliser openssl)
        if (function_exists('openssl_encrypt') && defined('SECURE_AUTH_KEY')) {
            $method = 'AES-256-CBC';
            $iv = substr(SECURE_AUTH_KEY, 0, 16);
            $encrypted = openssl_encrypt($key, $method, $salt, 0, $iv);
        } else {
            // Méthode de secours (moins sécurisée)
            $encrypted = base64_encode($key);
        }
        
        return $encrypted;
    }
    
    /**
     * Déchiffrer une clé API
     */
    public static function decrypt_api_key($encrypted) {
        if (empty($encrypted)) return '';
        
        $salt = wp_salt('auth');
        $decrypted = '';
        
        if (function_exists('openssl_decrypt') && defined('SECURE_AUTH_KEY')) {
            $method = 'AES-256-CBC';
            $iv = substr(SECURE_AUTH_KEY, 0, 16);
            $decrypted = openssl_decrypt($encrypted, $method, $salt, 0, $iv);
        } else {
            // Méthode de secours
            $decrypted = base64_decode($encrypted);
        }
        
        return $decrypted;
    }
    
    /**
     * Journaliser les activités importantes
     */
    public static function log_activity($action, $details = '') {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $user = wp_get_current_user();
        $user_id = $user->ID;
        $user_login = $user->user_login;
        
        $log_message = sprintf(
            '[%s] User %s (ID: %d) performed action: %s %s',
            current_time('mysql'),
            $user_login,
            $user_id,
            $action,
            !empty($details) ? "- Details: $details" : ''
        );
        
        error_log($log_message);
    }
}

// Initialiser la classe de sécurité
$sapg_security = new SAPG_Security();

/**
 * Fonctions utilitaires de sécurité
 */

/**
 * Sanitize les données d'entrée
 */
function sapg_sanitize_input($input, $type = 'text') {
    switch ($type) {
        case 'text':
            return sanitize_text_field($input);
        case 'textarea':
            return sanitize_textarea_field($input);
        case 'email':
            return sanitize_email($input);
        case 'url':
            return esc_url_raw($input);
        case 'int':
            return intval($input);
        case 'float':
            return floatval($input);
        case 'api_key':
            return apply_filters('sapg_sanitize_api_key', $input);
        default:
            return sanitize_text_field($input);
    }
}

/**
 * Valider une requête avec nonce
 */
function sapg_validate_request($action) {
    return apply_filters('sapg_validate_request', true, $action);
}

/**
 * Protéger contre les injections SQL
 */
function sapg_prepare_query($query, $args) {
    global $wpdb;
    return $wpdb->prepare($query, $args);
}
