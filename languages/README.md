# 🌍 Translations

This directory contains translation files for Simple AI Page Generator.

## 📋 Available Languages

- **English (en_US)** - Default language
- **French (fr_FR)** - Français ✅

## 🔧 How to Generate .mo Files

The `.mo` files are binary compiled versions of `.po` files used by WordPress.

### Method 1: Using Poedit (Recommended)

1. Download and install [Poedit](https://poedit.net/)
2. Open the `.po` file in Poedit
3. Save the file - Poedit will automatically generate the `.mo` file

### Method 2: Using WP-CLI

```bash
wp i18n make-mo languages/
```

### Method 3: Using gettext tools

If you have gettext installed:

```bash
msgfmt languages/ai-content-gen-fr_FR.po -o languages/ai-content-gen-fr_FR.mo
```

### Method 4: Online Tools

Use online tools like:
- [Po2Mo Online Converter](https://po2mo.net/)
- Upload your `.po` file and download the `.mo` file

## 📝 Translation Files

- `ai-content-gen.pot` - Template file (to be created)
- `ai-content-gen-fr_FR.po` - French translation source
- `ai-content-gen-fr_FR.mo` - French compiled translation (to be generated)

## 🌐 How WordPress Detects Language

WordPress automatically loads the correct translation based on:

1. **Site Language**: Set in Settings > General > Site Language
2. **User Language**: Each user can set their preferred language in their profile

If WordPress is set to French (`fr_FR`), it will automatically load `ai-content-gen-fr_FR.mo`.

## 🔄 Adding New Translations

To add a new language:

1. Copy `ai-content-gen-fr_FR.po` to `ai-content-gen-{locale}.po`
   - Example: `ai-content-gen-es_ES.po` for Spanish
2. Translate all `msgstr` entries
3. Generate the `.mo` file using one of the methods above
4. Place both files in this directory

## 🛠️ For Developers

### Generating POT Template

To extract all translatable strings and create a `.pot` template:

```bash
wp i18n make-pot . languages/ai-content-gen.pot
```

Or use Poedit to create a new catalog from sources.

### Translation Functions Used

The plugin uses WordPress i18n functions:

- `__()` - Returns translated string
- `_e()` - Echoes translated string
- `esc_html__()` - Returns escaped translated string
- `esc_html_e()` - Echoes escaped translated string
- `_n()` - Handles plural forms
- `_x()` - Translates with context

## 📞 Need Help?

For translation assistance:
- 📧 Email: contact@infinityweb.tn
- 💻 GitHub: [Create an issue](https://github.com/AkremBelkahla/simple-ai-page-generator/issues)

---

**Note**: The `.mo` files are not included in the repository. They should be generated locally or during the build process.
