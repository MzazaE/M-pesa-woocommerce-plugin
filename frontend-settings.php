<?php
add_shortcode('mpesa_settings_form', 'mpesa_render_frontend_form');

function mpesa_render_frontend_form() {
    if (!current_user_can('manage_options')) {
        return '<p>You do not have permission to view this form.</p>';
    }

    ob_start();
    ?>
    <form method="post">
        <label>Consumer Key:</label><br>
        <input type="text" name="mpesa_consumer_key" value="<?php echo esc_attr(get_option('mpesa_consumer_key')); ?>" /><br><br>

        <label>Consumer Secret:</label><br>
        <input type="text" name="mpesa_consumer_secret" value="<?php echo esc_attr(get_option('mpesa_consumer_secret')); ?>" /><br><br>

        <label>Shortcode:</label><br>
        <input type="text" name="mpesa_shortcode" value="<?php echo esc_attr(get_option('mpesa_shortcode')); ?>" /><br><br>

        <label>Passkey:</label><br>
        <input type="text" name="mpesa_passkey" value="<?php echo esc_attr(get_option('mpesa_passkey')); ?>" /><br><br>

        <input type="submit" name="save_mpesa_settings" value="Save Settings" />
        <input type="submit" name="verify_mpesa" value="Verify Credentials" />
    </form>
    <?php

    if (isset($_POST['save_mpesa_settings'])) {
        update_option('mpesa_consumer_key', sanitize_text_field($_POST['mpesa_consumer_key']));
        update_option('mpesa_consumer_secret', sanitize_text_field($_POST['mpesa_consumer_secret']));
        update_option('mpesa_shortcode', sanitize_text_field($_POST['mpesa_shortcode']));
        update_option('mpesa_passkey', sanitize_text_field($_POST['mpesa_passkey']));
        echo '<p><strong>✅ Settings saved successfully.</strong></p>';
    }

    if (isset($_POST['verify_mpesa'])) {
        mpesa_verify_credentials();
    }

    return ob_get_clean();
}