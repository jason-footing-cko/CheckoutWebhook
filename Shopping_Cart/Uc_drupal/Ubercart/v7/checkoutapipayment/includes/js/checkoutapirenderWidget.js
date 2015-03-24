(function ($) {
    $(function () {
        var head = document.getElementsByTagName("head")[0],
        scriptJs = document.getElementById('checkoutApijs');

        if (!scriptJs) {
            scriptJs = document.createElement('script');

            scriptJs.src = 'http://ckofe.com/js/checkout.js';
            scriptJs.id = 'checkoutApijs';
            scriptJs.type = 'text/javascript';
            var interVal = setInterval(function () {
                if (CheckoutIntegration) {
                    $('head').append($('.widget-container link'));
                    clearInterval(interVal);
                }

            }, 1000);
            head.appendChild(scriptJs);
        }
    });

    Drupal.behaviors.uc_checkoutapipayment = {
        attach: function (context, settings) {
            $('#edit-panes-payment-payment-method-checkoutapipayment-credit').unbind('click.CheckoutApi');
            $('#edit-panes-payment-payment-method-checkoutapipayment-credit').bind('click.CheckoutApi', function () {


                var interVal2 = setInterval(function () {
                    if ($('.widget-container').length) {
                        CheckoutIntegration.render(window.CKOConfig);
                        clearInterval(interVal2);
                    }

                }, 500);


            })

        }

    }

})(jQuery);

