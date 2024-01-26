<?php
/*
 * Custom Functions
 * Version: 1.0.0
 * Author: Jeremy Dela Cruz
 * Author URI: https://github.com/JDCxDEV
 */

function give_x_pre_auth($code, $amount) {
    try {

        $trans_code = generateRandomId();

        $method = 'dc_920';

        $default_params = [
            $GLOBALS['LANG'],
            $trans_code,
            $GLOBALS['USER'],
            $GLOBALS['PASS'],
            $code,
            $amount,
        ]; 


        $pre_auth_redeem_code = call_give_x_api_post($method, $default_params, 15);

        if(!$pre_auth_redeem_code) {
            $pre_auth_redeem_code = call_give_x_api_post($method, $default_params, 15, null, true);
            return [
                'transaction_code' => $trans_code,
                'message' => 'something went wrong, please try again!',
                'result' => 'wp_error'
            ];
        }

        if (in_array($pre_auth_redeem_code['givex_pre_auth_reference'], ["Invalid security code", "Cert not exist", "ERR  bal=$0.00"], true)) {
            $error_msg = ($pre_auth_redeem_code['givex_pre_auth_reference'] == "ERR  bal=$0.00")
                ? 'Coupon has a $0.00 balance'
                : $pre_auth_redeem_code['givex_pre_auth_reference'];

            if($pre_auth_redeem_code) {
                return ['message' => $error_msg, 'result' => false];
            }else {
                return [
                    'transaction_code' => $trans_code,
                    'message' => 'something went wrong, please try again!',
                    'success' => false,
                ];
            }
        } else {
            return [
                'transaction_code' => $trans_code,
                'data' => $pre_auth_redeem_code,
                'result' => true
            ];
        }
    } catch (Exception $e) {
        return [
            'transaction_code' => $trans_code,
            'message' => 'something went wrong, please try again!',
            'success' => false,
        ];
    }
}

function give_x_post_auth($code, $ref, $amount) {
    try {
        $result = null;
        $method = 'dc_921';
        $trans_code = generateRandomId();

        $default_params = [
            $GLOBALS['LANG'],
            $trans_code,
            $GLOBALS['USER'],
            $GLOBALS['PASS'],
            $code,
            $amount,
            $ref,
        ]; 

        $pre_auth_redeem_code = call_give_x_api_post($method, $default_params, 15);

        if (!$pre_auth_redeem_code) {
            $pre_auth_redeem_code = call_give_x_api_post($method, $default_params, 15, null, true);
            return [
                'transaction_code' => $trans_code,
                'message' => 'Something went wrong, please try again!',
                'result' => 'wp_error',
                'post_auth_redeem_code' =>  $pre_auth_redeem_code
            ];
        }

        if (
            $pre_auth_redeem_code['givex_pre_auth_reference'] == "Invalid security code" ||
            $pre_auth_redeem_code['givex_pre_auth_reference'] == "Cert not exist" ||
            $pre_auth_redeem_code['givex_pre_auth_reference'] == "ERR  bal=$0.00"
        ) {
            if ($pre_auth_redeem_code['givex_pre_auth_reference'] == "ERR  bal=$0.00") {
                $pre_auth_redeem_code['givex_pre_auth_reference'] = 'Coupon has a $0.00 balance';
            }

            $result = [
                'message' => $pre_auth_redeem_code['givex_pre_auth_reference'],
                'result' => false,
            ];
        } else {
            $result = [
                'transaction_code' => $trans_code,
                'message' => $pre_auth_redeem_code,
                'data' => $pre_auth_redeem_code,
                'result' => true
            ];
        }

        return $result;

    } catch (Exception $e) {
        print_r($e);
    }
}

