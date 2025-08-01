<?php
namespace SmartBot\Includes;

defined('ABSPATH') || exit;

class HuggingFace_Client {

    public static function get_response($query) {
        $api_key = get_option('smartbot_huggingface_api_key');
        $model = get_option('smartbot_huggingface_model', 'tiiuae/falcon-7b');

        if (!$api_key || !$model) {
            return false;
        }

        $response = wp_remote_post("https://api-inference.huggingface.co/models/{$model}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => json_encode(['inputs' => $query]),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            error_log('[SmartBot] HuggingFace request error: ' . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body[0]['generated_text'])) {
            return $body[0]['generated_text'];
        } elseif (isset($body['generated_text'])) {
            return $body['generated_text'];
        }

        return 'No response from Hugging Face model.';
    }
}
