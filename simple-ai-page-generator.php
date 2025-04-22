<?php
/**
 * Plugin Name: Simple AI Page Generator
 * Description: Un plugin avancé pour générer du contenu via différentes API d'IA (OpenAI, DeepSeek, Gemini)
 * Version: 1.1.0
 * Author: Votre Nom
 * Author URI: https://votresite.com
 * Text Domain: ai-content-gen
 */

// Sécurité : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Charger les dépendances
require_once plugin_dir_path(__FILE__) . 'includes/helpers/helpers.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/admin-pages.php';
require_once plugin_dir_path(__FILE__) . 'includes/api/api-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/generation/content-generation.php';
require_once plugin_dir_path(__FILE__) . 'includes/ai/claude-integration.php';

// Initialisation du plugin
function sapg_init() {
    // Charger les dépendances
    sapg_load_dependencies();
    
    // Enregistrer les hooks
    sapg_register_hooks();
    
    // Charger les traductions
    load_plugin_textdomain('ai-content-gen', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

// Activation du plugin
function sapg_activate() {
    // Options par défaut
    $default_options = array(
        'ai_model' => 'openai',
        'content_type' => 'post',
        'word_count' => 500,
        'openai_key' => '',
        'deepseek_key' => '',
        'gemini_key' => '',
        'huggingface_key' => '',
        'deepai_key' => '',
        'anthropic_key' => ''
    );
    
    add_option('sapg_options', $default_options);
}

// Désactivation du plugin
function sapg_deactivate() {
    // Nettoyage des options
    delete_option('sapg_options');
}

// Hooks
register_activation_hook(__FILE__, 'sapg_activate');
register_deactivation_hook(__FILE__, 'sapg_deactivate');
add_action('plugins_loaded', 'sapg_init');

// Les fonctions admin ont été déplacées dans includes/admin-pages.php

// Initialiser les paramètres
function sapg_settings_init() {
    // Section principale
    register_setting('sapg_options_group', 'sapg_options', 'sapg_options_validate');
    
    add_settings_section(
        'sapg_main_section',
        'Paramètres de génération',
        'sapg_section_text',
        'sapg-content-generator'
    );

    add_settings_field(
        'sapg_ai_model',
        'Modèle IA',
        'sapg_ai_model_input',
        'sapg-content-generator',
        'sapg_main_section'
    );

    add_settings_field(
        'sapg_content_type', 
        'Type de contenu',
        'sapg_content_type_input',
        'sapg-content-generator',
        'sapg_main_section'
    );

    add_settings_field(
        'sapg_word_count',
        'Nombre de mots',
        'sapg_word_count_input',
        'sapg-content-generator',
        'sapg_main_section'
    );
}

// Validation des options
function sapg_options_validate($input) {
    $newinput = array();
    
    // Validation des autres champs
    $newinput['ai_model'] = trim($input['ai_model']);
    $newinput['content_type'] = trim($input['content_type']);
    
    // Validation du nombre de mots
    $allowed_values = [100, 300, 500, 1000];
    $newinput['word_count'] = in_array($input['word_count'], $allowed_values) 
        ? $input['word_count'] 
        : 500; // Valeur par défaut
    
    return $newinput;
}
add_action('admin_init', 'sapg_settings_init');
add_filter('sapg_options_validate', 'sapg_options_validate');

// Texte de la section
function sapg_section_text() {
    echo '<p>Configurez les paramètres pour générer votre contenu</p>';
}

// Champ modèle IA
function sapg_ai_model_input() {
    $options = get_option('sapg_options');
    $models = [
        'deepseek' => !empty($options['deepseek_key']),
        'openai' => !empty($options['openai_key']),
        'gemini' => !empty($options['gemini_key']),
        'huggingface' => !empty($options['huggingface_key']),
        'deepai' => !empty($options['deepai_key']),
        'anthropic' => !empty($options['anthropic_key'])
    ];
    
    echo "<select name='sapg_options[ai_model]'>";
    foreach ($models as $model => $enabled) {
        $selected = ($options['ai_model'] ?? '') === $model ? 'selected' : '';
        $disabled = !$enabled ? 'disabled' : '';
        echo "<option value='$model' $selected $disabled>" . ucfirst($model) . "</option>";
    }
    echo "</select>";
    if (!$models['deepseek'] && !$models['openai'] && !$models['gemini']) {
        echo '<p class="description">Aucune API configurée. Veuillez configurer au moins une API dans les paramètres.</p>';
    }
}

// Champ type de contenu
function sapg_content_type_input() {
    $options = get_option('sapg_options');
    echo "<select name='sapg_options[content_type]'>
            <option value='post'>Article</option>
            <option value='page'>Page</option>
          </select>";
}

// Champ nombre de mots
function sapg_word_count_input() {
    $options = get_option('sapg_options');
    $selected = $options['word_count'] ?? 500;
    
    echo "<select name='sapg_options[word_count]'>";
    foreach ([100, 300, 500, 1000] as $value) {
        $selected_attr = $selected == $value ? 'selected' : '';
        echo "<option value='$value' $selected_attr>$value mots</option>";
    }
    echo "</select>";
}

// Traitement du formulaire
function sapg_generate_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $options = get_option('sapg_options');
    ?>
    <div class="wrap">
        <h1>Générateur de contenu IA</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('sapg_generate_content'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="sapg_title">Titre de la page</label></th>
                    <td>
                        <input name="sapg_title" type="text" id="sapg_title" class="regular-text" 
                            placeholder="Entrez un titre pour votre contenu" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="sapg_status">Statut de la page</label></th>
                    <td>
                        <select name="sapg_status" id="sapg_status">
                            <option value="publish">Publié</option>
                            <option value="draft">Brouillon</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label>Modèle IA</label></th>
                    <td>
                        <?php sapg_ai_model_input(); ?>
                        <p class="description">Sélectionnez le modèle IA à utiliser</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Générer le contenu'); ?>
        </form>
    </div>
    <?php
}

function sapg_process_form() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can('manage_options')) {
        check_admin_referer('sapg_generate_content');
        
        $options = get_option('sapg_options');
        
        // Vérifier si le modèle sélectionné est activé
        $selected_model = $_POST['sapg_options']['ai_model'] ?? '';
        $api_keys = [
            'deepseek' => $options['deepseek_key'] ?? '',
            'openai' => $options['openai_key'] ?? '',
            'gemini' => $options['gemini_key'] ?? ''
        ];
        
        if (empty($api_keys[$selected_model])) {
            wp_die('Le modèle sélectionné n\'est pas configuré. Veuillez configurer l\'API correspondante.');
        }
        
        // Récupérer les données du formulaire
        $title = sanitize_text_field($_POST['sapg_title'] ?? '');
        $status = in_array($_POST['sapg_status'], ['publish', 'draft']) 
            ? $_POST['sapg_status'] 
            : 'draft';
        
        // Ici vous devrez implémenter l'appel à l'API IA de votre choix
        $generated_content = "Ceci est un exemple de contenu généré. Implémentez l'appel API ici.";
        
        // Créer le contenu
        $post_data = array(
            'post_title'    => !empty($title) ? $title : 'Contenu généré - ' . date('Y-m-d H:i:s'),
            'post_content'  => $generated_content,
            'post_status'   => $status,
            'post_type'     => $options['content_type']
        );
        
        wp_insert_post($post_data);
    }
}

add_action('admin_init', 'sapg_process_form');
