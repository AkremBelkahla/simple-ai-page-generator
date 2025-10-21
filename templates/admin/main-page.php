<?php
/**
 * Main Admin Page Template
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

<div class="wrap sapg-admin-page">
    <h1><?php echo esc_html__('AI Content Generator', Config::TEXT_DOMAIN); ?></h1>
    
    <?php if (!$has_api): ?>
        <div class="notice notice-warning">
            <p>
                <?php echo esc_html__('No API keys configured. Please configure at least one API key in the settings.', Config::TEXT_DOMAIN); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=sapg-settings')); ?>" class="button button-primary">
                    <?php echo esc_html__('Configure APIs', Config::TEXT_DOMAIN); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="sapg-generator-container">
        <div class="sapg-card">
            <h2><?php echo esc_html__('Generate New Content', Config::TEXT_DOMAIN); ?></h2>
            
            <form id="sapg-generator-form" method="post">
                <?php wp_nonce_field('sapg_generate_content', 'sapg_nonce'); ?>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="sapg_title">
                                    <?php echo esc_html__('Content Title', Config::TEXT_DOMAIN); ?>
                                </label>
                            </th>
                            <td>
                                <input 
                                    type="text" 
                                    id="sapg_title" 
                                    name="title" 
                                    class="regular-text" 
                                    placeholder="<?php echo esc_attr__('Enter a topic or leave empty for random content', Config::TEXT_DOMAIN); ?>"
                                />
                                <p class="description">
                                    <?php echo esc_html__('The main topic or title for your content.', Config::TEXT_DOMAIN); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="sapg_model">
                                    <?php echo esc_html__('AI Model', Config::TEXT_DOMAIN); ?>
                                </label>
                            </th>
                            <td>
                                <select id="sapg_model" name="model" class="regular-text" <?php echo !$has_api ? 'disabled' : ''; ?>>
                                    <?php foreach (Config::SUPPORTED_MODELS as $model_id => $model_config): ?>
                                        <?php 
                                        $has_key = !empty($options[$model_id . '_key']);
                                        $selected = ($options['ai_model'] ?? 'openai') === $model_id;
                                        ?>
                                        <option 
                                            value="<?php echo esc_attr($model_id); ?>" 
                                            <?php selected($selected); ?>
                                            <?php disabled(!$has_key); ?>
                                        >
                                            <?php echo esc_html($model_config['name']); ?>
                                            <?php if (!$has_key): ?>
                                                (<?php echo esc_html__('Not configured', Config::TEXT_DOMAIN); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php echo esc_html__('Select the AI model to use for generation.', Config::TEXT_DOMAIN); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="sapg_word_count">
                                    <?php echo esc_html__('Word Count', Config::TEXT_DOMAIN); ?>
                                </label>
                            </th>
                            <td>
                                <select id="sapg_word_count" name="word_count" class="regular-text">
                                    <?php foreach (Config::WORD_COUNT_OPTIONS as $count): ?>
                                        <option 
                                            value="<?php echo esc_attr($count); ?>"
                                            <?php selected($options['word_count'] ?? 500, $count); ?>
                                        >
                                            <?php echo esc_html(number_format_i18n($count)); ?> 
                                            <?php echo esc_html__('words', Config::TEXT_DOMAIN); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php echo esc_html__('Approximate number of words to generate.', Config::TEXT_DOMAIN); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="sapg_content_type">
                                    <?php echo esc_html__('Content Type', Config::TEXT_DOMAIN); ?>
                                </label>
                            </th>
                            <td>
                                <select id="sapg_content_type" name="content_type" class="regular-text">
                                    <option value="post" <?php selected($options['content_type'] ?? 'post', 'post'); ?>>
                                        <?php echo esc_html__('Post', Config::TEXT_DOMAIN); ?>
                                    </option>
                                    <option value="page" <?php selected($options['content_type'] ?? 'post', 'page'); ?>>
                                        <?php echo esc_html__('Page', Config::TEXT_DOMAIN); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php echo esc_html__('Type of content to create.', Config::TEXT_DOMAIN); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="sapg_post_status">
                                    <?php echo esc_html__('Post Status', Config::TEXT_DOMAIN); ?>
                                </label>
                            </th>
                            <td>
                                <select id="sapg_post_status" name="post_status" class="regular-text">
                                    <option value="draft" <?php selected($options['post_status'] ?? 'draft', 'draft'); ?>>
                                        <?php echo esc_html__('Draft', Config::TEXT_DOMAIN); ?>
                                    </option>
                                    <option value="publish" <?php selected($options['post_status'] ?? 'draft', 'publish'); ?>>
                                        <?php echo esc_html__('Published', Config::TEXT_DOMAIN); ?>
                                    </option>
                                    <option value="pending" <?php selected($options['post_status'] ?? 'draft', 'pending'); ?>>
                                        <?php echo esc_html__('Pending Review', Config::TEXT_DOMAIN); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php echo esc_html__('Status for the generated content.', Config::TEXT_DOMAIN); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <p class="submit">
                    <button 
                        type="submit" 
                        id="sapg-generate-btn" 
                        class="button button-primary button-large"
                        <?php echo !$has_api ? 'disabled' : ''; ?>
                    >
                        <span class="dashicons dashicons-edit"></span>
                        <?php echo esc_html__('Generate Content', Config::TEXT_DOMAIN); ?>
                    </button>
                    
                    <span id="sapg-loading" class="spinner" style="display: none;"></span>
                </p>
            </form>
            
            <div id="sapg-result" style="display: none;"></div>
        </div>
        
        <div class="sapg-sidebar">
            <div class="sapg-card">
                <h3><?php echo esc_html__('Quick Tips', Config::TEXT_DOMAIN); ?></h3>
                <ul>
                    <li><?php echo esc_html__('Be specific with your title for better results', Config::TEXT_DOMAIN); ?></li>
                    <li><?php echo esc_html__('Review and edit generated content before publishing', Config::TEXT_DOMAIN); ?></li>
                    <li><?php echo esc_html__('Different AI models may produce different styles', Config::TEXT_DOMAIN); ?></li>
                    <li><?php echo esc_html__('Longer content takes more time to generate', Config::TEXT_DOMAIN); ?></li>
                </ul>
            </div>
            
            <div class="sapg-card">
                <h3><?php echo esc_html__('Recent Generations', Config::TEXT_DOMAIN); ?></h3>
                <?php
                $recent_posts = get_posts(array(
                    'posts_per_page' => 5,
                    'meta_key' => '_sapg_generated',
                    'meta_value' => '1',
                    'post_status' => 'any',
                ));
                
                if ($recent_posts): ?>
                    <ul class="sapg-recent-list">
                        <?php foreach ($recent_posts as $post): ?>
                            <li>
                                <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>">
                                    <?php echo esc_html($post->post_title); ?>
                                </a>
                                <span class="sapg-post-meta">
                                    <?php echo esc_html(get_post_meta($post->ID, '_sapg_model', true)); ?> - 
                                    <?php echo esc_html(human_time_diff(strtotime($post->post_date), current_time('timestamp'))); ?> 
                                    <?php echo esc_html__('ago', Config::TEXT_DOMAIN); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><?php echo esc_html__('No content generated yet.', Config::TEXT_DOMAIN); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
