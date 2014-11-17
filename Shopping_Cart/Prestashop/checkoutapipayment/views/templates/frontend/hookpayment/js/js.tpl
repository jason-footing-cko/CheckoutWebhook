<form name="checkoutapipayment_form" id="checkoutapipayment_form" action="{$link->getModuleLink('checkoutapipayment', 'validation', [], true)|escape:'html'}" method="post">
    <div class="payment-select-txt">{l s='Please select a credit/debit card' mod='checkoutprestashop'}</div>
    <div class="widget-container"></div>
    <input type="hidden" name="cko_cc_token" id="cko-cc-token" value="">
    <input type="hidden" name="cko_cc_email" id="cko-cc-email" value="" />
    <script type="text/javascript" src="http://ckofe.com/js/Checkout.js"></script>
    <script type="text/javascript">
     function CheckoutReady() {
         Checkout.render({
             publicKey: "{$publicKey}",
             userEmail: '{$mailAddress}',
             amount: "{$amount}",
             currency: "{$currency}",
             widgetContainerSelector: '.widget-container',
             widgetRendered: function (event) {

    //             if ($$('.cko-pay-now')[0]) {
    //                 $$('.cko-pay-now')[0].hide();
    //             }
             },
             cardTokenReceived: function (event) {
                 document.getElementById('cko-cc-token').value = event.data.cardToken;
                 document.getElementById('cko-cc-email').value = event.data.email;
                 // payment.save();
                 document.getElementById('checkoutapipayment_form').submit();
             }
         });

     }
        if (window.addEventListener) {

            window.addEventListener('load', CheckoutReady, false)
        }
        else if (window.attachEvent) {
            window.attachEvent('onload', CheckoutReady)
        }

    </script>
</form>