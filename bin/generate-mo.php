#!/usr/bin/env php
<?php
/**
 * Generate MO files from PO files
 *
 * Simple PHP script to convert .po files to .mo files without external dependencies.
 *
 * Usage: php bin/generate-mo.php
 *
 * @package Simple_AI_Page_Generator
 * @since 2.0.0
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Get plugin root directory
$plugin_dir = dirname(__DIR__);
$languages_dir = $plugin_dir . '/languages';

echo "🌍 Generating MO files from PO files...\n\n";

// Find all .po files
$po_files = glob($languages_dir . '/*.po');

if (empty($po_files)) {
    echo "❌ No .po files found in {$languages_dir}\n";
    exit(1);
}

echo "Found " . count($po_files) . " PO file(s):\n";

foreach ($po_files as $po_file) {
    $filename = basename($po_file);
    $mo_file = str_replace('.po', '.mo', $po_file);
    
    echo "  📄 {$filename} -> ";
    
    // Try to use msgfmt if available
    $msgfmt_available = false;
    exec('msgfmt --version 2>&1', $output, $return_code);
    if ($return_code === 0) {
        $msgfmt_available = true;
    }
    
    if ($msgfmt_available) {
        // Use msgfmt
        exec("msgfmt \"{$po_file}\" -o \"{$mo_file}\" 2>&1", $output, $return_code);
        
        if ($return_code === 0 && file_exists($mo_file)) {
            echo "✅ Generated\n";
        } else {
            echo "❌ Failed\n";
        }
    } else {
        // Fallback: Use PHP implementation
        if (convertPoToMo($po_file, $mo_file)) {
            echo "✅ Generated (PHP)\n";
        } else {
            echo "❌ Failed\n";
        }
    }
}

echo "\n🎉 Done!\n";

/**
 * Convert PO file to MO file using PHP
 *
 * @param string $po_file Path to .po file
 * @param string $mo_file Path to output .mo file
 * @return bool Success status
 */
function convertPoToMo($po_file, $mo_file) {
    if (!file_exists($po_file)) {
        return false;
    }
    
    $po_content = file_get_contents($po_file);
    $entries = array();
    
    // Parse PO file
    $lines = explode("\n", $po_content);
    $current_msgid = '';
    $current_msgstr = '';
    $in_msgid = false;
    $in_msgstr = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments and empty lines
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        
        // msgid
        if (strpos($line, 'msgid ') === 0) {
            // Save previous entry
            if (!empty($current_msgid) && !empty($current_msgstr)) {
                $entries[$current_msgid] = $current_msgstr;
            }
            
            $current_msgid = extractString($line);
            $current_msgstr = '';
            $in_msgid = true;
            $in_msgstr = false;
        }
        // msgstr
        elseif (strpos($line, 'msgstr ') === 0) {
            $current_msgstr = extractString($line);
            $in_msgid = false;
            $in_msgstr = true;
        }
        // Continuation line
        elseif ($line[0] === '"') {
            $str = extractString($line);
            if ($in_msgid) {
                $current_msgid .= $str;
            } elseif ($in_msgstr) {
                $current_msgstr .= $str;
            }
        }
    }
    
    // Save last entry
    if (!empty($current_msgid) && !empty($current_msgstr)) {
        $entries[$current_msgid] = $current_msgstr;
    }
    
    // Generate MO file
    return generateMoFile($mo_file, $entries);
}

/**
 * Extract string from PO line
 *
 * @param string $line PO file line
 * @return string Extracted string
 */
function extractString($line) {
    if (preg_match('/"(.*)"/s', $line, $matches)) {
        return stripcslashes($matches[1]);
    }
    return '';
}

/**
 * Generate MO file from entries
 *
 * @param string $mo_file Path to output .mo file
 * @param array $entries Translation entries
 * @return bool Success status
 */
function generateMoFile($mo_file, $entries) {
    // MO file format constants
    $magic = 0x950412de;
    $revision = 0;
    
    // Build string tables
    $originals = '';
    $translations = '';
    $offsets = array();
    
    $offset = 0;
    foreach ($entries as $original => $translation) {
        // Original string
        $offsets[] = array(
            'length' => strlen($original),
            'offset' => $offset
        );
        $originals .= $original . "\0";
        $offset += strlen($original) + 1;
    }
    
    $offset = 0;
    foreach ($entries as $original => $translation) {
        // Translation string
        $offsets[] = array(
            'length' => strlen($translation),
            'offset' => $offset
        );
        $translations .= $translation . "\0";
        $offset += strlen($translation) + 1;
    }
    
    // Calculate header size
    $count = count($entries);
    $originals_offset = 28;
    $translations_offset = $originals_offset + ($count * 8);
    $hash_offset = $translations_offset + ($count * 8);
    $strings_offset = $hash_offset;
    
    // Build MO file
    $mo = '';
    
    // Header
    $mo .= pack('V', $magic);
    $mo .= pack('V', $revision);
    $mo .= pack('V', $count);
    $mo .= pack('V', $originals_offset);
    $mo .= pack('V', $translations_offset);
    $mo .= pack('V', 0); // Hash table size
    $mo .= pack('V', $hash_offset);
    
    // Original strings table
    $strings_offset = $hash_offset;
    for ($i = 0; $i < $count; $i++) {
        $mo .= pack('V', $offsets[$i]['length']);
        $mo .= pack('V', $strings_offset + $offsets[$i]['offset']);
    }
    
    // Translation strings table
    $strings_offset += strlen($originals);
    for ($i = $count; $i < $count * 2; $i++) {
        $mo .= pack('V', $offsets[$i]['length']);
        $mo .= pack('V', $strings_offset + $offsets[$i]['offset']);
    }
    
    // Strings
    $mo .= $originals;
    $mo .= $translations;
    
    // Write to file
    return file_put_contents($mo_file, $mo) !== false;
}

exit(0);
