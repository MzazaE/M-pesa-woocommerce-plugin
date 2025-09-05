jQuery(function($) {
    let stkPushVerified = false;

    $('form.checkout').on('checkout_place_order_mpesa', function(e) {
        if (!stkPushVerified) {
            e.preventDefault(); // Halt WooCommerce submission

            const phoneNumber = $('input[name="mpesa_phone"]').val();

            if (!phoneNumber || !/^2547\d{8}$/.test(phoneNumber)) {
                alert('⚠️ Please enter a valid M-Pesa phone number.');
                return false;
            }

            $('#place_order').prop('disabled', true).text('Verifying STK Push...');

            $.ajax({
                url: mpesa_params.ajax_url,
                method: 'POST',
                data: {
                    action: 'mpesa_stk_push_confirm',
                    phone: phoneNumber,
                    order_id: $('input[name="order_id"]').val() || 0 // fallback
                },
                success: function(response) {
                    if (response.success) {
                        console.log('✅ STK Push confirmed');
                        stkPushVerified = true;

                         // 🧪 Show sandbox confirmation message
        alert('✅ STK Push simulated successfully. This is a sandbox test—no real prompt will appear.');

                        $('#place_order').prop('disabled', false).text('Place order');
                        $('form.checkout').submit(); // Proceed with order submission
                    } else {
                        alert('❌ STK Push failed: ' + response.data.message);
                        $('#place_order').prop('disabled', false).text('Place order');
                    }
                },
                error: function(xhr, status, error) {
                    alert('❌ STK Push request failed. Please try again.');
                    $('#place_order').prop('disabled', false).text('Place order');
                }
            });
        }
    });
});