(function ($) {
    Drupal.behaviors.checkoutapijs = {
        attach: function (context, settings) {
            $('#edit-commerce-payment-payment-method-commerce-gw3-checkoutapipaymentcommerce-payment-commerce-gw3-checkoutapipayment').click(function () {
                var elm = $(this),
                        parent = elm.parent();
                parent.append($('#cko-widget'));
                $('#cko-widget').show();
            });

            $('input.checkout-continue').click(function(event){
                event.preventDefault();    
                CheckoutIntegration.open();
            });
        }
    };
})(jQuery);