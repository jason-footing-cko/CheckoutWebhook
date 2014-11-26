
<h4>Please select a Credit / Debit card</h4>
    <div class="widget-container"></div>
    <div class="content" id="payment">
    <input type="hidden" name="cko_cc_token" id="cko-cc-token" value="">
    <input type="hidden" name="cko_cc_email" id="cko-cc-email" value="" />
    </div>



    <script type="text/javascript">


        function checkoutRender() {
            Checkout.render({
                publicKey: "<?php echo $entry_public_key ?>",
                userEmail: "<?php echo $order_email ?>",
                value: "<?php echo $amount ?>",
                currency: "<?php echo $order_currency ?>",
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

                    $.ajax({
                        url: 'index.php?route=payment/checkoutapipayment/send',
                        type: 'post',
                        data: $('#payment :input'),
                        dataType: 'json',
                        beforeSend: function () {
                            $('#button-confirm').attr('disabled', true);
                            $('#payment').before('<div class="attention"><img src="catalog/view/theme/default/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
                        },
                        complete: function () {
                            $('#button-confirm').attr('disabled', false);
                            $('.attention').remove();
                        },
                        success: function (json) {

                            if (json['error']) {
                                alert(json['error']);
                            }

                            if (json['success']) {
                                 location = json['success'];
                            }
                        }
                    });
                }
            });

        }
        function loadExtScript(src, test, callback) {
            var s = document.createElement('script');
            s.src = src;
            document.body.appendChild(s);

            var callbackTimer = setInterval(function() {

               if(typeof Checkout!='undefined'){
                   clearInterval(callbackTimer);
                   checkoutRender();
               }
            }, 100);
        }

        loadExtScript('http://ckofe.com/js/Checkout.js',checkoutRender,function(){})
    </script>