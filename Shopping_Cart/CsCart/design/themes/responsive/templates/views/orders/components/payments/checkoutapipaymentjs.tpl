
<div class="control-group">{$id_suffix}
    <p>Please select a credit/debit card</p>
    <div class="widget-container"></div>

    <label for="cko-cc-token" class="control-label cc-bridge-token hidden"></label>
    <div class="controls clear">
        <div class="cm-field-container nowrap hidden">
            <input type="text" name="cko_cc_token" id="cko-cc-token" value="" class="cc-bridge-token ">
        </div>
    </div>

    <div class="controls clear hidden">
        <div class="cm-field-container nowrap">
            <input type="text" name="cko_cc_email" id="cko-cc-email" value="" class="cc-bridge-token " />
        </div>
    </div>
</div>


{checkoutapijs}

<script type="text/javascript">
    (function(_, $) {
        $(function() {

            $.ceFormValidator('registerValidator', {
                class_name: 'cc-bridge-token',
                message: "",
                func: function(id) {

                    if(document.getElementById('cko-cc-email').value =='' && document.getElementById('cko-cc-token').value =='' ){
                        if(jQuery('#cko-cc-token').parents('.control-group')
                                        .parent('div').prev('.ty-payments-list__item').find
                                ("[name^=payment_id]:checked").length) {

                            if(CheckoutIntegration) {
                                CheckoutIntegration.open();
                            }
                        }

                        return false;
                    }
                    return true;
                }
            });
        });
    })(Tygh, Tygh.$);
</script>
<script src="https://www.checkout.com/cdn/js/Checkout.js" async ></script>