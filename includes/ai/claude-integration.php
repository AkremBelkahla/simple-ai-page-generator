<?php
// Sécurité : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

class SAPG_Claude_Integration {
    private $api_key;
    private $api_url = 'https://api.anthropic.com/v1/complete';

    public function __construct() {
        $options = get_option('sapg_options');
        $this->api_key = $options['claude_api_key'] ?? '';
    }

    public function generate_content($prompt, $max_tokens = 200) {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', __('Clé API Claude manquante', 'sapg'));
        }

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ),
            'body' => json_encode(array(
                'prompt' => $prompt,
                'max_tokens_to_sample' => $max_tokens,
                'temperature' => 0.7,
                'top_p' => 1,
                'stop_sequences' => ["\n\nHuman:"],
            )),
            'timeout' => 30,
        );

        $response = wp_remote_post($this->api_url, $args);

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
