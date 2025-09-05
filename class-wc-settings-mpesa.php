<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Ensure WooCommerce is active
if (!class_exists('WooCommerce')) return;

// ✅ Define the settings page class
if (class_exists('WC_Settings_Page') && !class_exists('WC_Settings_Mpesa')) {

    class WC_Settings_Mpesa extends WC_Settings_Page {

        public function __construct() {
            $this->id    = 'mpesa_gateway'; // Unique tab ID
            $this->label = __('M-Pesa Gateway', 'woocommerce'); // Tab label
            parent::__construct();
        }

        public function get_settings() {
            $settings = [
                [
                    'title' => __('M-Pesa API Credentials', 'woocommerce'),
                    'type'  => 'title',
                    'id'    => 'mpesa_api_settings'
                ],
                [
                    'title'    => __('Consumer Key', 'woocommerce'),
                    'id'       => 'mpesa_consumer_key',
                    'type'     => 'text',
                    'desc_tip' => true,
                    'default'  => ''
                ],
                [
                    'title'    => __('Consumer Secret', 'woocommerce'),
                    'id'       => 'mpesa_consumer_secret',
                    'type'     => 'password',
                    'desc_tip' => true,
                    'default'  => ''
                ],
                [
                    'title'    => __('Shortcode', 'woocommerce'),
                    'id'       => 'mpesa_shortcode',
                    'type'     => 'text',
                    'default'  => ''
                ],
                [
                    'title'    => __('Passkey', 'woocommerce'),
                    'id'       => 'mpesa_passkey',
                    'type'     => 'text',
                    'default'  => ''
                ],
                [
                    'type' => 'sectionend',
                    'id'   => 'mpesa_api_settings'
                ]
            ];

            return apply_filters('woocommerce_get_settings_' . $this->id, $settings);
        }

        public function output() {
            WC_Admin_Settings::output_fields($this->get_settings());
        }

        public function save() {
            WC_Admin_Settings::save_fields($this->get_settings());
        }
    }

    // ✅ Register the tab after WooCommerce is loaded
    add_filter('woocommerce_get_settings_pages', function($pages) {
        $pages[] = new WC_Settings_Mpesa();
        return $pages;
    });
}