<?php
namespace SmartBot\Includes;

defined('ABSPATH') || exit;

class Shortcodes {

    public static function init() {
        add_shortcode('smartbot_chat', [__CLASS__, 'render_chatbot']);
    }

    public static function render_chatbot() {
        ob_start();
        ?>
        <div id="smartbot-chatbot-container">
            <div id="smartbot-chat-window">
                <div id="smartbot-messages"></div>
                <input type="text" id="smartbot-user-input" placeholder="Type your question..." />
            </div>
            <button id="smartbot-toggle-button">💬</button>
        </div>
        <?php
        return ob_get_clean();
    }
}