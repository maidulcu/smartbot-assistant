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

// Autoload core classes
require_once SMARTBOT_ASSISTANT_DIR . 'includes/class-chatbot-loader.php';
require_once SMARTBOT_ASSISTANT_DIR . 'includes/class-faq-manager.php';
require_once SMARTBOT_ASSISTANT_DIR . 'includes/class-logger.php';
require_once SMARTBOT_ASSISTANT_DIR . 'includes/class-shortcodes.php';
require_once SMARTBOT_ASSISTANT_DIR . 'includes/class-claude-client.php';
require_once SMARTBOT_ASSISTANT_DIR . 'includes/class-gemini-client.php';
require_once SMARTBOT_ASSISTANT_DIR . 'includes/class-openai-client.php';
require_once SMARTBOT_ASSISTANT_DIR . 'includes/class-openrouter-client.php';
require_once SMARTBOT_ASSISTANT_DIR . 'includes/class-api-handler.php';
require_once SMARTBOT_ASSISTANT_DIR . 'admin/class-admin-ui.php';

register_activation_hook(__FILE__, ['SmartBot\Logger', 'create_log_table']);

// Activation & Deactivation Hooks
register_activation_hook(__FILE__, 'smartbot_assistant_activate');
register_deactivation_hook(__FILE__, 'smartbot_assistant_deactivate');

function smartbot_assistant_activate() {
    flush_rewrite_rules(); // for CPT
}

function smartbot_assistant_deactivate() {
    flush_rewrite_rules();
}

// Initialize plugin
add_action('plugins_loaded', 'smartbot_assistant_init');

function smartbot_assistant_init() {
    load_plugin_textdomain('smartbot-assistant', false, dirname(SMARTBOT_ASSISTANT_BASENAME) . '/languages');

    // Initialize components
    \SmartBot\Logger::init();
    \SmartBot\FAQ_Manager::init();
    \SmartBot\Admin_UI::init();
    \SmartBot\API_Handler::init();
    \SmartBot\Shortcodes::init();
    \SmartBot\Chatbot_Loader::init();
}


// smartbot-assistant/
// │
// ├── smartbot-assistant.php            # Main plugin file
// ├── uninstall.php                     # Cleanup on uninstall
// ├── readme.txt                        # WP.org readme
// │
// ├── assets/
// │   ├── css/
// │   │   └── chatbot.css
// │   └── js/
// │       └── chatbot.js
// │
// ├── admin/
// │   ├── class-admin-ui.php            # Admin page, menu, form handlers
// │   └── views/
// │       └── settings-page.php
// │
// ├── includes/
// │   ├── class-chatbot-loader.php      # Bootstraps chatbot
// │   ├── class-faq-manager.php         # CPT for FAQs
// │   ├── class-api-handler.php         # OpenAI or Claude API integration
// │   ├── class-shortcodes.php          # Chatbot UI shortcode
// │   └── class-helpers.php             # Misc utilities (logging, sanitization)
// │
// ├── languages/
// │   └── smartbot-assistant.pot
// │
// └── templates/
//     └── chatbot-window.php            # HTML layout for chatbot UI