# 🤖 Simple AI Page Generator

A professional WordPress plugin to generate high-quality content using AI. Currently supports **DeepSeek** with more AI models coming soon (OpenAI GPT, Google Gemini, Claude, Grok).

[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![Version](https://img.shields.io/badge/version-2.0.0-green.svg)](https://github.com/AkremBelkahla/simple-ai-page-generator)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)](LICENSE)

## ✨ Features

### 🎨 Content Generation
- **AI Models** 🤖 :
  - ✅ **DeepSeek** - Fully supported
  - 🔜 **OpenAI GPT** - Coming soon
  - 🔜 **Google Gemini** - Coming soon
  - 🔜 **Claude (Anthropic)** - Coming soon
  - 🔜 **Grok (xAI)** - Coming soon
- **Customizable** ⚙️ : Choose word count (100 to 2000 words)
- **Flexible** 📝 : Generate posts or pages
- **Smart** 🧠 : Structured content with semantic HTML

### 🔒 Security
- ✅ Strict input/output validation
- ✅ Complete data sanitization
- ✅ CSRF protection with nonces
- ✅ User permission verification
- ✅ Optional API key encryption

### ⚡ Performance
- 🚀 Built-in caching system
- 🎯 Optimized API requests
- 🧹 Automatic log cleanup

### 📊 Administration
- 💎 Modern and intuitive interface
- 📈 Detailed statistics
- 📜 Generation history
- 🔌 API connection testing
- 🐛 Detailed logs for debugging

## 📋 Requirements

- **WordPress** 🔵 : 5.8 or higher
- **PHP** 🟣 : 7.4 or higher
- **Permissions** 🔐 : `manage_options` for administration
- **API Key** 🔑 : At least one API key from a supported service

## 🌍 Languages

The plugin automatically adapts to your WordPress language:

- **🇬🇧 English** - Default language
- **🇫🇷 Français** - Full French translation available

**How it works**: If WordPress is set to French, the plugin interface will be in French. Otherwise, it defaults to English.

See [TRANSLATION.md](TRANSLATION.md) for more details on translations.

## 🚀 Installation

### Standard Installation

1. 📥 Download the plugin from the repository
2. 📂 Extract to `/wp-content/plugins/`
3. ✅ Activate from WordPress "Plugins" menu
4. ⚙️ Configure API keys in "AI Generator > Settings"

### WP-CLI Installation

```bash
wp plugin install simple-ai-page-generator --activate
```

## ⚙️ Configuration

### 1. Configure API Keys 🔑

Go to **AI Generator > Settings** and add your API key:

- **DeepSeek** 🔍 : [Get a key](https://platform.deepseek.com) ✅ **Available now**

**Coming Soon** 🔜 :
- **OpenAI GPT** 🤖 : [Get a key](https://platform.openai.com/api-keys)
- **Google Gemini** 💎 : [Get a key](https://ai.google.dev)
- **Claude (Anthropic)** 🎭 : [Get a key](https://console.anthropic.com)
- **Grok (xAI)** ⚡ : [Get a key](https://x.ai)

### 2. Default Settings 📝

Configure default settings:
- 🎯 Preferred AI model
- 📊 Default word count
- 💾 Cache activation
- 📋 Logging level

## 📖 Usage

### Simple Generation ✨

1. 🎯 Go to **AI Generator** in the admin menu
2. ✍️ Enter a title or topic (optional)
3. 🤖 Select the AI model
4. 📊 Choose word count
5. 📝 Select content type (Post/Page)
6. 🚦 Set publication status
7. 🎬 Click "Generate Content"

### Via Code 💻

```php
// Get plugin instance
$plugin = \Simple_AI_Page_Generator\Plugin::get_instance();
$generator = $plugin->get_content_generator();

// Generate and create a post
$post_id = $generator->generate_and_create_post(
    'My Title',       // Title
    'deepseek',       // Model (currently only DeepSeek is available)
    500,              // Word count
    'post',           // Content type
    'draft'           // Status
);

if (is_wp_error($post_id)) {
    echo $post_id->get_error_message();
} else {
    echo "Post created with ID: " . $post_id;
}
```

## 🏗️ Architecture

### File Structure 📁

```
simple-ai-page-generator/
├── assets/
│   ├── css/
│   │   └── admin-style.css
│   └── js/
│       └── admin-script.js
├── includes/
│   ├── admin/
│   │   └── class-admin-controller.php
│   ├── api/
│   │   ├── class-api-client.php
│   │   └── class-openai-client.php
│   ├── core/
│   │   ├── class-logger.php
│   │   └── class-validator.php
│   ├── generator/
│   │   └── class-content-generator.php
│   ├── class-autoloader.php
│   ├── class-config.php
│   └── class-plugin.php
├── languages/
├── templates/
│   └── admin/
│       ├── main-page.php
│       ├── settings-page.php
│       └── statistics-page.php
├── simple-ai-page-generator.php
└── uninstall.php
```

### Design Principles 🎯

- **PSR-4 Autoloading** 🔄 : Automatic class loading
- **Namespaces** 📦 : Modular code organization
- **Singleton Pattern** 🎭 : Single plugin instance
- **Dependency Injection** 💉 : Dependency injection
- **Separation of Concerns** 🎨 : Responsibility separation
- **WordPress Coding Standards** ✅ : WordPress standards compliance

## 🔌 Hooks & Filters

### Actions 🎬

```php
// Après l'initialisation du plugin
do_action('sapg_init', $plugin_instance);

// Après la génération de contenu
do_action('sapg_content_generated', $post_id, $model, $word_count);

// Logging personnalisé
do_action('sapg_log', $level, $message, $context);
```

### Filters 🎛️

```php
// Modifier la capacité requise
add_filter('sapg_required_capability', function($cap) {
    return 'edit_posts'; // Au lieu de 'manage_options'
});

// Personnaliser le prompt de génération
add_filter('sapg_generation_prompt', function($prompt, $title, $type, $count) {
    return $prompt . ' Ajoute des emojis.';
}, 10, 4);

// Modifier les données du post avant création
add_filter('sapg_post_data', function($data, $model, $word_count) {
    $data['post_category'] = [1, 2]; // Ajouter des catégories
    return $data;
}, 10, 3);

// Personnaliser le message système de l'API
add_filter('sapg_api_system_message', function($message) {
    return 'Tu es un expert SEO...';
});

// Modifier les options après validation
add_filter('sapg_sanitize_options', function($sanitized, $input) {
    // Logique personnalisée
    return $sanitized;
}, 10, 2);
```

## 📊 Logs and Debugging

### Enable Logs 🐛

Logs are stored in `/wp-uploads/sapg-logs/` and automatically cleaned after 30 days.

```php
// Dans wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Log Levels 📋

- **emergency** 🚨 : System unusable
- **alert** ⚠️ : Immediate action required
- **critical** 🔴 : Critical conditions
- **error** ❌ : Runtime errors
- **warning** ⚡ : Warnings
- **notice** 📢 : Normal but significant events
- **info** ℹ️ : Informational messages
- **debug** 🐛 : Debug information

## 🧪 Testing

### Manual Tests ✅

1. 🔌 Test each API with "Test Connection" button
2. 📝 Generate content with different parameters
3. 📋 Check logs for errors
4. 📊 Review statistics

### Unit Tests 🔬 (Coming Soon)

```bash
composer install
vendor/bin/phpunit
```

## 🔒 Security

### Implemented Best Practices ✅

- ✅ Strict input validation
- ✅ Output sanitization
- ✅ Nonce verification
- ✅ Permission control
- ✅ Data escaping
- ✅ SQL query preparation
- ✅ Injection protection
- ✅ Secured logs (.htaccess)

### Report a Vulnerability 🚨

Send an email to: security@infinityweb.tn

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

### Version 2.0.0 (2025-01-21) 🎉

**Major Refactoring**

- ✨ Modular architecture with PSR-4 autoloader
- 📋 Robust logging system
- 🔒 Strict validation and sanitization
- ⚙️ Centralized configuration
- 📚 Complete PHPDoc documentation
- 💎 Modernized admin interface
- 📊 Detailed statistics
- 🤖 Claude (Anthropic) support
- 🔐 Enhanced security
- ⚡ Optimized performance
- 🐛 Bug fixes

## 📦 Version Management

This plugin uses automated version management scripts. See [VERSION_MANAGEMENT.md](VERSION_MANAGEMENT.md) for details.

### Quick Commands

```bash
# Add changelog entry
php bin/changelog.php fixed "Bug description"

# Update version
php bin/update-version.php 2.0.1 "Release summary"

# Complete release
php bin/release.php 2.1.0 minor
```

## 🤝 Contributing

Contributions are welcome! 🎉

1. 🍴 Fork the project
2. 🌿 Create a branch (`git checkout -b feature/AmazingFeature`)
3. 💾 Commit your changes (`git commit -m 'Add AmazingFeature'`)
4. 📤 Push to the branch (`git push origin feature/AmazingFeature`)
5. 🔀 Open a Pull Request

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

## 📄 License

GPL v2 or later - see [LICENSE](LICENSE)

## 👨‍💻 Author

**Akrem Belkahla**
- 🌐 Website: [infinityweb.tn](https://infinityweb.tn)
- 📧 Email: contact@infinityweb.tn
- 💻 GitHub: [@AkremBelkahla](https://github.com/AkremBelkahla)

## 🙏 Acknowledgments

- 🔍 **DeepSeek** for their powerful AI API
- 🌍 **WordPress community** for continuous support
- 🔜 **Coming soon**: OpenAI, Google Gemini, Anthropic Claude, and xAI Grok integrations

## 📞 Support

- 📚 **Documentation**: [infinityweb.tn/docs/sapg](https://infinityweb.tn/docs/sapg)
- 🐛 **Issues**: [GitHub Issues](https://github.com/AkremBelkahla/simple-ai-page-generator/issues)
- 📧 **Email**: support@infinityweb.tn

---

Made with ❤️ by [Infinity Web](https://infinityweb.tn)
3. Configure the plugin settings through the dedicated menu.