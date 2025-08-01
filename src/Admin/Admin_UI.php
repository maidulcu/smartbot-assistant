<?php
namespace SmartBot\Admin;

defined('ABSPATH') || exit;

class Admin_UI {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_settings_page']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_notices', [__CLASS__, 'maybe_show_ai_key_notice']);
    }

    public static function add_settings_page() {
        add_menu_page(
            __('SmartBot Assistant', 'smartbot-assistant'),
            __('SmartBot', 'smartbot-assistant'),
            'manage_options',
            'smartbot-assistant-settings',
            [__CLASS__, 'render_settings_page'],
            'dashicons-format-chat',
            60
        );

        add_submenu_page(
            'smartbot-assistant-settings',
            __('Query Logs', 'smartbot-assistant'),
            __('Query Logs', 'smartbot-assistant'),
            'manage_options',
            'smartbot-assistant-logs',
            [__CLASS__, 'render_logs_page']
        );
    }

    public static function register_settings() {
        register_setting('smartbot_assistant_options', 'smartbot_assistant_enabled');
        register_setting('smartbot_assistant_options', 'smartbot_assistant_welcome_message');
        register_setting('smartbot_assistant_options', 'smartbot_assistant_api_key');
        register_setting('smartbot_assistant_options', 'smartbot_ai_provider');
        register_setting('smartbot_assistant_options', 'smartbot_claude_api_key');
        register_setting('smartbot_assistant_options', 'smartbot_gemini_api_key');
        register_setting('smartbot_assistant_options', 'smartbot_openrouter_api_key');
        register_setting('smartbot_assistant_options', 'smartbot_openai_model');
        register_setting('smartbot_assistant_options', 'smartbot_openai_assistant_id');
        register_setting('smartbot_assistant_options', 'smartbot_claude_model');
        register_setting('smartbot_assistant_options', 'smartbot_gemini_model');
        register_setting('smartbot_assistant_options', 'smartbot_openrouter_model');
        register_setting('smartbot_assistant_options', 'smartbot_huggingface_api_key');
        register_setting('smartbot_assistant_options', 'smartbot_huggingface_model');
    }

    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('SmartBot Assistant Settings', 'smartbot-assistant'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('smartbot_assistant_options'); ?>
                <?php do_settings_sections('smartbot_assistant_options'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Enable Chatbot', 'smartbot-assistant'); ?></th>
                        <td>
                            <input type="checkbox" name="smartbot_assistant_enabled" value="1" <?php checked(1, get_option('smartbot_assistant_enabled'), true); ?> />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Welcome Message', 'smartbot-assistant'); ?></th>
                        <td>
                            <input type="text" name="smartbot_assistant_welcome_message" value="<?php echo esc_attr(get_option('smartbot_assistant_welcome_message')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('AI Provider', 'smartbot-assistant'); ?></th>
                        <td>
                            <select name="smartbot_ai_provider">
                                <option value="openai" <?php selected(get_option('smartbot_ai_provider'), 'openai'); ?>>OpenAI</option>
                                <option value="claude" <?php selected(get_option('smartbot_ai_provider'), 'claude'); ?>>Claude</option>
                                <option value="gemini" <?php selected(get_option('smartbot_ai_provider'), 'gemini'); ?>>Gemini</option>
                                <option value="openrouter" <?php selected(get_option('smartbot_ai_provider'), 'openrouter'); ?>>OpenRouter</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('OpenAI API Key', 'smartbot-assistant'); ?></th>
                        <td>
                            <input type="text" name="smartbot_assistant_api_key" value="<?php echo esc_attr(get_option('smartbot_assistant_api_key')); ?>" class="regular-text" />
                            <p class="description">Get your key at <a href="https://platform.openai.com/account/api-keys" target="_blank">OpenAI API Keys</a>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('OpenAI Model', 'smartbot-assistant'); ?></th>
                        <td>
                            <select name="smartbot_openai_model">
                                <option value="gpt-3.5-turbo" <?php selected(get_option('smartbot_openai_model'), 'gpt-3.5-turbo'); ?>>gpt-3.5-turbo</option>
                                <option value="gpt-4" <?php selected(get_option('smartbot_openai_model'), 'gpt-4'); ?>>gpt-4</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('OpenAI Assistant ID', 'smartbot-assistant'); ?></th>
                        <td>
                            <input type="text" name="smartbot_openai_assistant_id" value="<?php echo esc_attr(get_option('smartbot_openai_assistant_id')); ?>" class="regular-text" />
                            <p class="description">Paste your OpenAI Assistant ID here (e.g., asst_abc123...).</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Claude API Key', 'smartbot-assistant'); ?></th>
                        <td>
                            <input type="text" name="smartbot_claude_api_key" value="<?php echo esc_attr(get_option('smartbot_claude_api_key')); ?>" class="regular-text" />
                            <p class="description">Get your Claude key from <a href="https://console.anthropic.com/settings/keys" target="_blank">Anthropic Console</a>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Claude Model', 'smartbot-assistant'); ?></th>
                        <td>
                            <select name="smartbot_claude_model">
                                <option value="claude-3-haiku-20240307" <?php selected(get_option('smartbot_claude_model'), 'claude-3-haiku-20240307'); ?>>Claude 3 Haiku</option>
                                <option value="claude-3-sonnet-20240229" <?php selected(get_option('smartbot_claude_model'), 'claude-3-sonnet-20240229'); ?>>Claude 3 Sonnet</option>
                                <option value="claude-3-opus-20240229" <?php selected(get_option('smartbot_claude_model'), 'claude-3-opus-20240229'); ?>>Claude 3 Opus</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Gemini API Key', 'smartbot-assistant'); ?></th>
                        <td>
                            <input type="text" name="smartbot_gemini_api_key" value="<?php echo esc_attr(get_option('smartbot_gemini_api_key')); ?>" class="regular-text" />
                            <p class="description">Create Gemini keys at <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Gemini Model', 'smartbot-assistant'); ?></th>
                        <td>
                            <select name="smartbot_gemini_model">
                                <option value="gemini-pro" <?php selected(get_option('smartbot_gemini_model'), 'gemini-pro'); ?>>Gemini Pro</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('OpenRouter API Key', 'smartbot-assistant'); ?></th>
                        <td>
                            <input type="text" name="smartbot_openrouter_api_key" value="<?php echo esc_attr(get_option('smartbot_openrouter_api_key')); ?>" class="regular-text" />
                            <p class="description">Get your key at <a href="https://openrouter.ai/keys" target="_blank">OpenRouter API Keys</a>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('OpenRouter Model', 'smartbot-assistant'); ?></th>
                        <td>
                            <select name="smartbot_openrouter_model">
                                <option value="openai/gpt-3.5-turbo" <?php selected(get_option('smartbot_openrouter_model'), 'openai/gpt-3.5-turbo'); ?>>OpenAI GPT-3.5</option>
                                <option value="openai/gpt-4" <?php selected(get_option('smartbot_openrouter_model'), 'openai/gpt-4'); ?>>OpenAI GPT-4</option>
                                <option value="mistralai/mixtral-8x7b-instruct" <?php selected(get_option('smartbot_openrouter_model'), 'mistralai/mixtral-8x7b-instruct'); ?>>Mixtral</option>
                                <option value="anthropic/claude-3-opus" <?php selected(get_option('smartbot_openrouter_model'), 'anthropic/claude-3-opus'); ?>>Claude 3 Opus</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Hugging Face API Key', 'smartbot-assistant'); ?></th>
                        <td>
                            <input type="text" name="smartbot_huggingface_api_key" value="<?php echo esc_attr(get_option('smartbot_huggingface_api_key')); ?>" class="regular-text" />
                            <p class="description">Get your token from <a href="https://huggingface.co/settings/tokens" target="_blank">Hugging Face</a>.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Hugging Face Model ID', 'smartbot-assistant'); ?></th>
                        <td>
                            <input type="text" name="smartbot_huggingface_model" value="<?php echo esc_attr(get_option('smartbot_huggingface_model')); ?>" class="regular-text" />
                            <p class="description">e.g., tiiuae/falcon-7b, mistralai/Mistral-7B-Instruct-v0.2, HuggingFaceH4/zephyr-7b-beta</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function render_logs_page() {
        global $wpdb;
        $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $table_name = $wpdb->prefix . 'smartbot_logs';

        $per_page = 20;
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $per_page;

        $where = $search_term ? $wpdb->prepare("WHERE query LIKE %s", '%' . $wpdb->esc_like($search_term) . '%') : '';
        $logs = $wpdb->get_results("SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");
        $total_pages = ceil($total_items / $per_page);

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('SmartBot Query Logs', 'smartbot-assistant') . '</h1>';
        echo '<form method="get" style="margin-bottom:20px;">
                <input type="hidden" name="page" value="smartbot-assistant-logs" />
                <input type="text" name="s" value="' . esc_attr($search_term) . '" placeholder="Search queries..." />
                <input type="submit" class="button" value="Search" />
              </form>';
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>
                <th>' . esc_html__('Time', 'smartbot-assistant') . '</th>
                <th>' . esc_html__('Query', 'smartbot-assistant') . '</th>
                <th>' . esc_html__('Type', 'smartbot-assistant') . '</th>
                <th>' . esc_html__('FAQ ID', 'smartbot-assistant') . '</th>
                <th>' . esc_html__('AI Response', 'smartbot-assistant') . '</th>
              </tr></thead><tbody>';

        if ($logs) {
            foreach ($logs as $log) {
                echo '<tr>';
                echo '<td>' . esc_html($log->created_at) . '</td>';
                echo '<td>' . esc_html($log->query) . '</td>';
                echo '<td>' . esc_html(ucfirst($log->response_type)) . '</td>';
                echo '<td>' . esc_html($log->id ?: '-') . '</td>';
                echo '<td>';

                $short_text = wp_trim_words($log->response, 20, '...');
                $full_text = esc_textarea($log->response);
                $unique_id = 'ai-response-' . $log->id;

                echo '<div>';
                echo '<div id="' . esc_attr($unique_id) . '-short">' . esc_html($short_text) . ' ';
                echo '<a href="javascript:void(0);" onclick="document.getElementById(\'' . esc_attr($unique_id) . '-full\').style.display=\'block\'; this.style.display=\'none\';">[Show more]</a>';
                echo '</div>';
                echo '<div id="' . esc_attr($unique_id) . '-full" style="display:none;">' . nl2br(esc_html($full_text)) . '</div>';
                echo '</div>';

                $create_faq_url = admin_url('post-new.php?post_type=smartbot_faq&faq_query=' . urlencode($log->query) . '&faq_answer=' . urlencode($log->response));
                echo '<p><a href="' . esc_url($create_faq_url) . '" class="button button-small">Create FAQ</a></p>';

                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="5">' . esc_html__('No logs found.', 'smartbot-assistant') . '</td></tr>';
        }

        echo '</tbody></table>';

        echo '<div class="tablenav"><div class="tablenav-pages">';
        $base_url = remove_query_arg('paged');
        $base_url = add_query_arg('paged', '%#%', $base_url);

        echo paginate_links([
            'base'      => $base_url,
            'format'    => '',
            'current'   => $paged,
            'total'     => $total_pages,
            'prev_text' => __('« Previous'),
            'next_text' => __('Next »'),
            'type'      => 'plain'
        ]);
        echo '</div></div>';

        echo '</div>';
    }

    public static function maybe_show_ai_key_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'smartbot-assistant') === false) {
            return;
        }

        $provider = get_option('smartbot_ai_provider');
        $notice = '';

        switch ($provider) {
            case 'openai':
                if (!get_option('smartbot_assistant_api_key')) {
                    $notice = __('OpenAI is selected but no API key is provided.', 'smartbot-assistant');
                }
                break;
            case 'claude':
                if (!get_option('smartbot_claude_api_key')) {
                    $notice = __('Claude is selected but no API key is provided.', 'smartbot-assistant');
                }
                break;
            case 'gemini':
                if (!get_option('smartbot_gemini_api_key')) {
                    $notice = __('Gemini is selected but no API key is provided.', 'smartbot-assistant');
                }
                break;
            case 'openrouter':
                if (!get_option('smartbot_openrouter_api_key')) {
                    $notice = __('OpenRouter is selected but no API key is provided.', 'smartbot-assistant');
                }
                break;
        }

        if ($notice) {
            echo '<div class="notice notice-warning"><p>' . esc_html($notice) . '</p></div>';
        }
    }
}

add_filter('default_title', function ($title, $post) {
    if ($post->post_type === 'smartbot_faq' && isset($_GET['faq_query'])) {
        $title = sanitize_text_field(wp_unslash($_GET['faq_query']));
    }
    return $title;
}, 10, 2);

add_filter('default_content', function ($content, $post) {
    if ($post->post_type === 'smartbot_faq' && isset($_GET['faq_answer'])) {
        $content = wp_kses_post(wp_unslash($_GET['faq_answer']));
    }
    return $content;
}, 10, 2);