<?php
/**
 * Plugin Name: SmartBot Assistant – AI-Powered Content Helper
 * Description: A lightweight, intelligent chatbot that enhances user engagement through AI-based content discovery and FAQ responses.
 * Version: 1.0.0
 * Author: Maidul
 * Text Domain: smartbot-assistant
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Define constants
define('SMARTBOT_ASSISTANT_VERSION', '1.0.0');
define('SMARTBOT_ASSISTANT_DIR', plugin_dir_path(__FILE__));
define('SMARTBOT_ASSISTANT_URL', plugin_dir_url(__FILE__));
define('SMARTBOT_ASSISTANT_BASENAME', plugin_basename(__FILE__));

// Composer autoload
if (file_exists(SMARTBOT_ASSISTANT_DIR . 'vendor/autoload.php')) {
    require_once SMARTBOT_ASSISTANT_DIR . 'vendor/autoload.php';
}

// Activation & Deactivation Hooks
register_activation_hook(__FILE__, 'smartbot_assistant_activate');
register_deactivation_hook(__FILE__, 'smartbot_assistant_deactivate');

function smartbot_assistant_activate() {
    flush_rewrite_rules(); // for CPT
    if (class_exists('\SmartBot\Includes\Logger')) {
        \SmartBot\Includes\Logger::create_log_table();
    }
}

function smartbot_assistant_deactivate() {
    flush_rewrite_rules();
}



// Initialize plugin
add_action('plugins_loaded', 'smartbot_assistant_init');

function smartbot_assistant_init() {
    load_plugin_textdomain('smartbot-assistant', false, dirname(SMARTBOT_ASSISTANT_BASENAME) . '/languages');

    // Initialize components
    \SmartBot\Includes\Logger::init();
    \SmartBot\Includes\FAQ_Manager::init();
    \SmartBot\Admin\Admin_UI::init();
    \SmartBot\Admin\Admin_Train::init();
    \SmartBot\Admin\Admin_Import::init();
    \SmartBot\Includes\API_Handler::init();
    \SmartBot\Includes\Shortcodes::init();
    \SmartBot\Includes\Chatbot_Loader::init();
}
