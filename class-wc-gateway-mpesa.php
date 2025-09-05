<?php
if (!defined('ABSPATH')) exit;

class WC_Gateway_Mpesa extends WC_Payment_Gateway {
    public function __construct() {
        $this->id = 'mpesa';
        $this->method_title = 'M-Pesa';
        $this->method_description = 'Pay via M-Pesa STK Push';
        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->enabled = $this->get_option('enabled');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title' => 'Enable/Disable',
                'type' => 'checkbox',
                'label' => 'Enable M-Pesa Gateway',
                'default' => 'yes'
            ],
'title' => [
    'title' => 'Title',
    'type' => 'text',
    'default' => 'M-Pesa'
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
        ];
    }

    public function payment_fields() {
    echo '<p><label for="mpesa_phone">Phone Number (2547XXXXXXXX):</label><br>';
    echo '<input type="tel" name="mpesa_phone" id="mpesa_phone" required pattern="2547[0-9]{8}" placeholder="2547XXXXXXXX" />';
    echo '</p>';
}

public function process_payment($order_id) {
    $order = wc_get_order($order_id);

    // 🔐 Sanitize and validate phone number
    $phone = isset($_POST['mpesa_phone']) ? sanitize_text_field($_POST['mpesa_phone']) : '';
    if (empty($phone) || !preg_match('/^2547\d{8}$/', $phone)) {
        wc_add_notice('Invalid phone number format. Use 2547XXXXXXXX.', 'error');
        return;
    }

    $amount = $order->get_total();

    // 🚀 Fire STK Push
    $response = initiate_mpesa_stk_push($phone, $amount, $order_id);

    // 📡 Log and handle response
    if (!empty($response['ResponseCode']) && $response['ResponseCode'] === '0') {
        $order->update_status('on-hold', __('Awaiting M-Pesa payment', 'mpesa-woocommerce-gateway'));
        $order->add_order_note('STK Push initiated for ' . $phone);
        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url($order)
        ];
    } else {
        $error = $response['errorMessage'] ?? 'STK Push failed. Try again.';
        wc_add_notice($error, 'error');
        return;
    }
}

}