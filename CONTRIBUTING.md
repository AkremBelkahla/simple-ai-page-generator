# Guide de Contribution

Merci de votre intérêt pour contribuer à Simple AI Page Generator ! Ce document fournit des directives pour contribuer au projet.

## 🤝 Comment Contribuer

### Signaler des Bugs

Si vous trouvez un bug :

1. Vérifiez que le bug n'a pas déjà été signalé dans les [Issues](https://github.com/AkremBelkahla/simple-ai-page-generator/issues)
2. Créez une nouvelle issue avec :
   - Un titre descriptif
   - Une description détaillée du problème
   - Les étapes pour reproduire le bug
   - Le comportement attendu vs le comportement actuel
   - Votre environnement (WordPress version, PHP version, etc.)
   - Des captures d'écran si pertinent

### Proposer des Fonctionnalités

Pour proposer une nouvelle fonctionnalité :

1. Ouvrez une issue avec le tag `enhancement`
2. Décrivez clairement la fonctionnalité
3. Expliquez pourquoi elle serait utile
4. Proposez une implémentation si possible

### Soumettre des Pull Requests

1. **Fork** le repository
2. **Créez une branche** depuis `develop` :
   ```bash
   git checkout -b feature/ma-fonctionnalite
   ```
3. **Faites vos modifications** en suivant les standards de code
4. **Testez** vos modifications
5. **Committez** avec des messages clairs :
   ```bash
   git commit -m "feat: ajoute support pour nouvelle API"
   ```
6. **Push** vers votre fork :
   ```bash
   git push origin feature/ma-fonctionnalite
   ```
7. **Ouvrez une Pull Request** vers la branche `develop`

## 📋 Standards de Code

### PHP

