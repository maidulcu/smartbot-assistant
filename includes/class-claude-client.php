<?php
namespace SmartBot;

defined('ABSPATH') || exit;

class Claude_Client {

    public static function get_response($query) {
        $api_key = get_option('smartbot_claude_api_key');
        if (!$api_key) {
            return false;
        }

        $model = get_option('smartbot_claude_model', 'claude-3-haiku-20240307');

        $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
            'headers' => [
                'Content-Type'    => 'application/json',
                'x-api-key'       => $api_key,
                'anthropic-version' => '2023-06-01',
            ],
            'body' => json_encode([
                'model'     => $model,
                'messages'  => [
                    ['role' => 'user', 'content' => $query],
                ],
                'max_tokens' => 512,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return $data['content'][0]['text'] ?? false;
    }
}
