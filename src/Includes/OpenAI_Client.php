<?php
namespace SmartBot\Includes;

defined('ABSPATH') || exit;

class OpenAI_Client {

    public static function get_response($query) {
        $api_key = get_option('smartbot_assistant_api_key');
        $assistant_id = get_option('smartbot_openai_assistant_id');

        if (!$api_key || !$assistant_id) {
            return false;
        }

        // Step 1: Create thread
        $thread_response = wp_remote_post('https://api.openai.com/v1/threads', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
                'OpenAI-Beta'   => 'assistants=v2',
            ],
            'body' => json_encode([]),
        ]);
        if (is_wp_error($thread_response)) {
            error_log('[SmartBot] WP_Error (thread): ' . $thread_response->get_error_message());
            return 'Thread creation failed (WP error).';
        }
        error_log('[SmartBot] Thread creation body: ' . wp_remote_retrieve_body($thread_response));
        $thread_data = json_decode(wp_remote_retrieve_body($thread_response));
        $thread_id = $thread_data->id ?? null;

        if (!$thread_id) return 'Failed to create thread.';

        // Step 2: Add user message
        wp_remote_post("https://api.openai.com/v1/threads/{$thread_id}/messages", [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
                'OpenAI-Beta'   => 'assistants=v2',
            ],
            'body' => json_encode([
                'role'    => 'user',
                'content' => $query,
            ]),
        ]);

        // Step 3: Run assistant
        $run_response = wp_remote_post("https://api.openai.com/v1/threads/{$thread_id}/runs", [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
                'OpenAI-Beta'   => 'assistants=v2',
            ],
            'body' => json_encode([
                'assistant_id' => $assistant_id,
            ]),
        ]);
        $run_data = json_decode(wp_remote_retrieve_body($run_response));
        $run_id = $run_data->id ?? null;

        if (!$run_id) return 'Failed to run assistant.';

        // Step 4: Poll for result
        $max_retries = 10;
        do {
            sleep(2);
            $status_response = wp_remote_get("https://api.openai.com/v1/threads/{$thread_id}/runs/{$run_id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'OpenAI-Beta'   => 'assistants=v2',
                ],
            ]);
            $status_data = json_decode(wp_remote_retrieve_body($status_response));
            $status = $status_data->status ?? null;
            $max_retries--;
        } while ($status !== 'completed' && $max_retries > 0);

        if ($status !== 'completed') return 'Assistant timed out.';

        // Step 5: Fetch response
        $messages_response = wp_remote_get("https://api.openai.com/v1/threads/{$thread_id}/messages", [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'OpenAI-Beta'   => 'assistants=v2',
            ],
        ]);
        $messages_data = json_decode(wp_remote_retrieve_body($messages_response));

        foreach ($messages_data->data as $msg) {
            if ($msg->role === 'assistant') {
                return $msg->content[0]->text->value ?? '';
            }
        }

        return 'No response from assistant.';
    }
}
