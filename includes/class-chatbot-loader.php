<?php
namespace SmartBot;

defined('ABSPATH') || exit;

class Chatbot_Loader {

    public static function init() {
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_action('wp_footer', [__CLASS__, 'inject_chatbot_ui']);
    }

    public static function enqueue_assets() {
        if (!get_option('smartbot_assistant_enabled')) {
            return;
        }

        wp_enqueue_style('smartbot-chatbot', SMARTBOT_ASSISTANT_URL . 'assets/css/chatbot.css', [], SMARTBOT_ASSISTANT_VERSION);
        wp_enqueue_script('smartbot-chatbot', SMARTBOT_ASSISTANT_URL . 'assets/js/chatbot.js', ['jquery'], SMARTBOT_ASSISTANT_VERSION, true);

        wp_localize_script('smartbot-chatbot', 'SmartBotSettings', [
            'welcomeMessage' => get_option('smartbot_assistant_welcome_message', 'Hi! How can I help you today?'),
            'ajaxUrl'        => admin_url('admin-ajax.php'),
            'enabled'        => (bool) get_option('smartbot_assistant_enabled'),
        ]);
    }

    public static function inject_chatbot_ui() {
        if (!get_option('smartbot_assistant_enabled')) {
            return;
        }

        echo '<div id="smartbot-chatbot-container" class="smartbot-chatbot-hidden">';
        echo '<div id="smartbot-chat-window"><div id="smartbot-messages"></div><input type="text" id="smartbot-user-input" placeholder="Type your question..." /></div>';
        echo '<button id="smartbot-toggle-button">💬</button>';
        echo '</div>';
    }
}
