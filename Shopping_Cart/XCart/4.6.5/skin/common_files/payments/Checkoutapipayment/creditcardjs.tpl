<h4>Please select a Credit / Debit card</h4>

<div class="widget-container"></div>
<div class="content" id="payment">
    <input type="hidden" name="cko_cc_token" id="cko-cc-token" value="">
    <input type="hidden" name="cko_cc_email" id="cko-cc-email" value="" />
</div>



{literal}
<script type="text/javascript">

<!--

function checkCheckoutForm() {

    // Check if profile filled in: registerform should not exist on the page
    if ($('form[name=registerform]').length > 0) {
        xAlert(txt_opc_incomplete_profile, '', 'E');
        return false;
    }

    if (need_shipping && ($('input[name=shippingid]').val() <= 0 || (undefined === shippingid || shippingid <= 0))) {
        xAlert(txt_opc_shipping_not_selected, '', 'E');
        return false;
    }

    if (!paymentid && (undefined === paymentid || paymentid <= 0)) {
        xAlert(txt_opc_shipping_not_selected, '', 'E');
        return false;
    }



    // Check terms accepting
    var termsObj = $('#accept_terms')[0];
    if (termsObj && !termsObj.checked) {
        xAlert(txt_accept_terms_err, '', 'W');
        return false;
    }
    var checkoutId = jQuery('#cko-cc-token').parents('.payment-details').attr('id').split('_')[1];
    if(checkoutId && $('#pm'+checkoutId+':checked').length
            && $('#cko-cc-email').val() =='' && $('#cko-cc-token').val() ==''){

        $('.cko-pay-now').trigger('click');
        $('.being-placed, .blockOverlay, .blockPage').hide();
        return false;
    }
    return true;
}

    (function($) {






        $.ajax({
            url: 'checkoutapipayment_ajax.php',
            dataType: 'json'

        }).done( function(xhrResponse) {

            var jsonObj = xhrResponse,
                setting = {
                debugMode: false,
                renderMode:0,
                namespace: 'CheckoutIntegration',
               widgetContainerSelector:'.widget-container',
                cardTokenReceived: function(event) {
                    document.getElementById('cko-cc-token').value = event.data.cardToken;
                    document.getElementById('cko-cc-email').value = event.data.email;
                    $('button.place-order-button').trigger('submit');
                },
                widgetRendered: function (event) {
                    $('.cko-pay-now').hide();
                },
                ready: function() {}
            };

            window.CKOConfig = $.extend({}, jsonObj, setting);

            $.ajax({
                url:'//checkout.com/cdn/js/Checkout.js',
                dataType: 'script',
                cache: true
            });

        });



    })(jQuery);


-->
</script>

{/literal}