<?php
namespace SmartBot;

defined('ABSPATH') || exit;

class OpenAI_Client {

    public static function get_response($query) {
        $api_key = get_option('smartbot_assistant_api_key');
        if (!$api_key) {
            return false;
        }

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body' => json_encode([
                'model'    => get_option('smartbot_openai_model', 'gpt-3.5-turbo'),
                'messages' => [
                    ['role' => 'user', 'content' => $query],
                ],
                'temperature' => 0.7,
                'max_tokens'  => 200,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return $data['choices'][0]['message']['content'] ?? false;
    }
}
