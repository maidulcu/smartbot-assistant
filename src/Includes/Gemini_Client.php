<?php
namespace SmartBot\Includes;

defined('ABSPATH') || exit;

class Gemini_Client {

    public static function get_response($query) {
        $api_key = get_option('smartbot_gemini_api_key');
        if (!$api_key) {
            return false;
        }

        $model = get_option('smartbot_gemini_model', 'gemini-pro');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

        $body = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $query]
                    ]
                ]
            ]
        ]);

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => $body,
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            error_log('[SmartBot] Gemini_Client error ' . $response);
            return false;
        }
       
        $data = json_decode(wp_remote_retrieve_body($response), true);
        error_log('[SmartBot] Gemini_Client response: ' . print_r($data, true));
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? false;
    }
}
