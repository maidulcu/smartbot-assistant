<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1><?php esc_html_e('Import FAQs from CSV', 'smartbot-assistant'); ?></h1>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('smartbot_import_faqs', 'smartbot_import_nonce'); ?>
        <p>
            <label for="faq_csv"><?php esc_html_e('Upload CSV File (question, answer):', 'smartbot-assistant'); ?></label><br>
            <input type="file" name="faq_csv" accept=".csv" required>
        </p>
        <p>
            <input type="submit" name="smartbot_import_submit" class="button button-primary" value="<?php esc_attr_e('Import FAQs', 'smartbot-assistant'); ?>">
        </p>
    </form>
</div>