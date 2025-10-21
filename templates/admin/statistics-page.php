<?php
/**
 * Statistics Page Template
 *
 * @package Simple_AI_Page_Generator
 * @subpackage Templates
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator;

use Simple_AI_Page_Generator\Generator\Content_Generator;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin = Plugin::get_instance();
$generator = $plugin->get_content_generator();
$stats = $generator ? $generator->get_statistics() : array();
?>

<div class="wrap sapg-statistics-page">
    <h1><?php echo esc_html__('AI Content Generator - Statistics', Config::TEXT_DOMAIN); ?></h1>
    
    <div class="sapg-stats-grid">
        <div class="sapg-stat-card">
            <div class="sapg-stat-icon">
                <span class="dashicons dashicons-edit-large"></span>
            </div>
            <div class="sapg-stat-content">
                <h3><?php echo esc_html__('Total Generated', Config::TEXT_DOMAIN); ?></h3>
                <p class="sapg-stat-number"><?php echo esc_html(number_format_i18n($stats['total_generated'] ?? 0)); ?></p>
            </div>
        </div>
        
        <div class="sapg-stat-card">
            <div class="sapg-stat-icon">
                <span class="dashicons dashicons-admin-post"></span>
            </div>
            <div class="sapg-stat-content">
                <h3><?php echo esc_html__('Posts', Config::TEXT_DOMAIN); ?></h3>
                <p class="sapg-stat-number"><?php echo esc_html(number_format_i18n($stats['by_type']['post'] ?? 0)); ?></p>
            </div>
        </div>
        
        <div class="sapg-stat-card">
            <div class="sapg-stat-icon">
                <span class="dashicons dashicons-admin-page"></span>
            </div>
            <div class="sapg-stat-content">
                <h3><?php echo esc_html__('Pages', Config::TEXT_DOMAIN); ?></h3>
                <p class="sapg-stat-number"><?php echo esc_html(number_format_i18n($stats['by_type']['page'] ?? 0)); ?></p>
            </div>
        </div>
    </div>
    
    <div class="sapg-stats-section">
        <h2><?php echo esc_html__('Usage by AI Model', Config::TEXT_DOMAIN); ?></h2>
        
        <?php if (!empty($stats['by_model'])): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('AI Model', Config::TEXT_DOMAIN); ?></th>
                        <th><?php echo esc_html__('Content Generated', Config::TEXT_DOMAIN); ?></th>
                        <th><?php echo esc_html__('Percentage', Config::TEXT_DOMAIN); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['by_model'] as $model => $count): ?>
                        <?php 
                        $percentage = $stats['total_generated'] > 0 
                            ? round(($count / $stats['total_generated']) * 100, 1) 
                            : 0;
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html(ucfirst($model)); ?></strong></td>
                            <td><?php echo esc_html(number_format_i18n($count)); ?></td>
                            <td>
                                <div class="sapg-progress-bar">
                                    <div class="sapg-progress-fill" style="width: <?php echo esc_attr($percentage); ?>%"></div>
                                </div>
                                <?php echo esc_html($percentage); ?>%
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php echo esc_html__('No statistics available yet. Start generating content!', Config::TEXT_DOMAIN); ?></p>
        <?php endif; ?>
    </div>
</div>