- Suivre les [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- Utiliser PHP 7.4+ features
- Documenter avec PHPDoc
- Typage strict des paramètres et retours
- Namespaces PSR-4

#### Exemple

```php
<?php
/**
 * Description de la classe
 *
 * @package Simple_AI_Page_Generator
 * @subpackage Nom_Sous_Package
 * @since 2.0.0
 */

namespace Simple_AI_Page_Generator\Nom_Package;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Ma_Classe
 *
 * Description détaillée de la classe.
 *
 * @since 2.0.0
 */
class Ma_Classe {
    
    /**
     * Propriété privée
     *
     * @var string
     */
    private $ma_propriete;
    
    /**
     * Constructeur
     *
     * @param string $param Description du paramètre.
     */
    public function __construct($param) {
        $this->ma_propriete = sanitize_text_field($param);
    }
    
    /**
     * Méthode publique
     *
     * @param int $id ID de l'élément.
     * @return bool|WP_Error True en cas de succès, WP_Error sinon.
     */
    public function ma_methode($id) {
        // Validation
        if (!is_numeric($id)) {
            return new WP_Error('invalid_id', 'ID invalide');
        }
        
        // Logique
        return true;
    }
}
```

### JavaScript

- Suivre les [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- Utiliser ES6+ quand possible
- Commenter le code complexe
- Utiliser JSDoc pour la documentation

#### Exemple

```javascript
/**
 * Fonction pour gérer un événement
 *
 * @param {Event} e - L'événement déclenché
 * @returns {void}
 */
function handleEvent(e) {
    e.preventDefault();
    
    // Logique
}
```

### CSS

- Suivre les [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)
- Utiliser des préfixes pour éviter les conflits : `.sapg-`
- Organiser par sections
- Commenter les sections importantes

#### Exemple

```css
/* ========================================
   Section Name
   ======================================== */

.sapg-element {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sapg-element:hover {
    background-color: #f0f0f1;
}
```

## 🧪 Tests

### Tests Manuels

Avant de soumettre une PR, testez :

1. Installation et activation du plugin
2. Configuration des API
3. Génération de contenu avec différents paramètres
4. Vérification des logs
5. Test sur différentes versions de WordPress
6. Test sur différentes versions de PHP

### Tests Automatisés

Les tests unitaires sont en cours de développement. Contribution bienvenue !

## 📝 Commits

Utilisez le format [Conventional Commits](https://www.conventionalcommits.org/) :

- `feat:` Nouvelle fonctionnalité
- `fix:` Correction de bug
- `docs:` Documentation
- `style:` Formatage, pas de changement de code
- `refactor:` Refactorisation
- `test:` Ajout de tests
- `chore:` Maintenance

### Exemples

```bash
feat: ajoute support pour DeepSeek API
fix: corrige validation des clés API
docs: met à jour le README avec exemples
refactor: améliore la structure du logger
```

## 🔒 Sécurité

### Bonnes Pratiques

- **Toujours** valider les entrées utilisateur
- **Toujours** sanitizer les sorties
- **Toujours** échapper les données avant affichage
- **Toujours** vérifier les permissions
- **Toujours** utiliser des nonces pour les formulaires
- **Jamais** faire confiance aux données externes
- **Jamais** exposer d'informations sensibles

### Signaler une Vulnérabilité

Si vous découvrez une vulnérabilité de sécurité :

1. **NE PAS** créer d'issue publique
2. Envoyer un email à : security@infinityweb.tn
3. Inclure une description détaillée
4. Proposer un correctif si possible

## 📚 Documentation

### Code

- Documenter toutes les classes publiques
- Documenter toutes les méthodes publiques
- Expliquer les algorithmes complexes
- Ajouter des exemples d'utilisation

### README

- Mettre à jour si vous ajoutez des fonctionnalités
- Ajouter des exemples de code
- Documenter les nouveaux hooks/filtres

### CHANGELOG

- Ajouter une entrée pour chaque changement
- Suivre le format Keep a Changelog
- Catégoriser les changements

## 🎨 Style et Qualité

### Outils Recommandés

- **PHP_CodeSniffer** pour vérifier les standards PHP
- **ESLint** pour JavaScript
- **Stylelint** pour CSS
- **PHPStan** pour l'analyse statique

### Installation

```bash
composer require --dev squizlabs/php_codesniffer
composer require --dev phpstan/phpstan
```

### Utilisation

```bash
# Vérifier le code PHP
vendor/bin/phpcs --standard=WordPress includes/

# Analyser le code
vendor/bin/phpstan analyse includes/
```

## 🌍 Internationalisation

- Utiliser les fonctions WordPress i18n : `__()`, `_e()`, `esc_html__()`, etc.
- Text domain : `ai-content-gen`
- Rendre toutes les chaînes traduisibles
- Utiliser des contextes quand nécessaire

### Exemple

```php
// Bon
echo esc_html__('Generate Content', 'ai-content-gen');

// Avec contexte
echo esc_html_x('Post', 'content type', 'ai-content-gen');

// Mauvais
echo 'Generate Content';
```

## 📦 Structure des Branches

- `main` : Version stable en production
- `develop` : Branche de développement
- `feature/*` : Nouvelles fonctionnalités
- `fix/*` : Corrections de bugs
- `hotfix/*` : Corrections urgentes

## ✅ Checklist PR

Avant de soumettre votre PR, vérifiez :

- [ ] Le code suit les standards WordPress
- [ ] Toutes les fonctions sont documentées
- [ ] Les tests manuels passent
- [ ] Pas de warnings PHP
- [ ] Pas d'erreurs JavaScript dans la console
- [ ] Le code est compatible PHP 7.4+
- [ ] Le code est compatible WordPress 5.8+
- [ ] Les chaînes sont traduisibles
- [ ] Le CHANGELOG est mis à jour
- [ ] La documentation est à jour

## 🙏 Remerciements

Merci à tous les contributeurs qui aident à améliorer ce plugin !

## 📞 Questions ?

Si vous avez des questions :

- Ouvrez une [Discussion](https://github.com/AkremBelkahla/simple-ai-page-generator/discussions)
- Envoyez un email : support@infinityweb.tn
- Consultez la [documentation](https://infinityweb.tn/docs/sapg)

---

**Bonne contribution ! 🚀**
