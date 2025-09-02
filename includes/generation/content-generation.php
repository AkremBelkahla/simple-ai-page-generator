<?php
/**
 * Fonctionnalités de génération de contenu via API IA
 */

// Sécurité : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale de génération de contenu
 */
class SAPG_Content_Generator {
    private $options;
    private $api_clients = [];
    private $transient_prefix = 'sapg_api_cache_';
    private $cache_expiration = 3600; // 1 heure
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->options = get_option('sapg_options');
        add_action('admin_init', [$this, 'process_generation_form']);
    }
    
    /**
     * Traitement du formulaire de génération
     */
    public function process_generation_form() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !current_user_can('manage_options')) {
            return;
        }
        
        if (!isset($_POST['sapg_generate_content']) || !wp_verify_nonce($_POST['sapg_nonce'], 'sapg_generate_content')) {
            return;
        }
        
        // Récupérer les paramètres
        $title = sanitize_text_field($_POST['sapg_title'] ?? '');
        $model = sanitize_text_field($_POST['sapg_model'] ?? $this->options['ai_model']);
        $content_type = $this->options['content_type'];
        $word_count = intval($this->options['word_count']);
        $status = sanitize_text_field($_POST['sapg_status'] ?? 'draft');
        
        // Vérifier que le modèle est valide
        if (!$this->is_api_configured($model)) {
            wp_die('Le modèle sélectionné n\'est pas configuré correctement.');
            return;
        }
        
        // Générer le contenu
        $prompt = $this->build_prompt($title, $content_type, $word_count);
        $generated_content = $this->generate_content($model, $prompt, $word_count);
        
        if (is_wp_error($generated_content)) {
            wp_die('Erreur lors de la génération du contenu: ' . $generated_content->get_error_message());
            return;
        }
        
        // Créer le post
        $post_data = array(
            'post_title'    => !empty($title) ? $title : 'Contenu généré - ' . date('Y-m-d H:i:s'),
            'post_content'  => $generated_content,
            'post_status'   => in_array($status, ['publish', 'draft']) ? $status : 'draft',
            'post_type'     => $content_type
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id) {
            // Ajouter des métadonnées sur la génération
            update_post_meta($post_id, '_sapg_generated', true);
            update_post_meta($post_id, '_sapg_model', $model);
            update_post_meta($post_id, '_sapg_word_count', $word_count);
            update_post_meta($post_id, '_sapg_generated_date', current_time('mysql'));
            
            // Rediriger vers l'édition du post
            wp_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));
            exit;
        } else {
            wp_die('Erreur lors de la création du contenu.');
        }
    }
    
    /**
     * Vérifier si l'API est configurée
     */
    private function is_api_configured($model) {
        $key_name = $model . '_key';
        return !empty($this->options[$key_name]);
    }
    
    /**
     * Construire le prompt pour la génération
     */
    private function build_prompt($title, $content_type, $word_count) {
        $type_label = $content_type === 'post' ? 'article' : 'page';
        $prompt = "Génère un {$type_label} WordPress de {$word_count} mots";
        
        if (!empty($title)) {
            $prompt .= " sur le sujet: {$title}";
        }
        
        $prompt .= ". Le contenu doit être bien structuré avec des titres, sous-titres et paragraphes. ";
        $prompt .= "Utilise le format HTML pour le formatage (h2, h3, p, ul, li, etc.).";
        
        return $prompt;
    }
    
    /**
     * Générer le contenu via l'API appropriée
     */
    public function generate_content($model, $prompt, $word_count) {
        // Vérifier si une réponse est en cache
        $cache_key = $this->transient_prefix . md5($model . $prompt . $word_count);
        $cached_response = get_transient($cache_key);
        
        if ($cached_response !== false) {
            return $cached_response;
        }
        
        // Générer le contenu via l'API appropriée
        $result = null;
        
        switch($model) {
            case 'openai':
                $result = $this->generate_openai_content($prompt, $word_count);
                break;
            case 'deepseek':
                $result = $this->generate_deepseek_content($prompt, $word_count);
                break;
            case 'gemini':
                $result = $this->generate_gemini_content($prompt, $word_count);
                break;
            case 'anthropic':
                $result = $this->generate_claude_content($prompt, $word_count);
                break;
            default:
                return new WP_Error('invalid_model', 'Modèle IA non supporté');
        }
        
        // Mettre en cache la réponse si elle est valide
        if (!is_wp_error($result)) {
            set_transient($cache_key, $result, $this->cache_expiration);
        }
        
        return $result;
    }
    
    /**
     * Générer du contenu via OpenAI
     */
    private function generate_openai_content($prompt, $word_count) {
        $api_key = $this->options['openai_key'];
        
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', 'Clé API OpenAI manquante');
        }
        
        $max_tokens = $word_count * 1.5; // Estimation du nombre de tokens nécessaires
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array('role' => 'system', 'content' => 'Tu es un expert en rédaction web qui génère du contenu WordPress de haute qualité.'),
                    array('role' => 'user', 'content' => $prompt)
                ),
                'max_tokens' => $max_tokens,
                'temperature' => 0.7
            )),
            'timeout' => 60
        );
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('api_error', $body['error']['message']);
        }
        
        return $body['choices'][0]['message']['content'] ?? '';
    }
    
    /**
     * Générer du contenu via DeepSeek
     */
    private function generate_deepseek_content($prompt, $word_count) {
        $api_key = $this->options['deepseek_key'];
        
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', 'Clé API DeepSeek manquante');
        }
        
        $max_tokens = $word_count * 1.5; // Estimation du nombre de tokens nécessaires
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'model' => 'deepseek-chat',
                'messages' => array(
                    array('role' => 'system', 'content' => 'Tu es un expert en rédaction web qui génère du contenu WordPress de haute qualité.'),
                    array('role' => 'user', 'content' => $prompt)
                ),
                'max_tokens' => $max_tokens,
                'temperature' => 0.7
            )),
            'timeout' => 60
        );
        
        $response = wp_remote_post('https://api.deepseek.com/v1/chat/completions', $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('api_error', $body['error']['message']);
        }
        
        return $body['choices'][0]['message']['content'] ?? '';
    }
    
    /**
     * Générer du contenu via Gemini
     */
    private function generate_gemini_content($prompt, $word_count) {
        $api_key = $this->options['gemini_key'];
        
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', 'Clé API Gemini manquante');
        }
        
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array('text' => $prompt)
                        )
                    )
                ),
                'generationConfig' => array(
                    'temperature' => 0.7,
                    'maxOutputTokens' => $word_count * 1.5,
                )
            )),
            'timeout' => 60
        );
        
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key;
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('api_error', $body['error']['message']);
        }
        
        return $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
    
    /**
     * Générer du contenu via Claude (Anthropic)
     */
    private function generate_claude_content($prompt, $word_count) {
        $api_key = $this->options['anthropic_key'];
        
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', 'Clé API Claude manquante');
        }
        
        $max_tokens = $word_count * 1.5; // Estimation du nombre de tokens nécessaires
        
        $args = array(
            'headers' => array(
                'x-api-key' => $api_key,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ),
            'body' => json_encode(array(
                'model' => 'claude-2',
                'prompt' => "\n\nHuman: {$prompt}\n\nAssistant:",
                'max_tokens_to_sample' => $max_tokens,
                'temperature' => 0.7
            )),
            'timeout' => 60
        );
        
        $response = wp_remote_post('https://api.anthropic.com/v1/complete', $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('api_error', $body['error']['message']);
        }
        
        return $body['completion'] ?? '';
    }
}

// Initialiser la classe
$sapg_content_generator = new SAPG_Content_Generator();
