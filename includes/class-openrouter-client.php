<?php
namespace SmartBot;

defined('ABSPATH') || exit;

class OpenRouter_Client {

    public static function get_response($query) {
        $api_key = get_option('smartbot_openrouter_api_key');
        if (!$api_key) {
            return false;
        }

        $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body' => json_encode([
                'model' => get_option('smartbot_openrouter_model', 'openai/gpt-3.5-turbo'),
                'messages' => [
                    ['role' => 'user', 'content' => $query],
                ],
                'temperature' => 0.7,
                'max_tokens'  => 200,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('[SmartBot] OpenRouter request failed: ' . $response->get_error_message());
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        error_log('[SmartBot] OpenRouter response: ' . print_r($data, true));

        return $data['choices'][0]['message']['content'] ?? false;
    }
}