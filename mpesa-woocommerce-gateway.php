<?php
/**
 * Plugin Name: M-Pesa WooCommerce Gateway
 * Description: Custom M-Pesa payment gateway for WooCommerce.
 * Version: 1.0.0
 * Author: Erickson Mzaza
 * URL: www.linkedin.com/in/erickson-mzaza-91ba851b0
 * Text Domain: mpesa-woocommerce-gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// ✅ Load translations properly (WordPress 6.7+ safe)
add_action('init', function() {
    load_plugin_textdomain('mpesa-woocommerce-gateway', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// ✅ Load STK Push logic
require_once plugin_dir_path(__FILE__) . 'mpesa-api.php';
require_once plugin_dir_path(__FILE__) . 'admin-settings.php';

// ✅ Register the gateway safely
if ( ! function_exists('erickson_mpesa_init') ) {
    function erickson_mpesa_init() {
        if ( ! class_exists('WC_Payment_Gateway') ) return;

        class WC_Gateway_Mpesa extends WC_Payment_Gateway {
            public function __construct() {
                $this->id                 = 'mpesa';
                $this->method_title       = 'M-Pesa';
                $this->method_description = 'Pay via M-Pesa STK Push';
                $this->has_fields         = true;

                $this->init_form_fields();
                $this->init_settings();

                $this->title            = $this->get_option('title');
                $this->consumer_key     = $this->get_option('consumer_key');
                $this->consumer_secret  = $this->get_option('consumer_secret');
                $this->shortcode        = $this->get_option('shortcode');
                $this->passkey          = $this->get_option('passkey');

                add_action('woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ]);
            }

            public function init_form_fields() {
                $this->form_fields = [
                    'enabled' => [
                        'title'   => 'Enable/Disable',
                        'type'    => 'checkbox',
                        'label'   => 'Enable M-Pesa Gateway',
                        'default' => 'yes'
                    ],
                    'title' => [
                        'title'       => 'Title',
                        'type'        => 'text',
                        'description' => 'Payment method title shown to customers',
                        'default'     => 'M-Pesa',
                        'desc_tip'    => true,
                    ],
                    'environment' => [
                        'title'       => 'Environment',
                        'type'        => 'select',
                        'description' => 'Choose whether to use live or sandbox endpoints',
                        'default'     => 'sandbox',
                        'options'     => [
                            'sandbox' => 'Sandbox (Testing)',
                            'live'    => 'Live (Production)'
                        ]
                    ],
                    'consumer_key' => [
                        'title'       => 'Consumer Key',
                        'type'        => 'text',
                        'description' => 'Your Safaricom API Consumer Key',
                        'default'     => '',
                    ],
                    'consumer_secret' => [
                        'title'       => 'Consumer Secret',
                        'type'        => 'text',
                        'description' => 'Your Safaricom API Consumer Secret',
                        'default'     => '',
                    ],
                    'shortcode' => [
                        'title'       => 'Shortcode',
                        'type'        => 'text',
                        'description' => 'Your M-Pesa Paybill or Till Number',
                        'default'     => '',
                    ],
                    'passkey' => [
                        'title'       => 'Passkey',
                        'type'        => 'text',
                        'description' => 'Your M-Pesa API Passkey',
                        'default'     => '',
                    ],
                ];
            }

            public function payment_fields() {
                echo '<p>' . esc_html__('Enter your M-Pesa phone number to receive a payment prompt:', 'mpesa-woocommerce-gateway') . '</p>';
                echo '<fieldset>';
                echo '<label for="mpesa_phone">' . esc_html__('Phone Number', 'mpesa-woocommerce-gateway') . '<span class="required">*</span></label>';
                echo '<input type="text" name="mpesa_phone" id="mpesa_phone" placeholder="2547XXXXXXXX" autocomplete="tel" value="' . (isset($_POST['mpesa_phone']) ? esc_attr($_POST['mpesa_phone']) : '') . '" required />';
                echo '</fieldset>';
            }

            public function process_payment($order_id) {
                $order = wc_get_order($order_id);

                if (isset($_POST['mpesa_phone'])) {
                    $phone = sanitize_text_field($_POST['mpesa_phone']);
                    update_post_meta($order_id, '_mpesa_phone', $phone);
                    $order->add_order_note('📱 M-Pesa phone captured: ' . $phone);
                } else {
                    $order->add_order_note('⚠️ M-Pesa phone number missing during checkout.');
                }

                $order->update_status('on-hold', 'Awaiting M-Pesa payment');
                wc_reduce_stock_levels($order_id);
                WC()->cart->empty_cart();

                return [
                    'result'   => 'success',
                    'redirect' => $this->get_return_url($order)
                ];
            }
        }

        add_filter('woocommerce_payment_gateways', function($gateways) {
            $gateways[] = 'WC_Gateway_Mpesa';
            return $gateways;
        });
    }

    add_action('plugins_loaded', 'erickson_mpesa_init', 11);
}