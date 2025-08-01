<?php
namespace SmartBot\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;

class Admin_Import {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_import_page']);
    }

    public static function register_import_page() {
        add_submenu_page(
            'smartbot-assistant-settings',
            __('Import FAQs', 'smartbot-assistant'),
            __('Import FAQs', 'smartbot-assistant'),
            'manage_options',
            'smartbot-import-faqs',
            [__CLASS__, 'render_import_page']
        );
    }

    public static function render_import_page() {
        if (isset($_POST['smartbot_import_submit']) && check_admin_referer('smartbot_import_faqs', 'smartbot_import_nonce')) {
            self::handle_import();
        }

        include SMARTBOT_ASSISTANT_DIR . 'admin/views/import-page.php';
    }

    protected static function handle_import() {
        if (!isset($_FILES['faq_csv']) || $_FILES['faq_csv']['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="notice notice-error"><p>' . esc_html__('File upload failed.', 'smartbot-assistant') . '</p></div>';
            return;
        }

        $file = $_FILES['faq_csv']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Could not open CSV file.', 'smartbot-assistant') . '</p></div>';
            return;
        }

        $row_count = 0;
        $skipped = 0;
        $header = fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            $question = $data[0] ?? '';
            $answer = $data[1] ?? '';

            if (!$question || !$answer) {
                $skipped++;
                continue;
            }

            wp_insert_post([
                'post_type' => 'smartbot_faq',
                'post_title' => sanitize_text_field($question),
                'post_content' => sanitize_textarea_field($answer),
                'post_status' => 'publish',
            ]);
            $row_count++;
        }

        fclose($handle);

        echo '<div class="notice notice-success"><p>' .
            sprintf(esc_html__('%d FAQs imported. %d rows skipped.', 'smartbot-assistant'), $row_count, $skipped) .
            '</p></div>';
    }
}

Admin_Import::init();