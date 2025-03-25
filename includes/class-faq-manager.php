<?php
namespace SmartBot;

defined('ABSPATH') || exit;

class FAQ_Manager {

    public static function init() {
        add_action('init', [__CLASS__, 'register_faq_cpt']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_faq_meta']);
    }

    public static function register_faq_cpt() {
        $labels = [
            'name'               => __('FAQs', 'smartbot-assistant'),
            'singular_name'      => __('FAQ', 'smartbot-assistant'),
            'add_new'            => __('Add New', 'smartbot-assistant'),
            'add_new_item'       => __('Add New FAQ', 'smartbot-assistant'),
            'edit_item'          => __('Edit FAQ', 'smartbot-assistant'),
            'new_item'           => __('New FAQ', 'smartbot-assistant'),
            'view_item'          => __('View FAQ', 'smartbot-assistant'),
            'search_items'       => __('Search FAQs', 'smartbot-assistant'),
            'not_found'          => __('No FAQs found', 'smartbot-assistant'),
            'not_found_in_trash' => __('No FAQs found in Trash', 'smartbot-assistant'),
            'menu_name'          => __('SmartBot FAQs', 'smartbot-assistant'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-editor-help',
            'supports'           => ['title', 'editor', 'custom-fields'],
            'has_archive'        => false,
            'exclude_from_search'=> true,
            'publicly_queryable' => false,
            'show_in_rest'       => true,
        ];

        register_post_type('smartbot_faq', $args);
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'smartbot_faq_keywords',
            __('FAQ Keywords', 'smartbot-assistant'),
            [__CLASS__, 'render_keywords_metabox'],
            'smartbot_faq',
            'normal',
            'default'
        );
    }

    public static function render_keywords_metabox($post) {
        $keywords = get_post_meta($post->ID, '_smartbot_faq_keywords', true);
        wp_nonce_field('smartbot_faq_meta_nonce', 'smartbot_faq_nonce');
        echo '<label for="smartbot_faq_keywords">' . esc_html__('Enter keywords/aliases separated by commas', 'smartbot-assistant') . '</label>';
        echo '<input type="text" id="smartbot_faq_keywords" name="smartbot_faq_keywords" value="' . esc_attr($keywords) . '" style="width:100%;" />';
    }

    public static function save_faq_meta($post_id) {
        if (!isset($_POST['smartbot_faq_nonce']) || !wp_verify_nonce($_POST['smartbot_faq_nonce'], 'smartbot_faq_meta_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (isset($_POST['post_type']) && 'smartbot_faq' === $_POST['post_type']) {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }

        if (isset($_POST['smartbot_faq_keywords'])) {
            $keywords = sanitize_text_field($_POST['smartbot_faq_keywords']);
            update_post_meta($post_id, '_smartbot_faq_keywords', $keywords);
        }
    }
}
