<?php
namespace SmartBot\Admin;

defined('ABSPATH') || exit;

class Admin_Train {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_train_page']);
    }

    public static function add_train_page() {
        add_submenu_page(
            'smartbot-assistant-settings',
            __('Train Assistant', 'smartbot-assistant'),
            __('Train Assistant', 'smartbot-assistant'),
            'manage_options',
            'smartbot-assistant-train',
            [__CLASS__, 'render_train_page']
        );
    }

    public static function render_train_page() {
        $free_post_types = ['smartbot_faq', 'product'];

        if (isset($_POST['smartbot_train_submit'])) {
            $selected_post_type = sanitize_text_field($_POST['smartbot_train_post_type'] ?? 'smartbot_faq');
            if (!in_array($selected_post_type, $free_post_types) && !get_option('smartbot_is_pro')) {
                echo '<div class="notice notice-error"><p>' . esc_html__('This content type requires a Pro license.', 'smartbot-assistant') . '</p></div>';
                return;
            }
        }

        if (isset($_POST['smartbot_train_submit']) && check_admin_referer('smartbot_train_assistant', 'smartbot_train_nonce')) {
            $selected_post_type = sanitize_text_field($_POST['smartbot_train_post_type'] ?? 'smartbot_faq');
            $posts = get_posts([
                'post_type' => $selected_post_type,
                'numberposts' => -1,
                'post_status' => 'publish',
            ]);

            $selected_fields = $_POST['smartbot_fields'] ?? ['title', 'content'];

            $content = '';
            foreach ($posts as $item) {
                $q_parts = [];
                $a_parts = [];

                if (in_array('title', $selected_fields)) {
                    $q_parts[] = strip_tags($item->post_title);
                }

                if (in_array('excerpt', $selected_fields)) {
                    $a_parts[] = strip_tags($item->post_excerpt);
                }

                if (in_array('content', $selected_fields)) {
                    $a_parts[] = strip_tags(apply_filters('the_content', $item->post_content));
                }

                if (in_array('meta', $selected_fields) && get_option('smartbot_is_pro')) {
                    $meta = get_post_meta($item->ID);
                    foreach ($meta as $key => $value) {
                        if (!is_protected_meta($key, 'post') && is_scalar($value[0])) {
                            $a_parts[] = $key . ': ' . $value[0];
                        }
                    }
                }

                if (!empty($q_parts) && !empty($a_parts)) {
                    $q = 'Q: ' . implode(' ', $q_parts);
                    $a = 'A: ' . implode(' ', $a_parts);
                    $content .= "{$q}\n{$a}\n\n";
                }
            }

            if ($content) {
                $upload_dir = wp_upload_dir();
                $file_path = $upload_dir['basedir'] . '/smartbot-faq.txt';
                file_put_contents($file_path, $content);

                $api_key = get_option('smartbot_assistant_api_key');
                $assistant_id = get_option('smartbot_openai_assistant_id');

                if ($api_key && $assistant_id) {
                    $response = wp_remote_post('https://api.openai.com/v1/files', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $api_key,
                            'OpenAI-Beta'   => 'assistants=v2',
                        ],
                        'body' => [
                            'purpose' => 'assistants',
                            'file'    => curl_file_create($file_path, 'text/plain', 'faq.txt'),
                        ],
                    ]);

                    if (is_wp_error($response)) {
                        echo '<div class="notice notice-error"><p>' . esc_html__('Upload failed.', 'smartbot-assistant') . '</p></div>';
                    } else {
                        $body = json_decode(wp_remote_retrieve_body($response), true);
                        $file_id = $body['id'] ?? '';

                        if ($file_id) {
                            wp_remote_post("https://api.openai.com/v1/assistants/{$assistant_id}", [
                                'headers' => [
                                    'Authorization' => 'Bearer ' . $api_key,
                                    'Content-Type'  => 'application/json',
                                    'OpenAI-Beta'   => 'assistants=v2',
                                ],
                                'body' => json_encode([
                                    'file_ids' => [$file_id],
                                ]),
                            ]);
                            echo '<div class="notice notice-success"><p>' . esc_html__('FAQ uploaded and linked to assistant!', 'smartbot-assistant') . '</p></div>';

                            // Save sync summary to options
                            update_option('smartbot_last_training', [
                                'post_type' => $selected_post_type,
                                'count'     => count($posts),
                                'timestamp' => time(),
                                'file_id'   => $file_id,
                            ]);
                        }
                    }
                }
            }
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Train Assistant with Content', 'smartbot-assistant'); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('smartbot_train_assistant', 'smartbot_train_nonce'); ?>
                <p><?php esc_html_e('Click the button below to prepare and send your FAQ content to your configured OpenAI Assistant for retrieval-based learning.', 'smartbot-assistant'); ?></p>
                <p>
                    <label for="smartbot_train_post_type"><?php esc_html_e('Select Content Type to Train:', 'smartbot-assistant'); ?></label>
                    <select name="smartbot_train_post_type" id="smartbot_train_post_type">
                        <option value="smartbot_faq">FAQ (Default)</option>
                        <option value="product">WooCommerce Products</option>
                        <?php
                        $custom_post_types = get_post_types(['_builtin' => false], 'objects');
                        foreach ($custom_post_types as $cpt) {
                            $disabled = in_array($cpt->name, $free_post_types) ? '' : 'disabled';
                            $label = in_array($cpt->name, $free_post_types) ? $cpt->labels->singular_name : $cpt->labels->singular_name . ' (Pro)';
                            printf('<option value="%1$s" %3$s>%2$s</option>', esc_attr($cpt->name), esc_html($label), $disabled);
                        }
                        ?>
                    </select>
                </p>
                <fieldset>
                    <legend><strong><?php esc_html_e('Select Fields to Include in Training', 'smartbot-assistant'); ?></strong></legend>
                    <label><input type="checkbox" name="smartbot_fields[]" value="title" checked> <?php esc_html_e('Title', 'smartbot-assistant'); ?></label><br>
                    <label><input type="checkbox" name="smartbot_fields[]" value="content" checked> <?php esc_html_e('Content', 'smartbot-assistant'); ?></label><br>
                    <label><input type="checkbox" name="smartbot_fields[]" value="excerpt"> <?php esc_html_e('Excerpt', 'smartbot-assistant'); ?></label><br>
                    <?php if (get_option('smartbot_is_pro')): ?>
                      <label><input type="checkbox" name="smartbot_fields[]" value="meta"> <?php esc_html_e('Custom Fields (Pro)', 'smartbot-assistant'); ?></label><br>
                    <?php endif; ?>
                </fieldset>
                <p>
                    <button type="button" class="button" id="smartbot-preview-button"><?php esc_html_e('Preview Training Content', 'smartbot-assistant'); ?></button>
                </p>
                <div id="smartbot-preview-container" style="display:none; margin-bottom: 15px;">
                    <textarea id="smartbot-preview-textarea" rows="10" style="width:100%;" readonly></textarea>
                </div>
                <p><input type="submit" name="smartbot_train_submit" class="button button-primary" value="<?php esc_attr_e('Send FAQ to Assistant', 'smartbot-assistant'); ?>"></p>
                <?php
                $last_training = get_option('smartbot_last_training');
                if ($last_training) {
                    echo '<hr><h3>' . esc_html__('Last Training Summary:', 'smartbot-assistant') . '</h3>';
                    echo '<ul>';
                    echo '<li><strong>' . esc_html__('Post Type:') . '</strong> ' . esc_html($last_training['post_type']) . '</li>';
                    echo '<li><strong>' . esc_html__('Post Count:') . '</strong> ' . esc_html($last_training['count']) . '</li>';
                    echo '<li><strong>' . esc_html__('Synced At:') . '</strong> ' . esc_html(date('Y-m-d H:i:s', $last_training['timestamp'])) . '</li>';
                    echo '<li><strong>' . esc_html__('File ID:') . '</strong> ' . esc_html($last_training['file_id']) . '</li>';
                    echo '</ul>';
                }
                ?>
            </form>
        </div>
        <script>
            const smartbotPreviewError = "<?php echo esc_js(__('No preview available or an error occurred.', 'smartbot-assistant')); ?>";

            document.addEventListener('DOMContentLoaded', function () {
                const button = document.getElementById('smartbot-preview-button');
                const textarea = document.getElementById('smartbot-preview-textarea');
                const container = document.getElementById('smartbot-preview-container');
                button.addEventListener('click', function () {
                    const form = button.closest('form');
                    const formData = new FormData(form);
                    formData.append('action', 'smartbot_preview_training_content');
                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.data && data.data.content) {
                            textarea.value = data.data.content;
                        } else {
                            textarea.value = smartbotPreviewError;
                        }
                        container.style.display = 'block';
                    });
                });
            });
        </script>
        <?php
    }
}

