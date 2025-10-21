# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [2.0.0] - 2025-01-21

### 🎉 Refactorisation Majeure

Cette version représente une refonte complète du plugin avec une architecture moderne et professionnelle.

### ✨ Ajouté

- **Architecture modulaire** avec autoloader PSR-4
- **Namespaces PHP** pour une meilleure organisation du code
- **Système de logging robuste** avec rotation automatique des fichiers
- **Classe Validator** pour validation stricte des entrées/sorties
- **Classe Config** pour configuration centralisée
- **Classe Logger** avec support de tous les niveaux PSR-3
- **Admin Controller** pour séparation de la logique admin
- **API Client abstrait** pour faciliter l'ajout de nouvelles APIs
- **Content Generator** avec logique métier isolée
- **Templates séparés** pour les pages admin
- **Statistiques détaillées** avec graphiques et métriques
- **Support de Claude (Anthropic)** comme nouveau modèle IA
- **Test de connexion API** directement depuis l'interface
- **Cache système** pour optimiser les performances
- **Nettoyage automatique** des logs anciens (30 jours)
- **Documentation PHPDoc** complète sur toutes les classes et méthodes
- **Hooks et filtres** pour extensibilité
- **Interface moderne** avec design responsive

### 🔒 Sécurité

- Validation stricte de toutes les entrées utilisateur
- Sanitization complète des données
- Vérification systématique des nonces
- Contrôle des permissions utilisateur
- Échappement des sorties
- Protection des fichiers de logs avec .htaccess
- Préparation sécurisée des requêtes SQL
- Chiffrement optionnel des clés API

### ⚡ Performance

- Système de cache avec transients WordPress
- Optimisation des requêtes à la base de données
- Chargement conditionnel des assets
- Minification et concaténation des scripts
- Lazy loading des composants

### 🎨 Interface Utilisateur

- Design moderne et épuré
- Interface responsive pour mobile
- Indicateurs visuels de statut
- Messages d'erreur clairs et informatifs
- Sidebar avec conseils et historique
- Boutons d'action intuitifs
- Animations et transitions fluides

### 📊 Statistiques

- Nombre total de contenus générés
- Répartition par modèle IA
- Répartition par type de contenu
- Graphiques de progression
- Historique des générations récentes

### 🛠️ Technique

- Respect des WordPress Coding Standards
- Architecture SOLID
- Dependency Injection
- Singleton Pattern pour le plugin principal
- Séparation des responsabilités (SoC)
- Code documenté et commenté
- Gestion d'erreurs robuste

### 📝 Documentation

- README.md complet et détaillé
- CHANGELOG.md structuré
- Documentation inline PHPDoc
- Exemples de code
- Guide d'utilisation des hooks
- Instructions d'installation et configuration

### 🔄 Modifié

- Refonte complète de la structure des fichiers
- Amélioration de la gestion des erreurs
- Optimisation des appels API
- Mise à jour des dépendances
- Amélioration de l'expérience utilisateur
- Refactorisation du code legacy

### 🐛 Corrigé

- Correction des fuites mémoire
- Résolution des conflits de noms
- Correction des erreurs de validation
- Fix des problèmes de cache
- Correction des bugs d'affichage
- Résolution des problèmes de permissions

### 🗑️ Supprimé

- Code obsolète et non utilisé
- Dépendances inutiles
- Fonctions dépréciées
- Fichiers de configuration redondants

### ⚠️ Breaking Changes

- Nouvelle structure de fichiers (migration nécessaire)
- Changement des namespaces
- Modification des noms de hooks
- Nouvelle organisation des options

### 📦 Migration depuis 1.x

Pour migrer depuis la version 1.x :

1. **Sauvegarder** vos clés API actuelles
2. **Désactiver** le plugin version 1.x
3. **Supprimer** l'ancienne version
4. **Installer** la version 2.0.0
5. **Activer** le nouveau plugin
6. **Reconfigurer** vos clés API dans les nouveaux paramètres

Les contenus générés précédemment restent intacts.

---

## [1.1.0] - 2024-09-02

### Ajouté

- Support de DeepSeek API
- Support de Google Gemini API
- Support de Claude (Anthropic) API
- Interface de configuration des API
- Validation des clés API
- Gestion des erreurs API

### Modifié

- Amélioration de l'interface admin
- Optimisation des appels API
- Mise à jour de la documentation

### Corrigé

- Bugs mineurs d'affichage
- Problèmes de compatibilité

---

## [1.0.0] - 2024-04-22

### Ajouté

- Version initiale du plugin
- Support de OpenAI GPT-3.5
- Génération de posts et pages
- Interface admin basique
- Configuration des paramètres
- Choix du nombre de mots
- Sélection du type de contenu

---

## Légende

- ✨ **Ajouté** : Nouvelles fonctionnalités
- 🔄 **Modifié** : Changements dans les fonctionnalités existantes
- 🐛 **Corrigé** : Corrections de bugs
- 🗑️ **Supprimé** : Fonctionnalités retirées
- 🔒 **Sécurité** : Corrections de vulnérabilités
- ⚡ **Performance** : Améliorations de performance
- 📝 **Documentation** : Changements dans la documentation
- ⚠️ **Breaking Changes** : Changements incompatibles avec les versions précédentes

---

[2.0.0]: https://github.com/AkremBelkahla/simple-ai-page-generator/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/AkremBelkahla/simple-ai-page-generator/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/AkremBelkahla/simple-ai-page-generator/releases/tag/v1.0.0
