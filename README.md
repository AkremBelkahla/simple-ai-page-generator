# Simple AI Page Generator

Plugin WordPress professionnel pour générer du contenu de qualité en utilisant diverses API d'intelligence artificielle (OpenAI, DeepSeek, Google Gemini, Claude).

## Fonctionnalités

### Génération de Contenu
- **Multi-API** : Support de OpenAI, DeepSeek, Google Gemini et Claude (Anthropic)
- **Personnalisable** : Choix du nombre de mots (100 à 2000)
- **Flexible** : Génération de posts ou de pages
- **Intelligent** : Contenu structuré avec HTML sémantique

### Sécurité
- Validation stricte des entrées/sorties
- Sanitization complète des données
- Protection CSRF avec nonces
- Vérification des permissions utilisateur
- Chiffrement des clés API (optionnel)

### Performance
- Système de cache intégré
- Optimisation des requêtes API
- Nettoyage automatique des logs

### Administration
- Interface moderne et intuitive
- Statistiques détaillées
- Historique des générations
- Test de connexion API
- Logs détaillés pour le debugging

## Prérequis

- **WordPress** : 5.8 ou supérieur
- **PHP** : 7.4 ou supérieur
- **Permissions** : `manage_options` pour l'administration
- **API Key** : Au moins une clé API d'un service supporté

## Installation

### Installation Standard

1. Télécharger le plugin depuis le dépôt
2. Décompresser dans `/wp-content/plugins/`
3. Activer depuis le menu "Extensions" de WordPress
4. Configurer les clés API dans "AI Generator > Settings"

### Installation via WP-CLI

```bash
wp plugin install simple-ai-page-generator --activate
```

## Configuration

### 1. Configurer les Clés API

Accédez à **AI Generator > Settings** et ajoutez vos clés API :

- **OpenAI** : [Obtenir une clé](https://platform.openai.com/api-keys)
- **DeepSeek** : [Obtenir une clé](https://platform.deepseek.com)
- **Google Gemini** : [Obtenir une clé](https://ai.google.dev)
- **Claude (Anthropic)** : [Obtenir une clé](https://console.anthropic.com)

### 2. Paramètres par Défaut

Configurez les paramètres par défaut :
- Modèle IA préféré
- Nombre de mots par défaut
- Activation du cache
- Niveau de logging

## Utilisation

### Génération Simple

1. Accédez à **AI Generator** dans le menu admin
2. Entrez un titre ou un sujet (optionnel)
3. Sélectionnez le modèle IA
4. Choisissez le nombre de mots
5. Sélectionnez le type de contenu (Post/Page)
6. Définissez le statut de publication
7. Cliquez sur "Generate Content"

### Via Code

```php
// Obtenir l'instance du plugin
$plugin = \Simple_AI_Page_Generator\Plugin::get_instance();
$generator = $plugin->get_content_generator();

// Générer et créer un post
$post_id = $generator->generate_and_create_post(
    'Mon Titre',      // Titre
    'openai',         // Modèle
    500,              // Nombre de mots
    'post',           // Type de contenu
    'draft'           // Statut
);

if (is_wp_error($post_id)) {
    echo $post_id->get_error_message();
} else {
    echo "Post créé avec l'ID : " . $post_id;
}
```

## Architecture

### Structure des Fichiers

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

### Principes de Conception

- **PSR-4 Autoloading** : Chargement automatique des classes
- **Namespaces** : Organisation modulaire du code
- **Singleton Pattern** : Instance unique du plugin
- **Dependency Injection** : Injection des dépendances
- **Separation of Concerns** : Séparation des responsabilités
- **WordPress Coding Standards** : Respect des standards WordPress

## Hooks & Filtres

### Actions

```php
// Après l'initialisation du plugin
do_action('sapg_init', $plugin_instance);

// Après la génération de contenu
do_action('sapg_content_generated', $post_id, $model, $word_count);

// Logging personnalisé
do_action('sapg_log', $level, $message, $context);
```

### Filtres

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

## Logs et Debugging

### Activer les Logs

Les logs sont stockés dans `/wp-uploads/sapg-logs/` et sont automatiquement nettoyés après 30 jours.

```php
// Dans wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Niveaux de Log

- **emergency** : Système inutilisable
- **alert** : Action immédiate requise
- **critical** : Conditions critiques
- **error** : Erreurs d'exécution
- **warning** : Avertissements
- **notice** : Événements normaux mais significatifs
- **info** : Messages informatifs
- **debug** : Informations de débogage

## Tests

### Tests Manuels

1. Tester chaque API avec le bouton "Test Connection"
2. Générer du contenu avec différents paramètres
3. Vérifier les logs pour les erreurs
4. Consulter les statistiques

### Tests Unitaires (À venir)

```bash
composer install
vendor/bin/phpunit
```

## Sécurité

### Bonnes Pratiques Implémentées

- Validation stricte des entrées
- Sanitization des sorties
- Vérification des nonces
- Contrôle des permissions
- Échappement des données
- Préparation des requêtes SQL
- Protection contre les injections
- Logs sécurisés (.htaccess)

### Signaler une Vulnérabilité

Envoyez un email à : security@infinityweb.tn

## Changelog

### Version 2.0.0 (2025-01-21)

**Refactorisation Majeure**

- Architecture modulaire avec autoloader PSR-4
- Système de logging robuste
- Validation et sanitization strictes
- Configuration centralisée
- Documentation PHPDoc complète
- Interface admin modernisée
- Statistiques détaillées
- Support de Claude (Anthropic)
- Sécurité renforcée
- Performance optimisée
- Corrections de bugs

### Version 1.1.0

- Support de plusieurs APIs
- Interface admin améliorée

### Version 1.0.0

- Version initiale

## Contribution

Les contributions sont les bienvenues !

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## Licence

GPL v2 ou supérieur - voir [LICENSE](LICENSE)

## Auteur

**Akrem Belkahla**
- Website: [infinityweb.tn](https://infinityweb.tn)
- Email: akrem.belkahla@infinityweb.tn
- GitHub: [@AkremBelkahla](https://github.com/AkremBelkahla)

## Remerciements

- OpenAI pour GPT
- DeepSeek pour leur API
- Google pour Gemini
- Anthropic pour Claude
- La communauté WordPress

## Support

- **Documentation** : [infinityweb.tn/docs/sapg](https://infinityweb.tn/docs/sapg)
- **Issues** : [GitHub Issues](https://github.com/AkremBelkahla/simple-ai-page-generator/issues)
- **Email** : support@infinityweb.tn

---

Made with ❤️ by [Infinity Web](https://infinityweb.tn)
3. Configure the plugin settings through the dedicated menu.