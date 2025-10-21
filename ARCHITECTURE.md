# Architecture du Plugin

Ce document décrit l'architecture technique de Simple AI Page Generator version 2.0.0.

## 📐 Vue d'Ensemble

Le plugin suit une architecture modulaire basée sur les principes SOLID et les standards WordPress. Il utilise des namespaces PHP, un autoloader PSR-4, et une séparation claire des responsabilités.

## 🏗️ Structure des Dossiers

```
simple-ai-page-generator/
│
├── assets/                          # Ressources front-end
│   ├── css/
│   │   └── admin-style.css         # Styles admin
│   └── js/
│       └── admin-script.js         # Scripts admin
│
├── includes/                        # Code PHP du plugin
│   │
│   ├── admin/                       # Contrôleurs admin
│   │   └── class-admin-controller.php
│   │
│   ├── api/                         # Clients API
│   │   ├── class-api-client.php    # Classe abstraite
│   │   └── class-openai-client.php # Implémentation OpenAI
│   │
│   ├── core/                        # Composants core
│   │   ├── class-logger.php        # Système de logging
│   │   └── class-validator.php     # Validation/Sanitization
│   │
│   ├── generator/                   # Logique de génération
│   │   └── class-content-generator.php
│   │
│   ├── class-autoloader.php        # Autoloader PSR-4
│   ├── class-config.php            # Configuration
│   └── class-plugin.php            # Classe principale
│
├── languages/                       # Fichiers de traduction
│   └── ai-content-gen.pot
│
├── templates/                       # Templates de vues
│   └── admin/
│       ├── main-page.php
│       ├── settings-page.php
│       └── statistics-page.php
│
├── simple-ai-page-generator.php    # Point d'entrée
├── uninstall.php                   # Script de désinstallation
├── README.md                        # Documentation utilisateur
├── CHANGELOG.md                     # Historique des versions
├── CONTRIBUTING.md                  # Guide de contribution
└── ARCHITECTURE.md                  # Ce fichier
```

## 🔧 Composants Principaux

### 1. Plugin (Singleton)

**Fichier** : `includes/class-plugin.php`

Classe principale qui orchestre tous les composants du plugin.

**Responsabilités** :
- Initialisation du plugin
- Enregistrement des hooks WordPress
- Gestion du cycle de vie (activation/désactivation)
- Coordination des composants

**Pattern** : Singleton

```php
$plugin = \Simple_AI_Page_Generator\Plugin::get_instance();
```

### 2. Autoloader

**Fichier** : `includes/class-autoloader.php`

Charge automatiquement les classes selon la convention PSR-4.

**Convention de nommage** :
- Namespace : `Simple_AI_Page_Generator\Sous_Package`
- Fichier : `class-nom-classe.php`
- Dossier : `sous-package/`

**Exemple** :
```
Simple_AI_Page_Generator\Core\Logger
→ includes/core/class-logger.php
```

### 3. Config

**Fichier** : `includes/class-config.php`

Centralise toutes les constantes et configurations du plugin.

**Contient** :
- Versions et prérequis
- Modèles IA supportés
- Options par défaut
- Constantes de configuration

### 4. Logger

**Fichier** : `includes/core/class-logger.php`

Système de logging conforme PSR-3.

**Niveaux** :
- emergency, alert, critical, error
- warning, notice, info, debug

**Fonctionnalités** :
- Rotation automatique des logs
- Protection des fichiers (.htaccess)
- Nettoyage automatique (30 jours)
- Intégration avec WP_DEBUG

### 5. Validator

**Fichier** : `includes/core/class-validator.php`

Validation et sanitization strictes des données.

**Méthodes** :
- `validate_text()` - Texte simple
- `validate_email()` - Emails
- `validate_url()` - URLs
- `validate_int()` - Entiers
- `validate_api_key()` - Clés API
- `validate_ai_model()` - Modèles IA
- `verify_nonce()` - Nonces
- `check_capability()` - Permissions

### 6. API Client (Abstract)

**Fichier** : `includes/api/class-api-client.php`

Classe abstraite pour tous les clients API.

**Méthodes abstraites** :
- `generate_content()` - Génération de contenu
- `test_connection()` - Test de connexion

**Méthodes communes** :
- `make_request()` - Requête HTTP
- `get_cached_response()` - Récupération cache
- `set_cached_response()` - Mise en cache
- `calculate_max_tokens()` - Calcul tokens

### 7. Content Generator

**Fichier** : `includes/generator/class-content-generator.php`

Orchestre la génération de contenu.

**Workflow** :
1. Validation des paramètres
2. Récupération du client API
3. Construction du prompt
4. Génération du contenu
5. Création du post WordPress
6. Ajout des métadonnées

### 8. Admin Controller

**Fichier** : `includes/admin/class-admin-controller.php`

Gère l'interface d'administration.

**Responsabilités** :
- Enregistrement des menus
- Rendu des pages admin
- Enregistrement des settings
- Gestion des formulaires

