<?php

add_action('init', function() {
    if (isset($_GET['mpesa_callback'])) {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);

        // Log or process callback
        file_put_contents(__DIR__ . '/mpesa-callback-log.txt', print_r($data, true));

        // You can update order status here based on ResultCode
        http_response_code(200);
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        exit;
    }
});