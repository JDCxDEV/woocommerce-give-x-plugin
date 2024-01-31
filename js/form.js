jQuery(document).ready(function ($) {

    /* Global Variables */
    let host = window.location.origin + '/give-x';
    let apiUrlGiveXRedeem = host + "/wp-json/api/v1/give-x-submit-form";
    let apiUrlGiveXRemove = host + "/wp-json/api/v1/give-x-remove-code";
    const giveXEnterCode = $('.give-x-enter-code');
    const respondBlock = $('.respond-block');
    const applyGiftCardButton = $('#apply_gift_card_button');
    
    $('tr.fee:contains("GiveX Coupon")').hide();
    
    $(document).on('click', 'a.give-x-show-giftcard', show_gift_card_form);
    $(document).on('click', 'button.give-x-prv, button.other-prv', hide_forms);

    applyGiftCardButton.on('click', handleApplyGiftCard);

    function hide_forms() {
        if (giveXEnterCode.is(':visible')) {
            giveXEnterCode.hide(300, function () {
                $('#gift_card_code').find(':input:eq(0)').focus();
            });
        } else {
            $('#gift_card_code').find(':input:eq(0)').focus();
        }
        return false;
    }

    function show_gift_card_form() {
        giveXEnterCode.slideToggle(300, function () {
            $('#gift_card_code').find(':input:eq(0)').focus();
        });
        return false;
    }

    function block($node) {
        $node.addClass('processing').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    }

    function update_cart_totals() {
        block($('div.cart_totals'));

        $.ajax({
            url: host + '/?wc-ajax=get_cart_totals',
            dataType: 'html',
            success: function (response) {
                $('div.cart_totals').replaceWith(response);
                $('tr.fee:contains("GiveX Coupon")').hide();
                initializeGiveX();
            }
        });

        $(document.body).trigger('update_checkout');
    }

    function handleApplyGiftCard() {
        const cardCode = $('#gift_card_code').val();
        const cardPin = $('#gift_card_pin').val();
        const cartTotal = $('#total_cart_value').val();

        if (!cardCode.length || !cardPin.length) {
            respondBlock.html('<p class="error-message">Card code and pin are required.</p>');
            return;
        }

        respondBlock.html('');

        applyGiftCardButton.html('<i class="fas fa-circle-notch fa-spin loading-indicator"></i> Apply Gift Card').prop('disabled', true);

        $.ajax({
            url: apiUrlGiveXRedeem,
            method: 'POST',
            data: {
                gift_card_code: cardCode,
                gift_card_pin: cardPin,
                cart_total: cartTotal,
            },
            success: function (response) {
                if (!response.success) {
                    respondBlock.html(`<p class="error-message">${response.message}.</p>`);
                } else {
                    update_cart_totals();
                    respondBlock.html(`<p class="success-message">${response.message}</p>`);
                }
            },
            error: function (error) {
                console.error('Error:', error);
            },
            complete: function () {
                applyGiftCardButton.prop('disabled', false).html('Apply Gift Card');
            }
        });
    }

    function initializeGiveX() {
        $('#give-x-coupon').on('click', function () {
            const dataCodeValue = $(this).data('code');
            $(this).html('<i class="fas fa-circle-notch fa-spin loading-indicator"></i> Remove').prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: apiUrlGiveXRemove,
                data: { code: dataCodeValue },
                success: function () {
                    update_cart_totals();
                },
                error: function (error) {
                    console.error('Error:', error);
                },
                complete: function () {
                    $(this).prop('disabled', false).html('Remove');
                }
            });
        });
    }

    initializeGiveX();

    $('.givex-have-a-code').each(function () {
        $(this).hide();
        const parentElement = $(this).closest('.woocommerce-info');
        if (parentElement) {
            parentElement.hide();
        }
    });
});