add_action('wp_ajax_smartbot_preview_training_content', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error();
    }

    //error_log('🔍 Preview handler triggered');

    $free_post_types = ['smartbot_faq', 'product'];
    $selected_post_type = sanitize_text_field($_POST['smartbot_train_post_type'] ?? 'smartbot_faq');
    $selected_fields = $_POST['smartbot_fields'] ?? ['title', 'content'];

    //error_log('Post type: ' . $selected_post_type);
    //error_log('Fields: ' . implode(', ', $selected_fields));

    if (!in_array($selected_post_type, $free_post_types) && !get_option('smartbot_is_pro')) {
        wp_send_json_error();
    }

    $posts = get_posts([
        'post_type' => $selected_post_type,
        'numberposts' => -1,
        'post_status' => 'publish',
    ]);

    //error_log('Posts found: ' . count($posts));

    $content = '';
    foreach ($posts as $item) {
        $q_parts = [];
        $a_parts = [];

        if (in_array('title', $selected_fields)) {
            $q_parts[] = strip_tags($item->post_title);
        }

        if (in_array('excerpt', $selected_fields)) {
            $a_parts[] = strip_tags($item->post_excerpt);
        }

        if (in_array('content', $selected_fields)) {
            $a_parts[] = strip_tags(apply_filters('the_content', $item->post_content));
        }

        if (in_array('meta', $selected_fields) && get_option('smartbot_is_pro')) {
            $meta = get_post_meta($item->ID);
            foreach ($meta as $key => $value) {
                if (!is_protected_meta($key, 'post') && is_scalar($value[0])) {
                    $a_parts[] = $key . ': ' . $value[0];
                }
            }
        }

        if (!empty($q_parts) && !empty($a_parts)) {
            $q = 'Q: ' . implode(' ', $q_parts);
            $a = 'A: ' . implode(' ', $a_parts);
            $content .= "{$q}\n{$a}\n\n";
        }
    }

    //error_log('Preview content length: ' . strlen($content));

    if (empty($content)) {
        wp_send_json_error(['message' => 'No content generated']);
    }

    wp_send_json_success(['content' => $content]);
});