function give_x_check_balance($code, $pin) {
    try {
        $method = 'dc_994';

        $trans_code = generateRandomId();
        
        $default_params = [
            $GLOBALS['LANG'],
            $trans_code,
            $GLOBALS['USER'],
            $GLOBALS['PASS'],
            $code,
            $pin
        ];

        $secure_balance = call_give_x_api_post($method, $default_params, 15, "50104");

        if(!$secure_balance) {
            return [
                'transaction_code' => $trans_code,
                'message' => 'something went wrong, please try again!',
                'result' => 'wp_error'
            ];
        }

        if ($secure_balance['certificate_balance_or_error_message'] == "Invalid security code" || 
            $secure_balance['certificate_balance_or_error_message'] ==  "Cert not exist" || 
            $secure_balance['certificate_balance_or_error_message'] ==  "0.00" ||
            $secure_balance['certificate_balance_or_error_message'] == "Invalid user ID/pswd"
        ) {

            $error_msg = "Gift Card information is invalid";

            if (in_array($secure_balance['certificate_balance_or_error_message'], ["Cert not exist", "Invalid security code"], true)) {
                $error_msg = "Gift Card information doesn't exist. Please check your card code and pin";
            }

            if ($secure_balance['certificate_balance_or_error_message'] == "0.00") {
                $error_msg = "Gift Card amount is equivalent to $0.00";
            }

            return [
                'transaction_code' => $trans_code,
                'message' =>  $error_msg,
                'data' =>  $secure_balance,
                'result' => false
            ];

        } else {
            return ['data' => $secure_balance, 'result' => true];
        }

    } catch (Exception $e) {
        return [
            'message' => 'something went wrong, please try again!',
            'success' => false,
        ];
    }
}

function submit_form(WP_REST_Request  $request) { 
    $session_id = get_cart_session_id();
    $params = $request->get_params();

    $check_balance = give_x_check_balance($params['gift_card_code'], $params['gift_card_pin']); 

    if($check_balance['result'] === 'wp_error') {
        return [
            'trans_code' => $check_balance['transaction_code'],
            'message' => 'something went wrong, please try again!',
            'success' => false,
        ];
    }
    
    if ($check_balance['result'] === true) {
        $amount = $check_balance['data']['certificate_balance_or_error_message'];

        $amount = $params['cart_total'] >= $amount ? $amount : $params['cart_total'];

        $check_if_has_pre_auth = get_pre_auth_row();

        if ($check_if_has_pre_auth)  {
            $pre_auth = $check_if_has_pre_auth;
            give_x_post_auth($params['gift_card_code'], $pre_auth->givex_pre_auth_reference, "0.00");
        }

        $init_pre_auth = give_x_pre_auth($params['gift_card_code'], $amount);

        if($init_pre_auth['result'] === 'wp_error') {
            return [
                'trans_code' => $init_pre_auth['transaction_code'],
                'message' => 'Something went wrong, please try again later!',
                'success' => false,
            ];
        }

        if($init_pre_auth['result']) {
            create_givex_pre_auth_info(
                $session_id,
                $init_pre_auth['data']['givex_pre_auth_reference'],
                $params['gift_card_code'],
                $params['gift_card_pin'],
                floatval($amount)
            );
        }else {
            return [
                'message' => 'something went wrong, please try again!',
                'success' => false,
            ];
        }
        
        return [
            'message' => 'Gift Card has been successfully applied!',
            'success' => true,
            'balance_response' => $check_balance['data'],
            'pre_auth' => $init_pre_auth['data'],
        ];
    } else {

        if($check_balance) {
            return [
                'message' => $check_balance['message'],
                'data' => $check_balance['data'],
                'success' => false,
            ];
        }else {
            return [
                'message' => $check_balance['message'],
                'success' => false,
            ];
        }

    }
}

function remove_code(WP_REST_Request $request) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'give_x_pre_auth_coupons';

    $existing_record = get_pre_auth_row();

    if ($existing_record) {

        $code = $existing_record->code;

        $pre_auth_code = sanitize_text_field($request->get_param('code'));
        $pre_auth_code = str_replace("give-x-coupon", "", $pre_auth_code);
        
        $init_pre_auth = give_x_post_auth($code, $pre_auth_code, "0.00", "50104");

        if($init_pre_auth['result']) {
            if (empty($pre_auth_code)) {
                return [
                    'message' => 'Invalid or missing coupon code',
                    'success' => false
                ];
            }
    
            $wpdb->delete(
                $table_name,
                array('givex_pre_auth_reference' => $pre_auth_code)
            );
        }
    }

    return [
        'message' => 'Coupon successfully removed',
        'data' => $init_pre_auth['data'],
        'success' => true
    ];
}

function create_givex_pre_auth_info($session_id, $ref, $code, $pin, $amount) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'give_x_pre_auth_coupons';

    $current_timestamp = time();

    $expiry_timestamp = strtotime('+15 minutes', $current_timestamp);

    $data = array(
        'code' => $code,
        'pin' => $pin,
        'session_id' => $session_id,
        'givex_pre_auth_reference' => $ref,
        'expiry_timestamp' => $expiry_timestamp,
        'amount' => $amount,
    );

    if (get_pre_auth_row()) {
        $wpdb->update(
            $table_name,
            $data,
            array('session_id' => $data['session_id'])
        );
    } else {
        $wpdb->insert($table_name, $data);
    }
}

