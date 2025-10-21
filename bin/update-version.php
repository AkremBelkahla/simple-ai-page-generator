#!/usr/bin/env php
<?php
/**
 * Version Update Script
 *
 * Updates plugin version across all files and generates changelog entry.
 *
 * Usage: php bin/update-version.php <new_version> "<changelog_message>"
 * Example: php bin/update-version.php 2.0.1 "Fixed API connection bug"
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
    echo "Usage: php bin/update-version.php <new_version> \"<changelog_message>\"\n";
    echo "Example: php bin/update-version.php 2.0.1 \"Fixed API connection bug\"\n";
    exit(1);
}

$new_version = $argv[1];
$changelog_message = $argv[2];

// Validate version format (semantic versioning)
if (!preg_match('/^\d+\.\d+\.\d+$/', $new_version)) {
    echo "Error: Version must follow semantic versioning (e.g., 2.0.1)\n";
    exit(1);
}

// Get plugin root directory
$plugin_dir = dirname(__DIR__);

// Files to update
$files_to_update = [
    'simple-ai-page-generator.php' => [
        'patterns' => [
            '/Version:\s*[\d.]+/' => 'Version: ' . $new_version,
            '/define\(\'SAPG_VERSION\',\s*\'[\d.]+\'\)/' => "define('SAPG_VERSION', '{$new_version}')",
        ]
    ],
    'includes/class-config.php' => [
        'patterns' => [
            '/const VERSION = \'[\d.]+\';/' => "const VERSION = '{$new_version}';",
        ]
    ],
    'README.md' => [
        'patterns' => [
            '/version-[\d.]+-green/' => "version-{$new_version}-green",
        ]
    ],
    'readme.txt' => [
        'patterns' => [
            '/Stable tag:\s*[\d.]+/' => 'Stable tag: ' . $new_version,
        ]
    ],
];

echo "🚀 Updating plugin version to {$new_version}...\n\n";

// Update version in files
foreach ($files_to_update as $file => $config) {
    $file_path = $plugin_dir . '/' . $file;
    
    if (!file_exists($file_path)) {
        echo "⚠️  Warning: File not found: {$file}\n";
        continue;
    }
    
    $content = file_get_contents($file_path);
    $original_content = $content;
    
    foreach ($config['patterns'] as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    if ($content !== $original_content) {
        file_put_contents($file_path, $content);
        echo "✅ Updated: {$file}\n";
    } else {
        echo "ℹ️  No changes: {$file}\n";
    }
}

echo "\n";

// Update CHANGELOG.md
$changelog_path = $plugin_dir . '/CHANGELOG.md';
if (file_exists($changelog_path)) {
    $changelog_content = file_get_contents($changelog_path);
    
    // Get current date
    $date = date('Y-m-d');
    
    // Create new changelog entry
    $new_entry = "\n## [{$new_version}] - {$date}\n\n";
    $new_entry .= "### Changed\n\n";
    $new_entry .= "- {$changelog_message}\n";
    
    // Insert after the first ## [Unreleased] or after the title
    if (preg_match('/^## \[Unreleased\]/m', $changelog_content)) {
        $changelog_content = preg_replace(
            '/(^## \[Unreleased\].*?\n)/',
            "$1{$new_entry}\n",
            $changelog_content,
            1
        );
    } else {
        // Insert after the main title and description
        $changelog_content = preg_replace(
            '/(# Changelog.*?\n\n.*?\n\n)/',
            "$1{$new_entry}\n",
            $changelog_content,
            1
        );
    }
    
    file_put_contents($changelog_path, $changelog_content);
    echo "✅ Updated: CHANGELOG.md\n";
} else {
    echo "⚠️  Warning: CHANGELOG.md not found\n";
}

echo "\n";

// Summary
echo "🎉 Version update complete!\n\n";
echo "Next steps:\n";
echo "1. Review the changes: git diff\n";
echo "2. Commit the changes: git add . && git commit -m \"chore: bump version to {$new_version}\"\n";
echo "3. Create a git tag: git tag -a v{$new_version} -m \"Version {$new_version}\"\n";
echo "4. Push changes: git push && git push --tags\n";
echo "\n";

exit(0);
