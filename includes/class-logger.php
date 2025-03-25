<?php
namespace SmartBot;

defined('ABSPATH') || exit;

class Logger {

    public static function init() {
        
    }

    public static function create_log_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'smartbot_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            query TEXT NOT NULL,
            response_type VARCHAR(20) NOT NULL,
            response TEXT,
            faq_id BIGINT(20),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function log_query($query, $response_type, $response = '', $faq_id = null) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'smartbot_logs',
            [
                'query'         => $query,
                'response_type' => $response_type, // 'faq' or 'ai'
                'response'      => $response,
                'faq_id'        => $faq_id,
                'created_at'    => current_time('mysql'),
            ],
            [
                '%s', '%s', '%s', '%s', '%d', '%s'
            ]
        );
    }
}
