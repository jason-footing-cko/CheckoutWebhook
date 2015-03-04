<h4>Please select a Credit / Debit card</h4>
<div class="widget-container"></div>
<div class="content" id="payment">
    <input type="hidden" name="cko-cc-paymenToken" id="cko-cc-paymenToken" value="">
</div>
{checkoutapijs}
<script>
    function checkCheckoutForm()
    {ldelim}
            // Check if profile filled in: registerform should not exist on the page
            if ($('form[name=registerform]').length > 0) {
                xAlert(txt_opc_incomplete_profile, '', 'E');
                return false;
    {rdelim}

            if (need_shipping && ($('input[name=shippingid]').val() <= 0 || (undefined === shippingid || shippingid <= 0))) {ldelim}
                        xAlert(txt_opc_shipping_not_selected, '', 'E');
                        return false;
    {rdelim}

            if (!paymentid && (undefined === paymentid || paymentid <= 0)) {ldelim}
                        xAlert(txt_opc_shipping_not_selected, '', 'E');
                        return false;
    {rdelim}



            // Check terms accepting
            var termsObj = $('#accept_terms')[0];
            if (termsObj && !termsObj.checked) {ldelim}
                        xAlert(txt_accept_terms_err, '', 'W');
                        return false;
    {rdelim}
            var checkoutId = jQuery('#cko-cc-paymenToken').parents('.payment-details').attr('id').split('_')[1];
            if (checkoutId && $('#pm' + checkoutId + ':checked').length
                    && $('#cko-cc-paymenToken').val() == '') {ldelim}
                                if (CheckoutIntegration){ldelim}
                                                CheckoutIntegration.open();
    {rdelim}

                $('.being-placed, .blockOverlay, .blockPage').hide();
                return false;
    {rdelim}

            return true;
    {rdelim}
</script>
<script src="http://ckofe.com/js/checkout.js" async ></script>
<script type="text/javascript">

        window.CKOConfig = {ldelim}
                debugMode: false,
                renderMode: 2,
                namespace: 'CheckoutIntegration',
                publicKey: '{$checkoutapiData.publicKey}',
                paymentToken: '{$checkoutapiData.paymentToken}',
                value: '{$checkoutapiData.amount}',
                currency: '{$checkoutapiData.currency}',
                customerEmail: '{$checkoutapiData.email}',
                customerName: '{$checkoutapiData.name}',
                paymentMode: 'card',
                title: '',
                subtitle: 'Please enter your credit card details',
                widgetContainerSelector: '.widget-container',
                cardCharged: function (event) {ldelim}
                            document.getElementById('cko-cc-paymenToken').value = event.data.paymentToken;
                            $('button.place-order-button').trigger('submit');

    {rdelim},
    {rdelim};
</script>