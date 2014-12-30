<?php
class methods_creditcard extends methods_Abstract
{
    public function submitFormCharge ($payment_method, $pane_form, $pane_values, $order, $charge) {
        $config = parent::submitFormCharge($payment_method, $pane_form, $pane_values, $order, $charge);
        $config['postedParam']['cardToken'] = $pane_values['credit_card']['cko_cc_token'];
        $config['postedParam']['email'] = $pane_values['credit_card']['cko_cc_email'];


        return $this->_placeorder($config,$charge,$order,$payment_method);
    }

    public function  submit_form($payment_method)
    {

        $form['credit_card']['cko_cc_token'] = array(
            '#type' => 'textfield',
            '#attributes' =>  array(
                'style' => array(
                    'display:none'
                )
            ),
            '#title' =>''



        );
        $form['credit_card']['cko_cc_email'] = array(
            '#type' => 'textfield',
            '#attributes' =>  array(
                'style' => array(
                    'display:none'
                )
            ),
            '#title' =>''


        );
        return $form;

    }

    public function getExtraInit(){
        $toReturn = parent::getExtraInit();
        $array = array();
        $payment_method = commerce_payment_method_instance_load('commerce_gw3_checkoutapipayment|commerce_payment_commerce_gw3_checkoutapipayment');
        module_load_include('inc', 'commerce_payment', 'includes/commerce_payment.credit_card');
        global $user;
        $order = commerce_cart_order_load($user->uid);
        if($order) {
            $order_wrapper = entity_metadata_wrapper('commerce_order', $order);
            $billing_address = $order_wrapper->commerce_customer_billing->commerce_customer_address->value();
            $order_array = $order_wrapper->commerce_order_total->value();

            // Prepare the fields to include on the credit card form.
            $Api = CheckoutApi_Api::getApi(array('mode'=>$payment_method['settings']['mode']));
            $config = array();
            $config['debug'] = false;
            $config['publicKey'] = $payment_method['settings']['public_key'] ;
            $config['email'] =  $order->mail;
            $config['name'] = "{$billing_address['first_name']} {$billing_address['last_name']}";
            $config['amount'] =  $order_array['amount'];
            $config['currency'] = $order_array['currency_code'];
            $config['renderMode'] = 2;
            $config['widgetSelector'] =  '.widget-container';
            $config['cardTokenReceivedEvent'] = "

                            document.getElementById('edit-commerce-payment-payment-details-credit-card-cko-cc-token').value = event.data.cardToken;
                            document.getElementById('edit-commerce-payment-payment-details-credit-card-cko-cc-email').value = event.data.email;
                            ";
            $config['widgetRenderedEvent'] ="jQuery('#cko-widget').hide();

            jQuery('#edit-commerce-payment-payment-method-commerce-gw3-checkoutapipaymentcommerce-payment-commerce-gw3-checkoutapipayment').click(function(){
             var elm = jQuery(this),
                parent = elm.parent();
                parent.append(jQuery('#cko-widget'));
                jQuery('#cko-widget').show();
                jQuery('#edit-commerce-payment-payment-details-credit-card-hidden').hide();
                jQuery('#payment-details').hide();

            });
            jQuery('[name=\"commerce_payment\[payment_method\]\"]').click(function(){
                if(jQuery(this).attr('id')!='edit-commerce-payment-payment-method-commerce-gw3-checkoutapipaymentcommerce-payment-commerce-gw3-checkoutapipayment'){
                    jQuery('#cko-widget').hide();
                            jQuery('#payment-details').show();
                }else {
                 jQuery('#cko-widget').show();
                }

            });
            jQuery('[name=\"commerce_payment\[payment_method\]\"]:checked').trigger('click');
            ";
            $config['readyEvent'] = '';


            $jsConfig = $Api->getJsConfig($config);

            $array['script'] =$jsConfig.'';
            $array['script_external'] ='https://www.checkout.com/cdn/js/Checkout.js';
        }
        return $array;
    }
}