
<h4>Please select a Credit / Debit card</h4>
    <div class="widget-container"></div>
    <div class="content" id="payment">
    <input type="hidden" name="cko_cc_token" id="cko-cc-token" value="">
    <input type="hidden" name="cko_cc_email" id="cko-cc-email" value="" />
    </div>



    <script type="text/javascript">


        function checkoutRender() {


        }
        function loadExtScript(src) {

            if(!document.getElementById('checkoutApiJs')) {
                var s = document.createElement('script');
                s.src = src;
                s.type = 'text/javascript';
                s.id = 'checkoutApiJs';
                s.async = 'true';
                s.onload =  function() {
                    var rs = this.readyState; console.log(rs);

                    try {
                        if(typeof Checkout!='undefined') {
                            Checkout.render(<?php echo $jsconfig ?>);
                        }


                    } catch (e) {

                    }
                };

                //document.body.appendChild(s);
            }
//            var callbackTimer = setInterval(function() {
//
//               if(typeof Checkout!='undefined'){
//                   clearInterval(callbackTimer);
//                   checkoutRender();
//               }
//            }, 180);
        }

     //   loadExtScript('https://www.checkout.com/cdn/js/Checkout.js');

        $.ajax({
            url: '//checkout.com/cdn/js/Checkout.js',
            dataType: 'script',
            cache: true,
            beforeSend: function(){
                <?php echo $jsconfig ?>
            },
            success: function() {
              //  Checkout.render();
            }

        });
    </script>