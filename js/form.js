jQuery(document).ready(function ($) {

    /* Global Variables */

    let host = window.location.origin + '/sparkpop';

    let apiUrlGiveXRedeem =  host + "/wp-json/api/v1/give-x-submit-form";
    let apiUrlGiveXRemove =  host + "/wp-json/api/v1/give-x-remove-code";

    $('tr.fee:contains("GiveX Coupon")').hide();
    
    $( document ).on( 'click', 'a.give-x-show-giftcard', show_gift_card_form );

    $( document ).on( 'click', 'button.sparkpop-prv', hide_forms );

    $( document ).on( 'click', 'button.other-prv', hide_forms ); 


    function hide_forms() {
        var giveXEnterCode = $('.give-x-enter-code');
        var ywgcEnterCode = $('.ywgc_enter_code');
    
        // Check if either element is visible
        if (giveXEnterCode.is(':visible') || ywgcEnterCode.is(':visible')) {
            // Hide both elements
            giveXEnterCode.hide(300);
            ywgcEnterCode.hide(300, function () {
                // After hiding, focus on the first input inside the element with ID 'gift_card_code'
                $('#gift_card_code').find(':input:eq(0)').focus();
            });
        } else {
            // If none of them is visible, just focus on the input without toggling
            $('#gift_card_code').find(':input:eq(0)').focus();
        }
    
        // Prevent the default behavior of the anchor tag (e.g., navigating to a URL)
        return false;
    }


    function show_gift_card_form() {
        $( '.give-x-enter-code' ).slideToggle(
            300,
            function() {
                $( '#gift_card_code' ).find( ':input:eq( 0 )' ).focus();
            }
        );
        return false;
    }

    const applyGiftCardButton = document.getElementById('apply_gift_card_button');

	/**
	 * Update the cart after something has changed.
	 */


    var block = function($node) {
		$node.addClass( 'processing' ).block(
			{
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			}
		);
	};

    
	function update_cart_totals() {
		block( $( 'div.cart_totals' ) );

		$.ajax(
			{
				url: host + '/?wc-ajax=get_cart_totals',
				dataType: 'html',
				success: function(response) {
					$( 'div.cart_totals' ).replaceWith( response );
                    $('tr.fee:contains("GiveX Coupon")').hide();
                    initializeGiveX();
				}
			}
		);

		$( document.body ).trigger( 'update_checkout' )
    
	}

    applyGiftCardButton.addEventListener('click', function () {

        const cardCode = $('#gift_card_code').val();
        const cardPin = $('#gift_card_pin').val();
        const cartTotal = $('#total_cart_value').val();

        const respondBlock = $('.respond-block');

        if (!cardCode.length || !cardPin.length) {
            respondBlock.html('<p class="error-message">Card code and pin is required.</p>');
            return;
        }

        respondBlock.html('');

        applyGiftCardButton.innerHTML = '<i class="fas fa-circle-notch fa-spin loading-indicator"></i> Apply Gift Card';
        applyGiftCardButton.disabled = true;

        $.ajax({
            url: apiUrlGiveXRedeem,
            method: 'POST',
            data: {
                gift_card_code: cardCode,
                gift_card_pin: cardPin,
                cart_total: cartTotal,
            },
            success: function (response) {
                // Handle the success response
                if (!response.success) {
                    respondBlock.html(`<p class="error-message">${response.message}.</p>`);
                }else {
                    update_cart_totals()
                    respondBlock.html(`<p class="success-message">${response.message}</p>`);
                }

            },
            error: function (error) {
                // Handle errors
                console.error('Error:', error);
            },
            complete: function () {
                applyGiftCardButton.disabled = false;
                applyGiftCardButton.innerHTML = 'Apply Gift Card';
            }
        });
    });

    function initializeGiveX() {
        $('#give-x-coupon').on('click', function() {
            var dataCodeValue = $(this).data('code');
            $(this).innerHTML = '<i class="fas fa-circle-notch fa-spin loading-indicator"></i> Remove';
            $(this).prop('disabled', true);
            
            $.ajax({
                type: 'POST',
                url: apiUrlGiveXRemove,
                data: { code: dataCodeValue},
                success: function() {
                    update_cart_totals();
                },
                error: function(error) {
                    console.error('Error:', error);
                },
                complete: function () {
                    $(this).prop('disabled', false);
                    $(this).innerHTML = 'Remove';
                }
            });
        });
    }

    initializeGiveX();


    var elements = document.querySelectorAll('.givex-have-a-code');

    elements.forEach(function(element) {
        // Hide the element with class "givex-have-a-code"
        element.style.display = 'none';

        // Hide the parent element with class "woocommerce-info"
        var parentElement = element.closest('.woocommerce-info');
        if (parentElement) {
            parentElement.style.display = 'none';
        }
    });
});