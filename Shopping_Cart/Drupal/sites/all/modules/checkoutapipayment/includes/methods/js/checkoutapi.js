(function($) {
  Drupal.behaviors.CKOConfig = {
    attach: function (context, settings) {
               settings.CKOConfig.debug,
                       
                settings.CKOConfig.renderMode,
                settings.CKOConfig.publicKey,
                settings.CKOConfig.email,
//                'namespace': 'CheckoutIntegration',
//                settings.CKOConfig.name,
//                settings.CKOConfig.amount,
//                settings.CKOConfig.currency,
//                settings.CKOConfig.paymentToken,
//                'paymentMode': 'card',              
               settings.CKOConfig.widgetSelector
    }
  };
})(jQuery);