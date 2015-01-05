<form name="checkoutapipayment_form" id="checkoutapipayment_form" action="{$link->getModuleLink('checkoutapipayment', 'validation', [], true)|escape:'html'}" method="post">
    <div class="payment-select-txt">{l s='Please select a credit/debit card' mod='checkoutprestashop'}</div>
    <div class="widget-container"></div>
    <input type="hidden" name="cko_cc_token" id="cko-cc-token" value="">
    <input type="hidden" name="cko_cc_email" id="cko-cc-email" value="" />

    <script async src="https://www.checkout.com/cdn/js/Checkout.js"></script>
    <script type="text/javascript">
        jQuery(function(){
            jQuery('#click_checkoutprestashop').attr('href','javascript:void(0');
        });
    {$jsScript}

    </script>
</form>