<h4 class="heading4"><?php echo $text_credit_card; ?>:</h4>

<form id="checkoutapipayment" class="form-horizontal validate-creditcard">
    <div class="widget-container"></div>
    <div>
        <input type="hidden" name="cko_cc_paymenToken" id="cko-cc-paymenToken" value="<?php echo $paymentToken ?>">
    </div>
    <div class="form-group action-buttons text-center">
        <a id="<?php echo $back->name ?>" href="<?php echo $back->href; ?>" class="btn btn-default mr10" title="<?php echo $back->text ?>">
            <i class="fa fa-arrow-left"></i>
            <?php echo $back->text ?>
        </a>
        <button id="<?php echo $submit->name ?>" class="btn btn-orange" title="<?php echo $submit->text ?>" type="submit">
            <i class="fa fa-check"></i>
            <?php echo $submit->text; ?>
        </button>
    </div>
</form>

<script src="http://ckofe.com/js/checkout.js" async ></script>
<script type="text/javascript">
    window.CKOConfig = {
        debugMode: false,
        renderMode: 0,
        namespace: 'CheckoutIntegration',
        publicKey: '<?php echo $publicKey ?>',
        paymentToken: '<?php echo $paymentToken ?>',
        value: '<?php echo $amount ?>',
        currency: '<?php echo $order_currency ?>',
        customerEmail: '<?php echo $email ?>',
        customerName: '<?php echo $name ?>',
        paymentMode: 'card',
        title: '<?php echo $store_name ?>',
        subtitle: 'Please enter your credit card details',
        widgetContainerSelector: '.widget-container',
        cardCharged: function (event) {
            document.getElementById('cko-cc-paymenToken').value = event.data.paymentToken;
        },
        ready: function () {
        }
    };
</script>

