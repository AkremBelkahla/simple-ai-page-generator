<?php
/**
 * Settings Page Template
 *
 * @package Simple_AI_Page_Generator
 * @subpackage Templates
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap sapg-settings-page">
    <h1><?php echo esc_html__('AI Content Generator - Settings', Config::TEXT_DOMAIN); ?></h1>
    
    <?php settings_errors(); ?>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('sapg_options_group');
        do_settings_sections('sapg-settings');
        submit_button();
        ?>
    </form>
</div>
