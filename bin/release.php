#!/usr/bin/env php
<?php
/**
 * Release Script
 *
 * Complete release workflow: update version, update changelog, create git tag.
 *
 * Usage: php bin/release.php <version> <type>
 * Types: major, minor, patch
 * Example: php bin/release.php 2.1.0 minor
 *
 * @package Simple_AI_Page_Generator
 * @since 2.0.0
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Check arguments
if ($argc < 2) {
    echo "Usage: php bin/release.php <version> [type]\n";
    echo "\nTypes:\n";
    echo "  major - Breaking changes (1.0.0 -> 2.0.0)\n";
    echo "  minor - New features (1.0.0 -> 1.1.0)\n";
    echo "  patch - Bug fixes (1.0.0 -> 1.0.1)\n";
    echo "\nExample: php bin/release.php 2.1.0 minor\n";
    exit(1);
}

$new_version = $argv[1];
$release_type = isset($argv[2]) ? strtolower($argv[2]) : 'patch';

// Validate version format
if (!preg_match('/^\d+\.\d+\.\d+$/', $new_version)) {
    echo "Error: Version must follow semantic versioning (e.g., 2.0.1)\n";
    exit(1);
}

// Validate release type
$valid_types = ['major', 'minor', 'patch'];
if (!in_array($release_type, $valid_types)) {
    echo "Error: Invalid type '{$release_type}'. Must be one of: " . implode(', ', $valid_types) . "\n";
    exit(1);
}

// Get plugin root directory
$plugin_dir = dirname(__DIR__);

echo "🚀 Starting release process for version {$new_version} ({$release_type})...\n\n";

// Step 1: Check git status
echo "📋 Step 1: Checking git status...\n";
exec('git status --porcelain', $output, $return_code);
if (!empty($output)) {
    echo "⚠️  Warning: You have uncommitted changes:\n";
    foreach ($output as $line) {
        echo "   {$line}\n";
    }
    echo "\nDo you want to continue? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) !== 'y') {
        echo "Release cancelled.\n";
        exit(0);
    }
}
echo "✅ Git status checked\n\n";

// Step 2: Update CHANGELOG.md - Move [Unreleased] to new version
echo "📋 Step 2: Updating CHANGELOG.md...\n";
$changelog_path = $plugin_dir . '/CHANGELOG.md';
if (file_exists($changelog_path)) {
    $changelog_content = file_get_contents($changelog_path);
    $date = date('Y-m-d');
    
    // Replace [Unreleased] with new version
    $changelog_content = preg_replace(
        '/^## \[Unreleased\]/m',
        "## [Unreleased]\n\n## [{$new_version}] - {$date}",
        $changelog_content,
        1
    );
    
    file_put_contents($changelog_path, $changelog_content);
    echo "✅ CHANGELOG.md updated\n\n";
} else {
    echo "⚠️  Warning: CHANGELOG.md not found\n\n";
}

// Step 3: Update version in files
echo "📋 Step 3: Updating version in files...\n";
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
    }
}
echo "\n";

// Step 4: Git operations
echo "📋 Step 4: Git operations...\n";

// Add files
echo "Adding files to git...\n";
exec('git add .', $output, $return_code);
if ($return_code !== 0) {
    echo "❌ Error adding files to git\n";
    exit(1);
}

// Commit
$commit_message = "chore: release version {$new_version}";
echo "Creating commit: {$commit_message}\n";
exec("git commit -m \"{$commit_message}\"", $output, $return_code);
if ($return_code !== 0) {
    echo "⚠️  Warning: Commit failed (maybe no changes?)\n";
}

// Create tag
$tag_name = "v{$new_version}";
$tag_message = "Version {$new_version}";
echo "Creating tag: {$tag_name}\n";
exec("git tag -a {$tag_name} -m \"{$tag_message}\"", $output, $return_code);
if ($return_code !== 0) {
    echo "❌ Error creating git tag\n";
    exit(1);
}

echo "✅ Git operations completed\n\n";

// Step 5: Summary
echo "🎉 Release {$new_version} prepared successfully!\n\n";
echo "📝 Summary:\n";
echo "   Version: {$new_version}\n";
echo "   Type: {$release_type}\n";
echo "   Tag: {$tag_name}\n";
echo "   Date: " . date('Y-m-d H:i:s') . "\n";
echo "\n";
echo "🚀 Next steps:\n";
echo "   1. Review the changes: git show\n";
echo "   2. Push to remote: git push origin main\n";
echo "   3. Push tags: git push origin {$tag_name}\n";
echo "   4. Create GitHub release (optional)\n";
echo "\n";
echo "To push everything now, run:\n";
echo "   git push && git push --tags\n";
echo "\n";

exit(0);
