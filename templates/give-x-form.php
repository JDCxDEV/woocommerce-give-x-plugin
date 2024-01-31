<?php
/**
 * Checkout give-x cards form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-gift-cards.php.
 *
 * Author: Jeremy Dela Cruz
 * Author URI: https://github.com/JDCxDEV
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!apply_filters('give-x-show-giftcard', true)) {
    return;
}

$direct_display = 'no';

if (function_exists('wc_print_notice')) {
    wc_print_notice(
        '<div class="givex-have-a-code">' . $icon . 'Got a gift card from a loved one? <a href="#" class="give-x-show-giftcard">Use it here!</a></div>',
        'notice'
    );
}
?>

<div class="give-x-enter-code" method="post" style="<?php echo ('yes' !== $direct_display ? 'display:none' : ''); ?>">
    <div style="position: relative">

        <p class="form-row form-row-first">
            <p class="give-x-description">
                Apply the 20-digit gift card code in the box below
            </p>

            <div class="respond-block"></div>

            <div class="give-x-input-row">
                <input type="text" maxlength="30" name="gift_card_code" class="input-text give-x-column gift_card_code" placeholder="Card Number" id="gift_card_code" value="" />
                <input type="text" maxlength="6" name="gift_card_pin" class="input-text give-x-column gift_card_pin" placeholder="Card Pin" id="gift_card_pin" value="" />
            </div>
        </p>

        <p class="form-row form-row-last">
            <button type="submit" id="apply_gift_card_button" class="button givex_apply_gift_card_button" name="givex_apply_gift_card">
                Apply Gift Card
            </button>
        </p>

        <div class="clear"></div>
    </div>
</div>

<style>
    .give-x-column {
        width: 49%;
    }

    .give-x-description {
        margin-bottom: 1em;
    }

    .give-x-input-row:after {
        margin-bottom: 1em;
        content: "";
        clear: both;
    }

    .give-x-input-row {
        margin-bottom: 1em;
    }

    .give-x-button {
        margin-bottom: 1.5em;
    }

    .give-x-enter-code {
        border-radius: 30px;
        padding: 1.3333rem;
        margin-bottom: 2.6667rem;
        background-color: #f6f8fc;
    }

    .error-message, .success-message {
        margin-bottom: 12px;
        font-size: 14px;
    }

    .error-message {
        color: #ff0033;
    }

    .success-message {
        color: #22bb33;
    }

    .loading-indicator {
        margin-right: 5px;
    }

    .give-x-remove-coupon {
        margin-left: 3px;
        border: none;
        background: transparent;
        color: red;
        text-decoration: underline;
    }
</style>