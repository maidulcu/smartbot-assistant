<?php
/**
 * Uninstall script for SmartBot Assistant plugin
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete custom tables
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}smartbot_logs");

// Delete plugin options
$option_keys = [
    'smartbot_assistant_enabled',
    'smartbot_assistant_welcome_message',
    'smartbot_assistant_api_key',
    'smartbot_claude_api_key',
    'smartbot_gemini_api_key',
    'smartbot_openrouter_api_key',
    'smartbot_ai_provider',
];

foreach ($option_keys as $key) {
    delete_option($key);
}