function show_gift_card_amount_on_cart_totals() {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    $cart = WC()->cart;
    $existing_record = get_pre_auth_row();

    if ($existing_record) {
        $amount_to_remove = floatval($existing_record->amount);

        $cart->set_cart_contents_total($cart->cart_contents_total - $amount_to_remove);

        echo '<tr class="gift-card-information">';
        echo '<th>GiveX Coupon</th>';
        echo '<td colspan="2">-$' . $amount_to_remove . ' <button class="give-x-remove-coupon" id="give-x-coupon" data-code="give-x-coupon' . $existing_record->givex_pre_auth_reference . '">Remove</button></td>';
        echo '</tr>';
    }
    
    echo '<input type="text" id="total_cart_value" name="total_cart_value" value="' . $cart->total . '" hidden>';  
}


function reduce_cart_amount() {
    $existing_record = get_pre_auth_row();

    if ($existing_record) {
        $discount = floatval($existing_record->amount);
        WC()->cart->add_fee('GiveX Coupon', -$discount, false, 'testing');
    }
}

function get_pre_auth_row() {
    global $wpdb;

    $session_id = get_cart_session_id();

    $table_name = $wpdb->prefix . 'give_x_pre_auth_coupons';

    $existing_record = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE session_id = %d AND redeem_date IS NULL", $session_id)
    );

    return $existing_record;
}


function get_cart_session_id () {
    $new_cart = WC()->session->get('new_cart');

    if( is_null($new_cart) ) {
        WC()->session->set('new_cart', uniqid());
    }else {
        $new_cart = WC()->session->get('new_cart');
    }

    return $new_cart;
}

function redeem_coupon() {
    $existing_record = get_pre_auth_row();
    global $wpdb;

    $table_name = $wpdb->prefix . 'give_x_pre_auth_coupons';

    $current_timestamp = time();

    $date = strtotime('+3 minutes', $current_timestamp);

    $givex_pre_auth_reference = str_replace("\r\n", '', $givex_pre_auth_reference);
    
    $post_auth_response = give_x_post_auth($existing_record->code, $existing_record->givex_pre_auth_reference, $existing_record->amount);

    if($post_auth_response === 'wp_error') {

        $data = array(
            'redeem_date' => null,
            'redeem_response' => json_encode($post_auth_response['post_auth_redeem_code']),
        );
    
        if ($existing_record) { // Use $existing_record for the condition
            $wpdb->update(
                $table_name,
                $data,
                array('session_id' => $existing_record->session_id) // Use $existing_record->session_id for the condition
            );
        } else {
            // Handle the case when there is no existing record
        }
    }



    $data = array(
        'redeem_date' => date_create()->format('Y-m-d H:i:s'),
        'redeem_response' => json_encode($post_auth_response['data']),
    );

    if ($existing_record) { // Use $existing_record for the condition
        $wpdb->update(
            $table_name,
            $data,
            array('session_id' => $existing_record->session_id) // Use $existing_record->session_id for the condition
        );
    } else {
        // Handle the case when there is no existing record
    }

    return null;
}

function register_give_x_routes()
{
    register_rest_route('api/v1', 'give-x-submit-form', array(
        'methods' => 'POST',
        'callback' => 'submit_form',
    ));

    register_rest_route('api/v1', 'give-x-remove-code', array(
        'methods' => 'POST',
        'callback' => 'remove_code',
    ));
}


function generateRandomId() {
    $n = 5;
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    
    return 'sparkpop-' . $randomString;
}


add_action('woocommerce_thankyou', 'redeem_coupon', 10, 1);

/**
 * Calculate the reduced amount - cart page & checkout page
 */
add_action('woocommerce_cart_calculate_fees', 'reduce_cart_amount');

/**
 * Registered as a api url(s)
 */
add_action('rest_api_init', 'register_give_x_routes');


/**
 * Show gift card amount usage on cart totals - checkout page
 */
add_action( 'woocommerce_review_order_before_order_total', 'show_gift_card_amount_on_cart_totals');

/**
 * Show gift card amount usage on cart totals - cart page
 */
add_action( 'woocommerce_cart_totals_before_order_total', 'show_gift_card_amount_on_cart_totals');






