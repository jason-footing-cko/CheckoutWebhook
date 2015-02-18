<form name="checkoutapipayment_form" id="checkoutapipayment_form" action="{$link->getModuleLink('checkoutapipayment', 'validation', [], true)|escape:'html'}" method="post">
    <div class="payment-select-txt">{l s='Please select a credit/debit card' mod='checkoutprestashop'}</div>
    <div class="widget-container"></div>
    <input type="hidden" name="cko_cc_paymenToken" id="cko-cc-paymenToken" value="">
    {if $paymentToken && $success }

    <script type="text/javascript">
        jQuery(function(){
            jQuery('#click_checkoutprestashop').attr('href','javascript:void(0');
        });

        window.CKOConfig = {
            debugMode: false,
            renderMode: 0,
            namespace: 'CheckoutIntegration',
            publicKey: '{$publicKey}',
            paymentToken: "{$paymentToken}",
            value: '{$amount}',
            currency: '{$currencyIso}',
            customerEmail: '{$mailAddress}',
            customerName: '{$name}',
            paymentMode: 'card',
            title: '{$store}',
            subtitle:'{l s='Please enter your credit card details' mod='checkoutprestashop'}',
            widgetContainerSelector: '.widget-container',
            cardCharged: function(event){
                document.getElementById('cko-cc-paymenToken').value = event.data.paymentToken;
                document.getElementById('checkoutapipayment_form').submit();
            },
            ready: function() {

            }
        };
    </script>
    <script src="http://ckofe.com/js/checkout.js" async ></script>
    {else}
        {$message}
        {l s='Event id' mod='checkoutprestashop'}: {$eventId}
    {/if}
</form>