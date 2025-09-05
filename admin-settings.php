<?php
if (!defined('ABSPATH')) exit;

// Register admin menu
add_action('admin_menu', 'mpesa_plugin_menu');
function mpesa_plugin_menu() {
    add_menu_page(
        'M-Pesa Settings',
        'M-Pesa',
        'manage_options',
        'mpesa-settings',
        'mpesa_settings_page',
        'dashicons-smartphone',
        56
    );
}

// Register settings
add_action('admin_init', 'mpesa_register_settings');
function mpesa_register_settings() {
    register_setting('mpesa_settings', 'mpesa_consumer_key');
    register_setting('mpesa_settings', 'mpesa_consumer_secret');
    register_setting('mpesa_settings', 'mpesa_shortcode');
    register_setting('mpesa_settings', 'mpesa_passkey');

    add_settings_section('mpesa_api', 'API Credentials', null, 'mpesa-settings');

    add_settings_field('mpesa_consumer_key', 'Consumer Key', function() {
        echo '<input type="text" name="mpesa_consumer_key" value="' . esc_attr(get_option('mpesa_consumer_key')) . '" class="regular-text" />';
    }, 'mpesa-settings', 'mpesa_api');

    add_settings_field('mpesa_consumer_secret', 'Consumer Secret', function() {
        echo '<input type="password" name="mpesa_consumer_secret" value="' . esc_attr(get_option('mpesa_consumer_secret')) . '" class="regular-text" />';
    }, 'mpesa-settings', 'mpesa_api');

    add_settings_field('mpesa_shortcode', 'Shortcode', function() {
        echo '<input type="text" name="mpesa_shortcode" value="' . esc_attr(get_option('mpesa_shortcode')) . '" class="regular-text" />';
    }, 'mpesa-settings', 'mpesa_api');

    add_settings_field('mpesa_passkey', 'Passkey', function() {
        echo '<input type="text" name="mpesa_passkey" value="' . esc_attr(get_option('mpesa_passkey')) . '" class="regular-text" />';
    }, 'mpesa-settings', 'mpesa_api');
}

// Admin settings page
function mpesa_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    echo '<div class="wrap"><h1>M-Pesa Plugin Settings</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('mpesa_settings');
    do_settings_sections('mpesa-settings');
    submit_button();

    echo '<p><input type="submit" name="verify_mpesa" value="Verify Credentials" class="button-secondary" /></p>';

    if (isset($_POST['verify_mpesa'])) {
        mpesa_verify_credentials();
    }

    echo '</form></div>';
}

// Optional verification logic
function mpesa_verify_credentials() {
    echo '<div class="notice notice-info"><p>Verification logic not yet implemented.</p></div>';
}