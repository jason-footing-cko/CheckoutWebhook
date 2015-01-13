
<h4>Please select a Credit / Debit card</h4>
    <div class="widget-container"></div>
    <div class="content" id="payment">
    <input type="hidden" name="cko_cc_token" id="cko-cc-token" value="">
    <input type="hidden" name="cko_cc_email" id="cko-cc-email" value="" />
    </div>



    <script type="text/javascript">


        function checkoutRender() {
            Checkout.render(<?php echo $jsconfig ?>);

        }
        function loadExtScript(src, test, callback) {

            if(!document.getElementById('checkoutApiJs')) {
                var s = document.createElement('script');
                s.src = src;
                s.id = 'checkoutApiJs';
                document.body.appendChild(s);
            }
            var callbackTimer = setInterval(function() {

               if(typeof Checkout!='undefined'){
                   clearInterval(callbackTimer);
                   checkoutRender();
               }
            }, 180);
        }

        loadExtScript('https://www.checkout.com/cdn/js/Checkout.js',checkoutRender,function(){})
    </script>