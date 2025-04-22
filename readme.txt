=== Simple AI Page Generator ===
Contributors: Votre Nom
Tags: ai, content generation, openai, deepseek, gemini
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Un plugin WordPress pour générer du contenu via différentes API d'IA.

== Description ==

Ce plugin permet de générer du contenu (articles ou pages) en utilisant différentes API d'IA comme OpenAI, DeepSeek et Gemini.

== Installation ==

1. Téléchargez le plugin
2. Décompressez le fichier dans le dossier wp-content/plugins
3. Activez le plugin dans le tableau de bord WordPress

== Structure du projet ==

Le plugin est organisé comme suit :

- /assets
  - /css
    - admin-style.css
  - /js
    - (futurs fichiers JavaScript)
- /includes
  - /admin
    - admin-pages.php
  - /api
    - api-settings.php
  - /generation
    - content-generation.php
  - /helpers
    - helpers.php
- /languages
  - Fichiers de traduction

== Changelog ==

= 1.1.0 =
* Ajout de la prise en charge de l'API Gemini
* Amélioration de la gestion des clés API
* Correction de bugs mineurs

= 1.0.0 =
* Version initiale
