<?php
// ✅ Ensure HTTP functions are available
if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
    require_once( ABSPATH . WPINC . '/class-http.php' );
    require_once( ABSPATH . WPINC . '/http.php' );
}

// ✅ STK Push Commander Function
function initiate_mpesa_stk_push($phone, $amount, $order_id) {
    error_log("📞 initiate_mpesa_stk_push called with phone: $phone, amount: $amount, order_id: $order_id");

    // 🔐 Retrieve credentials from plugin settings
    $consumer_key    = get_option('mpesa_consumer_key');
    $consumer_secret = get_option('mpesa_consumer_secret');
    $shortcode       = get_option('mpesa_shortcode');
    $passkey         = get_option('mpesa_passkey');
    $callback_url    = get_site_url() . '/?mpesa_callback=1';
    $timestamp       = date('YmdHis');
    $password        = base64_encode($shortcode . $passkey . $timestamp);

    // 🌍 Determine environment
    $environment = get_option('woocommerce_mpesa_environment', 'sandbox');

    if ($environment === 'live') {
        $token_url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $stk_url   = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    } else {
        $token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $stk_url   = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    }

    // 🔑 Request access token
    $credentials     = base64_encode("$consumer_key:$consumer_secret");
    error_log('📡 Requesting token with credentials: ' . $credentials);

    $token_response = wp_remote_get($token_url, [
        'headers' => ['Authorization' => "Basic $credentials"]
    ]);

    error_log('📡 Token response: ' . print_r($token_response, true));

    if (is_wp_error($token_response)) {
        error_log('❌ Token request failed: ' . $token_response->get_error_message());
        return ['ResponseCode' => '1', 'errorMessage' => 'Token request failed'];
    }

    $token = json_decode(wp_remote_retrieve_body($token_response))->access_token;
    error_log('🔑 Access token: ' . $token);

    // 📦 Build STK Push payload
    $payload = [
        'BusinessShortCode' => $shortcode,
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'TransactionType'   => 'CustomerPayBillOnline',
        'Amount'            => $amount,
        'PartyA'            => $phone,
        'PartyB'            => $shortcode,
        'PhoneNumber'       => $phone,
        'CallBackURL'       => $callback_url,
        'AccountReference'  => 'Order' . $order_id,
        'TransactionDesc'   => 'WooCommerce Order Payment'
    ];

    error_log('📦 STK Push payload: ' . print_r($payload, true));

    // 🚀 Send STK Push request
    $response = wp_remote_post($stk_url, [
        'headers' => [
            'Authorization' => "Bearer $token",
            'Content-Type'  => 'application/json'
        ],
        'body' => json_encode($payload)
    ]);

    error_log('📡 STK Push raw response: ' . print_r($response, true));

    if (is_wp_error($response)) {
        error_log('❌ STK Push failed: ' . $response->get_error_message());
        return ['ResponseCode' => '1', 'errorMessage' => 'STK Push failed'];
    }

    // 📊 Decode and log final response
    $response_data = json_decode(wp_remote_retrieve_body($response), true);
    error_log('📡 STK Push decoded response: ' . print_r($response_data, true));

    return $response_data;
}