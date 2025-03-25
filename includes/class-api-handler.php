<?php
namespace SmartBot;

defined('ABSPATH') || exit;

use SmartBot\Logger;
use SmartBot\OpenAI_Client;
use SmartBot\Claude_Client;
use SmartBot\Gemini_Client;
use SmartBot\OpenRouter_Client;

class API_Handler {

    public static function init() {
        add_action('wp_ajax_smartbot_handle_query', [__CLASS__, 'handle_query']);
        add_action('wp_ajax_nopriv_smartbot_handle_query', [__CLASS__, 'handle_query']);
    }

    public static function handle_query() {
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        error_log('[SmartBot] Incoming query: ' . $query);

        if (empty($query)) {
            wp_send_json_error('Empty query');
        }

        // Try to match FAQs by keyword
        $faq_response = self::match_faq($query);
        if ($faq_response) {
            error_log('[SmartBot] Matched FAQ response');
            Logger::log_query($query, 'faq', $faq_response, $faq_id = null); // You may later update with real FAQ ID
            wp_send_json_success($faq_response);
        }

        // Fallback to AI
        $ai_response = self::get_ai_response($query);
        error_log('[SmartBot] Using AI fallback');
        if (!$ai_response) {
            error_log('[SmartBot] AI response failed or empty');
        }
        Logger::log_query($query, 'ai', $ai_response);
        wp_send_json_success($ai_response ?: 'No response available.');
    }

    private static function match_faq($query) {
        $args = [
            'post_type'      => 'smartbot_faq',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];
        $faqs = get_posts($args);

        foreach ($faqs as $faq) {
            $keywords = get_post_meta($faq->ID, '_smartbot_faq_keywords', true);
            if (!$keywords) continue;

            $keyword_array = array_map('trim', explode(',', $keywords));
            foreach ($keyword_array as $keyword) {
                if (stripos($query, $keyword) !== false) {
                    return apply_filters('the_content', $faq->post_content);
                }
            }
        }

        return false;
    }

    private static function get_ai_response($query) {
        $provider = get_option('smartbot_ai_provider', 'openai');
        error_log('[SmartBot] Selected AI provider: ' . $provider);
        $response = false;

        switch ($provider) {
            case 'claude':
                if (get_option('smartbot_claude_api_key')) {
                    error_log('[SmartBot] Sending query to Claude');
                    $response = Claude_Client::get_response($query);
                } else {
                    error_log('[SmartBot] Claude API key missing');
                }
                break;
            case 'gemini':
                if (get_option('smartbot_gemini_api_key')) {
                    error_log('[SmartBot] Sending query to Gemini');
                    $response = Gemini_Client::get_response($query);
                } else {
                    error_log('[SmartBot] Gemini API key missing');
                }
                break;
            case 'openrouter':
                if (get_option('smartbot_openrouter_api_key')) {
                    error_log('[SmartBot] Sending query to OpenRouter');
                    $response = OpenRouter_Client::get_response($query);
                } else {
                    error_log('[SmartBot] OpenRouter API key missing');
                }
                break;
            case 'openai':
            default:
                if (get_option('smartbot_assistant_api_key')) {
                    error_log('[SmartBot] Sending query to OpenAI');
                    $response = OpenAI_Client::get_response($query);
                } else {
                    error_log('[SmartBot] OpenAI API key missing');
                }
                break;
        }

        if (!$response) {
            error_log('[SmartBot] No valid AI response received.');
            return __('Sorry, our assistant is temporarily unavailable. Please try again later.', 'smartbot-assistant');
        }

        return $response;
    }
}
