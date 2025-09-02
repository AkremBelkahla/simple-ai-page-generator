<?php
// Sécurité : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Ajouter la page admin
function sapg_add_admin_page() {
    add_menu_page(
        'Générateur de contenu', 
        'Générateur IA', 
        'manage_options', 
        'sapg-content-generator', 
        'sapg_admin_page_html',
        'dashicons-edit',
        21 // Position juste après le menu Pages (20)
    );
    
    // Enregistrer les nouveaux paramètres
    add_action('admin_init', function() {
        register_setting('sapg_options_group', 'sapg_options');
        
        add_settings_section(
            'sapg_page_section',
            'Paramètres de la page',
            null,
            'sapg-content-generator'
        );
        
        add_settings_field(
            'sapg_page_title',
            'Titre de la page',
            'sapg_page_title_input',
            'sapg-content-generator',
            'sapg_page_section'
        );
        
        add_settings_field(
            'sapg_page_status',
            'Statut de la page',
            'sapg_page_status_input',
            'sapg-content-generator',
            'sapg_page_section'
        );
        
    });
}
add_action('admin_menu', 'sapg_add_admin_page');

// Enregistrer les styles admin
function sapg_register_admin_styles() {
    wp_register_style(
        'sapg-admin-style', 
        plugins_url('assets/css/admin-style.css', dirname(__FILE__)), 
        array(), 
        '1.0.0'
    );
}
add_action('admin_init', 'sapg_register_admin_styles');

// Charger les styles admin uniquement sur la page du plugin
function sapg_load_admin_styles($hook) {
    if ($hook === 'toplevel_page_sapg-content-generator') {
        wp_enqueue_style('sapg-admin-style');
    }
}
add_action('admin_enqueue_scripts', 'sapg_load_admin_styles');

// HTML de la page admin
function sapg_admin_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap sapg-admin-page">
        <h1>Générateur de contenu IA</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('sapg_options_group');
            do_settings_sections('sapg-content-generator');
            
            
            submit_button('Générer le contenu');
            ?>
        </form>
    </div>
    <?php
}

// Champ pour le titre de la page
function sapg_page_title_input() {
    $options = get_option('sapg_options');
    echo '<input type="text" name="sapg_options[page_title]" value="'.esc_attr($options['page_title'] ?? '').'" class="regular-text">';
    echo '<p class="description">Entrez le titre de la page à créer</p>';
}

// Champ pour le statut de la page
function sapg_page_status_input() {
    $options = get_option('sapg_options');
    ?>
    <select name="sapg_options[page_status]" class="regular-text">
        <option value="publish" <?php selected($options['page_status'] ?? 'publish', 'publish'); ?>>Publié</option>
        <option value="draft" <?php selected($options['page_status'] ?? 'publish', 'draft'); ?>>Brouillon</option>
    </select>
    <p class="description">Choisissez si la page doit être publiée ou enregistrée comme brouillon</p>
    <?php
}

// Ajouter les nouveaux champs à la validation existante
add_filter('sapg_options_validate', function($newinput, $input) {
    // Validation du titre
    $newinput['page_title'] = sanitize_text_field($input['page_title'] ?? '');
    
    // Validation du statut
    $newinput['page_status'] = in_array($input['page_status'] ?? 'publish', ['publish', 'draft']) 
        ? $input['page_status'] 
        : 'publish';
    
    
    return $newinput;
}, 10, 2);
