<?php
/*
 * Plugin Name: GiveX API Integration
 * Description: Allow user to use GiveX gift cards
 * Version: 1.0.0
 * Author: Jeremy Dela Cruz
 * Author URI: https://github.com/JDCxDEV
 * Text Domain: give-x-api
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/woocommerce.php';
require_once __DIR__ . '/data-dictionary.php';
require_once( ABSPATH . 'wp-includes/class-wpdb.php' );

$BASE_URL = 'https://dc-au1.givex.com';
$PORT = '50104';
$PORT_BACKUP = '50104';

$USER = '1416846';
$PASS = 'Y9dUNqWlkc5EqHfD';
$LANG = 'en';

function call_give_x_api_post(string $method, array $params, int $timeout, string $default_port = null, $cancel_transaction = false): array | string {
    $retry_count = $cancel_transaction ? 2 : 1;
    $total_retry = 1;

    $port_current = $default_port ? $default_port : $GLOBALS['PORT'];
  
    do {
        $request_data = [
            'jsonrpc' => '2.0',
            'id' => '1',
            'method' => $cancel_transaction ? $method . ';C' : $method . ';' . $total_retry,
            'params' => $params,
        ];

        $request_json = json_encode($request_data);

        $base_current = $default_port ==  $GLOBALS['PORT'] ? $GLOBALS['BASE_URL'] : 'https://dc-au2.givex.com';

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $request_args = [
            'body' => $request_json,
            'headers' => $headers,
            'timeout' => $cancel_transaction ? 60 : $timeout,
        ];

        $response = wp_remote_post($base_current . ':' . $port_current, $request_args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();

            $retry_count++;
            $total_retry++;

            if ($retry_count >= 2 && $port_current != $GLOBALS['PORT_BACKUP'] && !$cancel_transaction) {
                $port_current = $GLOBALS['PORT_BACKUP'];
                $retry_count = 1; // Reset retry count after switching port
            }

            sleep(2);
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);

            if ($response_data) {
                return get_data($response_data['result'], $method);
            } else {
                return 'JSON parsing error.';
            }
        }
    } while ($retry_count < 3);

    return false;
}

function give_x_api_scripts()
{
    wp_enqueue_script('give-x-form', plugins_url('js/form.js', __FILE__), array('jquery'), '1.0', true);
}


// Hook the script to run after the checkout form
function run_script_after_checkout_form() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {

            var targetElement = $('#thwmsc-tab-panel-3');

            var observer = new MutationObserver(function (mutationsList, observer) {
                var displayStyle = targetElement.css('display');

                if (displayStyle !== 'none') {
                    $('tr.fee:contains("GiveX Coupon")').hide();
                }
            });

            var config = { attributes: true, childList: true, subtree: true };

            observer.observe(targetElement[0], config);
        });
    </script>

    <?php
}

add_action('wp_enqueue_scripts', 'give_x_api_scripts');
add_action('woocommerce_checkout_after_order_review', 'run_script_after_checkout_form');
?>