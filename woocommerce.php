<?php
/*
 * Woocommerce Hook Functions
 * Version: 1.0.0
 * Author: Jeremy Dela Cruz
 * Author URI: https://github.com/JDCxDEV
 */

function add_give_x_form_code()
{
    require __DIR__ . '/templates/give-x-form.php';
}

function add_give_x_buttons()
{
    require __DIR__ . '/templates/giftcards-button.php';
}

function create_givex_pre_auth_coupons_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'give_x_pre_auth_coupons';

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Table doesn't exist, so create it

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(255) NOT NULL,
            givex_pre_auth_reference VARCHAR(255) NOT NULL,
            code VARCHAR(50) NOT NULL,
            pin VARCHAR(20) NOT NULL,
            expiry_timestamp BIGINT NOT NULL,
            redeem_date DATETIME DEFAULT NULL,
            redeem_response TEXT DEFAULT NULL,
            date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            amount DECIMAL(10,2) DEFAULT '0.00',
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
    }
}

function add_give_x_custom_functions()
{
    add_action('woocommerce_before_cart', 'add_give_x_form_code');
    add_action('woocommerce_before_cart', 'add_give_x_buttons', 5);
    add_action('woocommerce_before_checkout_form', 'add_give_x_form_code');
}

add_action('wp', 'create_givex_pre_auth_coupons_table');
add_action('wp', 'add_give_x_custom_functions');