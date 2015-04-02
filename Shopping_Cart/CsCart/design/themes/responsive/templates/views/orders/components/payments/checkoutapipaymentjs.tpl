
<div class="control-group">{$id_suffix}
    <p>Please select a credit/debit card</p>
    <div class="widget-container"></div>

    <label for="cko-cc-paymenToken" class="control-label cc-bridge-token "></label>
    <div class="controls clear">
        <div class="cm-field-container nowrap ">
            <input type="hidden" name="cko_cc_paymenToken" id="cko-cc-paymenToken" value="" class="cc-bridge-token ">
        </div>
    </div>

</div>


{checkoutapijs}

<script type="text/javascript">
    var seeder = 0;
    (function(_, $) {
       $.ceEvent('on', 'ce.commoninit', function () {
           if(document.getElementById('cko-cc-paymenToken')) {
            if (!document.getElementById('cko-checkoutjs')) {
                var script = document.createElement("script");
                script.type = "text/javascript";
                script.src = "//checkout.com/cdn/js/Checkout.js";
                script.id = "cko-checkoutjs";
                document.getElementsByTagName("head")[0].appendChild(script)
            } else {
                if(!seeder) {
                    if(typeof CheckoutIntegration != 'undefined') {
                        CheckoutIntegration.render(window.CKOConfig);
                    }else if(typeof Checkout != 'undefined') {
                        Checkout.render(window.CKOConfig);
                    }
                    seeder++;
                }
            }
           $.ceEvent('on', 'ce.formpre_'+ $('#cko-cc-paymenToken').parents('.payments-form.cm-processed-form').attr
                   ('name'),
                   function () {

                       if (document.getElementById('cko-cc-paymenToken').value == '') {
                           if (jQuery('#cko-cc-paymenToken').parents('.control-group')
                                           .parent('div').prev('.ty-payments-list__item').find
                                   ("[name^=payment_id]:checked").length) {

                               if (typeof CheckoutIntegration != 'undefined') {
                                   CheckoutIntegration.open();
                               }


                           }

                           return false;
                       }
                       return true;
           });

           }

       })
    })(Tygh, Tygh.$);




</script>
