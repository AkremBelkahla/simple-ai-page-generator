#!/usr/bin/env php
<?php
/**
 * Changelog Entry Script
 *
 * Adds a new entry to the CHANGELOG.md file.
 *
 * Usage: php bin/changelog.php <type> "<message>"
 * Types: added, changed, deprecated, removed, fixed, security
 * Example: php bin/changelog.php fixed "Corrected API timeout issue"
 *
 * @package Simple_AI_Page_Generator
 * @since 2.0.0
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Check arguments
if ($argc < 3) {
    echo "Usage: php bin/changelog.php <type> \"<message>\"\n";
    echo "\nTypes:\n";
    echo "  added      - New features\n";
    echo "  changed    - Changes in existing functionality\n";
    echo "  deprecated - Soon-to-be removed features\n";
    echo "  removed    - Removed features\n";
    echo "  fixed      - Bug fixes\n";
    echo "  security   - Security fixes\n";
    echo "\nExample: php bin/changelog.php fixed \"Corrected API timeout issue\"\n";
    exit(1);
}

$type = strtolower($argv[1]);
$message = $argv[2];

// Valid types
$valid_types = ['added', 'changed', 'deprecated', 'removed', 'fixed', 'security'];
$type_labels = [
    'added' => 'Added',
    'changed' => 'Changed',
    'deprecated' => 'Deprecated',
    'removed' => 'Removed',
    'fixed' => 'Fixed',
    'security' => 'Security',
];

$type_emojis = [
    'added' => '✨',
    'changed' => '🔄',
    'deprecated' => '⚠️',
    'removed' => '🗑️',
    'fixed' => '🐛',
    'security' => '🔒',
];

if (!in_array($type, $valid_types)) {
    echo "Error: Invalid type '{$type}'. Must be one of: " . implode(', ', $valid_types) . "\n";
    exit(1);
}

// Get plugin root directory
$plugin_dir = dirname(__DIR__);
$changelog_path = $plugin_dir . '/CHANGELOG.md';

if (!file_exists($changelog_path)) {
    echo "Error: CHANGELOG.md not found\n";
    exit(1);
}

$changelog_content = file_get_contents($changelog_path);

// Check if [Unreleased] section exists
if (!preg_match('/^## \[Unreleased\]/m', $changelog_content)) {
    // Create [Unreleased] section
    $unreleased_section = "\n## [Unreleased]\n\n";
    
    // Insert after the main title
    $changelog_content = preg_replace(
        '/(# Changelog.*?\n\n.*?\n\n)/',
        "$1{$unreleased_section}",
        $changelog_content,
        1
    );
}

// Find or create the type section within [Unreleased]
$type_label = $type_labels[$type];
$type_emoji = $type_emojis[$type];
$section_pattern = '/^## \[Unreleased\].*?\n(.*?)(?=\n## \[|$)/ms';

if (preg_match($section_pattern, $changelog_content, $matches)) {
    $unreleased_content = $matches[1];
    
    // Check if the type section exists
    if (preg_match("/^### {$type_label}/m", $unreleased_content)) {
        // Add to existing section
        $new_entry = "- {$type_emoji} {$message}\n";
        $changelog_content = preg_replace(
            "/(^### {$type_label}\n)/m",
            "$1{$new_entry}",
            $changelog_content,
            1
        );
    } else {
        // Create new section
        $new_section = "\n### {$type_label}\n\n- {$type_emoji} {$message}\n";
        
        // Insert after [Unreleased] header
        $changelog_content = preg_replace(
            '/(^## \[Unreleased\]\n)/m',
            "$1{$new_section}",
            $changelog_content,
            1
        );
    }
}

// Save the updated changelog
file_put_contents($changelog_path, $changelog_content);

echo "✅ Changelog entry added successfully!\n";
echo "\n";
echo "Type: {$type_emoji} {$type_label}\n";
echo "Message: {$message}\n";
echo "\n";
echo "Don't forget to commit your changes:\n";
echo "git add CHANGELOG.md\n";
echo "git commit -m \"{$type}: {$message}\"\n";
echo "\n";

exit(0);
