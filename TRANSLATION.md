# 🌍 Guide de Traduction / Translation Guide

## Langues Supportées / Supported Languages

- **🇬🇧 English (en_US)** - Langue par défaut / Default language
- **🇫🇷 Français (fr_FR)** - ✅ Disponible / Available

## 🎯 Fonctionnement / How It Works

Le plugin détecte automatiquement la langue de WordPress et charge la traduction appropriée :

The plugin automatically detects WordPress language and loads the appropriate translation:

- **Si WordPress est en français** → Interface en français
- **If WordPress is in French** → French interface
- **Sinon / Otherwise** → Interface en anglais / English interface

## ⚙️ Configuration

### Changer la Langue de WordPress / Change WordPress Language

1. Allez dans **Réglages > Général** / Go to **Settings > General**
2. Sélectionnez **Langue du site** / Select **Site Language**
3. Choisissez **Français** ou **English** / Choose **Français** or **English**
4. Sauvegardez / Save

Le plugin s'adaptera automatiquement ! / The plugin will adapt automatically!

## 📁 Fichiers de Traduction / Translation Files

```
languages/
├── ai-content-gen-fr_FR.po    # Fichier source français / French source file
├── ai-content-gen-fr_FR.mo    # Fichier compilé français / French compiled file
└── README.md                   # Documentation
```

## 🔧 Générer les Fichiers .mo / Generate .mo Files

### Méthode 1 : Utiliser Poedit (Recommandé / Recommended)

1. Téléchargez [Poedit](https://poedit.net/)
2. Ouvrez le fichier `.po` / Open the `.po` file
3. Sauvegardez / Save → Le fichier `.mo` est généré automatiquement / The `.mo` file is generated automatically

### Méthode 2 : Script PHP Inclus / Included PHP Script

```bash
php bin/generate-mo.php
```

Ce script génère automatiquement tous les fichiers `.mo` depuis les fichiers `.po`.

This script automatically generates all `.mo` files from `.po` files.

### Méthode 3 : WP-CLI

```bash
wp i18n make-mo languages/
```

### Méthode 4 : Outils en Ligne / Online Tools

Utilisez / Use: [Po2Mo Online Converter](https://po2mo.net/)

## 🌐 Ajouter une Nouvelle Langue / Add a New Language

### 1. Créer le Fichier PO / Create PO File

Copiez le fichier français / Copy the French file:

```bash
cp languages/ai-content-gen-fr_FR.po languages/ai-content-gen-{locale}.po
```

Exemples / Examples:
- Espagnol / Spanish: `ai-content-gen-es_ES.po`
- Allemand / German: `ai-content-gen-de_DE.po`
- Italien / Italian: `ai-content-gen-it_IT.po`

### 2. Traduire / Translate

Ouvrez le fichier avec Poedit ou un éditeur de texte et traduisez tous les `msgstr`.

Open the file with Poedit or a text editor and translate all `msgstr` entries.

### 3. Générer le Fichier MO / Generate MO File

```bash
php bin/generate-mo.php
```

### 4. Tester / Test

1. Changez la langue de WordPress / Change WordPress language
2. Vérifiez l'interface du plugin / Check the plugin interface

## 📝 Chaînes Traduisibles / Translatable Strings

Le plugin utilise les fonctions WordPress i18n / The plugin uses WordPress i18n functions:

```php
// Retourne la traduction / Returns translation
__('Text', 'ai-content-gen')

// Affiche la traduction / Echoes translation
_e('Text', 'ai-content-gen')

// Avec échappement HTML / With HTML escaping
esc_html__('Text', 'ai-content-gen')
esc_html_e('Text', 'ai-content-gen')

// Avec contexte / With context
_x('Post', 'content type', 'ai-content-gen')

// Pluriels / Plurals
_n('%s word', '%s words', $count, 'ai-content-gen')
```

## 🎨 Zones Traduites / Translated Areas

### Interface Admin / Admin Interface
- ✅ Menu et sous-menus / Menus and submenus
- ✅ Formulaires / Forms
- ✅ Boutons / Buttons
- ✅ Messages d'erreur / Error messages
- ✅ Messages de succès / Success messages
- ✅ Tooltips et descriptions / Tooltips and descriptions

### Pages du Plugin / Plugin Pages
- ✅ Page de génération / Generation page
- ✅ Page de paramètres / Settings page
- ✅ Page de statistiques / Statistics page

### Messages JavaScript / JavaScript Messages
- ✅ Notifications AJAX
- ✅ Messages de chargement / Loading messages
- ✅ Confirmations

## 🔍 Vérifier les Traductions / Check Translations

### Tester en Français / Test in French

1. **Réglages > Général > Langue du site** → Français
2. Allez dans **Générateur IA** / Go to **AI Generator**
3. Vérifiez que tout est en français / Check everything is in French

### Tester en Anglais / Test in English

1. **Settings > General > Site Language** → English
2. Go to **AI Generator**
3. Check everything is in English

## 🐛 Problèmes Courants / Common Issues

### Les traductions ne s'affichent pas / Translations don't show

**Solution:**
1. Vérifiez que le fichier `.mo` existe / Check that `.mo` file exists
2. Videz le cache WordPress / Clear WordPress cache
3. Vérifiez la langue dans **Réglages > Général** / Check language in **Settings > General**

### Certaines chaînes ne sont pas traduites / Some strings are not translated

**Solution:**
1. Vérifiez que la chaîne existe dans le fichier `.po` / Check the string exists in `.po` file
2. Régénérez le fichier `.mo` / Regenerate the `.mo` file
3. Videz le cache / Clear cache

## 🤝 Contribuer / Contribute

Vous voulez ajouter une traduction ? / Want to add a translation?

1. 🍴 Fork le projet / Fork the project
2. 📝 Créez votre fichier de traduction / Create your translation file
3. 🔄 Testez / Test
4. 📤 Soumettez une Pull Request / Submit a Pull Request

## 📞 Support

Besoin d'aide ? / Need help?

- 📧 Email: contact@infinityweb.tn
- 💻 GitHub: [Issues](https://github.com/AkremBelkahla/simple-ai-page-generator/issues)
- 📚 Documentation: [infinityweb.tn/docs/sapg](https://infinityweb.tn/docs/sapg)

---

**Note**: Les fichiers `.mo` doivent être générés localement. Ils ne sont pas inclus dans le dépôt Git.

**Note**: The `.mo` files must be generated locally. They are not included in the Git repository.
