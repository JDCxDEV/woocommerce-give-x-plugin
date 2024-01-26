<div class="giftcards-buttons">
    <button class="button button--gift-card give-x-prv coupon-active" onclick="toggleGiftCard('give-x')">
        Spark Pop gift card
    </button>

    <button class="button button--gift-card other-prv" onclick="toggleGiftCard('otherProvider')">
        Vendor gift card
    </button>
</div>

<style>
    .button--gift-card {
        background-color: #d3d3d3 !important;
    }

    .button--gift-card:hover {
        color: black !important;
    }

    .button--gift-card.coupon-active:hover {
        color: #ffe000 !important;
    }

    .giftcards-buttons {
        padding-bottom: 10px;
    }

    .hidden {
        display: none;
    }

    .coupon-active {
        background-color: #6529f5 !important;
    }



    @media (max-width: 600px) {
        .give-x-prv {
            width: 100%;
            margin-bottom: 12px;
        }

        .other-prv {
            width: 100%;
        }
    }
</style>

<script>
    function toggleGiftCard(provider, button) {
        const give-xButton = document.querySelector('.give-x-prv');
        const otherProviderButton = document.querySelector('.other-prv');

        var hideElements, showElements, activeButton, inactiveButton;

        if (provider === 'give-x') {
            hideElements = document.querySelectorAll('.givex-have-a-code');
            showElements = document.querySelectorAll('.ywgc_have_code');
            activeButton = give-xButton;
            inactiveButton = otherProviderButton;
        } else {
            hideElements = document.querySelectorAll('.ywgc_have_code');
            showElements = document.querySelectorAll('.givex-have-a-code');
            activeButton = otherProviderButton;
            inactiveButton = give-xButton;
        }

        hideElements.forEach(function(element) {
            // Hide the element
            element.style.display = 'none';

            // Hide the parent element with class "woocommerce-info"
            var parentElement = element.closest('.woocommerce-info');
            if (parentElement) {
                parentElement.style.display = 'none';
            }
        });

        showElements.forEach(function(element) {
            // Show the element
            element.style.display = 'block';

            // Show the parent element with class "woocommerce-info"
            var parentElement = element.closest('.woocommerce-info');
            if (parentElement) {
                parentElement.style.display = 'block';
            }
        });

        // Add "coupon-active" class to the active button
        activeButton.classList.add('coupon-active');

        // Remove "coupon-active" class from the inactive button
        inactiveButton.classList.remove('coupon-active');
    }
</script>