## 🔄 Flux de Données

### Génération de Contenu

```
Utilisateur
    ↓
Formulaire Admin (template)
    ↓
Admin Controller (validation)
    ↓
Content Generator (orchestration)
    ↓
API Client (appel API)
    ↓
WordPress (création post)
    ↓
Métadonnées + Logs
```

### Validation des Données

```
Input Utilisateur
    ↓
Validator::validate_*()
    ↓
Sanitization
    ↓
Validation métier
    ↓
Logging (si erreur)
    ↓
Return (valeur ou false)
```

## 🎯 Principes de Conception

### SOLID

1. **Single Responsibility** : Chaque classe a une seule responsabilité
2. **Open/Closed** : Ouvert à l'extension, fermé à la modification
3. **Liskov Substitution** : Les classes dérivées sont substituables
4. **Interface Segregation** : Interfaces spécifiques et ciblées
5. **Dependency Inversion** : Dépendance aux abstractions

### Design Patterns

- **Singleton** : Plugin principal
- **Factory** : Création des clients API
- **Strategy** : Différents clients API
- **Template Method** : API Client abstrait
- **Dependency Injection** : Logger, Validator

### WordPress Best Practices

- Hooks et filtres pour extensibilité
- Nonces pour sécurité CSRF
- Capabilities pour permissions
- Transients pour cache
- Options API pour configuration
- Filesystem API pour fichiers

## 🔐 Sécurité

### Couches de Sécurité

1. **Validation** : Vérification du format et type
2. **Sanitization** : Nettoyage des données
3. **Escaping** : Échappement à l'affichage
4. **Nonces** : Protection CSRF
5. **Capabilities** : Contrôle d'accès
6. **Prepared Statements** : Protection SQL

### Points de Contrôle

- Tous les inputs utilisateur
- Toutes les sorties HTML
- Tous les formulaires
- Toutes les requêtes AJAX
- Tous les accès fichiers
- Toutes les requêtes DB

## ⚡ Performance

### Optimisations

1. **Cache** :
   - Transients WordPress
   - Cache des réponses API
   - Expiration configurable

2. **Lazy Loading** :
   - Chargement conditionnel des assets
   - Initialisation à la demande

3. **Database** :
   - Requêtes optimisées
   - Index appropriés
   - Nettoyage régulier

4. **Assets** :
   - Chargement uniquement sur pages admin
   - Minification (production)
   - Concaténation

## 🔌 Extensibilité

### Hooks Disponibles

**Actions** :
```php
do_action('sapg_init', $plugin);
do_action('sapg_content_generated', $post_id, $model, $word_count);
do_action('sapg_log', $level, $message, $context);
```

**Filtres** :
```php
apply_filters('sapg_required_capability', 'manage_options');
apply_filters('sapg_generation_prompt', $prompt, $title, $type, $count);
apply_filters('sapg_post_data', $data, $model, $word_count);
apply_filters('sapg_api_system_message', $message);
apply_filters('sapg_sanitize_options', $sanitized, $input);
```

### Ajouter un Nouveau Client API

1. Créer une classe dans `includes/api/`
2. Étendre `API_Client`
3. Implémenter les méthodes abstraites
4. Ajouter la configuration dans `Config`
5. Enregistrer dans `Content_Generator`

**Exemple** :
```php
namespace Simple_AI_Page_Generator\API;

class Mon_API_Client extends API_Client {
    
    public function generate_content($prompt, $word_count, array $options = array()) {
        // Implémentation
    }
    
    public function test_connection() {
        // Implémentation
    }
}
```

## 📊 Base de Données

### Options WordPress

- `sapg_options` : Configuration du plugin

### Post Meta

- `_sapg_generated` : Marqueur de contenu généré
- `_sapg_model` : Modèle IA utilisé
- `_sapg_word_count` : Nombre de mots demandé
- `_sapg_generated_date` : Date de génération
- `_sapg_version` : Version du plugin

### Transients

- `sapg_api_{hash}` : Cache des réponses API

## 🧪 Tests

### Tests Manuels

1. Installation/Désinstallation
2. Configuration API
3. Génération de contenu
4. Vérification des logs
5. Tests de sécurité
6. Tests de performance

### Tests Automatisés (À venir)

- Tests unitaires (PHPUnit)
- Tests d'intégration
- Tests E2E (Playwright)

## 📈 Évolutions Futures

### Prévues

- [ ] Tests unitaires complets
- [ ] Support d'autres APIs (Mistral, Cohere)
- [ ] Génération par lots
- [ ] Templates de prompts
- [ ] Planification de génération
- [ ] Intégration avec Gutenberg
- [ ] API REST pour génération
- [ ] Webhooks
- [ ] Export/Import de configuration

### Considérations

- Compatibilité ascendante
- Migration de données
- Performance à grande échelle
- Multisite support

## 📚 Ressources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
- [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)

---

**Version** : 2.0.0  
**Dernière mise à jour** : 2025-01-21  
**Auteur** : Akrem Belkahla
