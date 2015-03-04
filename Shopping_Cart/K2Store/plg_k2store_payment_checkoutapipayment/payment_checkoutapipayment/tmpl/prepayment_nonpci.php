<?php
/*
* @package		plg_k2store_payment_checkoutapipayment
* @subpackage	K2Store
* --------------------------------------------------------------------------------
*/

//no direct access
defined('_JEXEC') or die('Restricted access');

?>
  <div class="note">
       <strong><?php echo JText::_($vars->display_name); ?></strong>
		<br />
		<?php echo JText::_($vars->onbeforepayment_text); ?>
    </div>
<form id="k2store_checkoutapipaymentdirect_form" action="<?php echo JRoute::_( "index.php?option=com_k2store&view=checkout" ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

		<p>Please Select your Credit Card Type</p>

		<input type="hidden" name="cko_cc_token" id="cko_cc_token" value="" >
		<input type="hidden" name="cko_cc_email" id="cko_cc_email" value="" >

		<script src="https://www.checkout.com/cdn/js/Checkout.js"></script>
		<div style="" class="widget-container">
		    <script type="text/javascript">
		        window.CKOConfig = {
		            namespace: 'CKOJS',
		            publicKey: <?php echo '\''. $vars->publishable_key .'\'' ?>,
		            customerEmail: <?php echo '\''. $vars->customerEmail .'\''?>,
		            customerName: <?php echo  '\''.$vars->customerName .'\''?>,
		            value: <?php echo  '\''.$vars->value .'\''?>,
		            currency: <?php echo '\''. $vars->currency .'\''?>,
		            billingDetails: {
		                addressLine1: <?php echo '\''. $vars->billing_addressLine1 .'\'' ?>,
		                addressLine2: <?php echo '\''.  $vars->billing_addressLine2 .'\'' ?>,
		                postcode: <?php echo '\''.  $vars->billing_postcode .'\'' ?>,
		                country: <?php echo '\''.  $vars->billing_country .'\''?>,
		                city: <?php echo '\''. $vars->billing_city .'\''?>,
		                state: <?php echo '\''.  $vars->billing_state .'\'' ?>,
		                phone: <?php echo '\''. $vars->billing_phone .'\''?>
		            },
		            widgetContainerSelector: '.widget-container',
		            widgetRendered: function (event) {
		                jQuery(".cko-pay-now").hide();
		            },
		            cardTokenReceived: function (event) {
		                document.getElementById('cko_cc_token').value = event.data.cardToken;
		                document.getElementById('cko_cc_email').value = event.data.email;
		            }
		        };

		    </script>
		</div>

        <div class="plugin_error_div">
			<span class="plugin_error"></span>
			<span class="plugin_error_instruction"></span>
		</div>

		<br />


		<input type="button" onclick="k2storecheckoutapipaymentdirectSubmit(this)" class="button btn btn-primary" value="<?php echo JText::_($vars->button_text); ?>" />

    	<input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>' />
    	<input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>' />
    	<input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>' />
     	<input type='hidden' name='option' value='com_k2store' />
    	<input type='hidden' name='view' value='checkout' />
   	 	<input type='hidden' name='task' value='confirmPayment' />
    	<input type='hidden' name='paction' value='process' />

    	<?php echo JHTML::_( 'form.token' ); ?>
</form>


<script type="text/javascript">
if(typeof(k2store) == 'undefined') {
	var k2store = {};
}
if(typeof(k2store.jQuery) == 'undefined') {
	k2store.jQuery = jQuery.noConflict();
}

if(typeof(k2storeURL) == 'undefined') {
	var k2storeURL = '';
}

function k2storecheckoutapipaymentdirectSubmit(button) {

	(function($) {
		$(button).attr('disabled', 'disabled');
		$(button).val('<?php echo JText::_('K2STORE_CHECKOUTAPIPAYMENT_PROCESSING_PLEASE_WAIT')?>');
		var form = $('#k2store_checkoutapipaymentdirect_form');
	    var values = form.serializeArray();

	var jqXHR =	$.ajax({
			url: 'index.php',
			type: 'post',
			data: values,
			dataType: 'json',
			beforeSend: function() {
				$(button).after('<span class="wait">&nbsp;<img src="media/k2store/images/loader.gif" alt="" /></span>');
			}
	});

		jqXHR.done(function(json) {
			form.find('.k2success, .k2warning, .k2attention, .k2information, .k2error').remove();
			console.log(json);
			if (json['error']) {
				form.find('.plugin_error').after('<span class="k2error">' + json['error']+ '</span>');
				form.find('.plugin_error_instruction').after('<br /><span class="k2error"><?php echo JText::_('K2STORE_CHECKOUTAPI_ON_ERROR_INSTRUCTIONS'); ?></span>');
				$(button).val('<?php echo JText::_('K2STORE_CHECKOUTAPIPAYMENT_ERROR_PROCESSING')?>');
			}

			if (json['redirect']) {
				$(button).val('<?php echo JText::_('K2STORE_CHECKOUTAPIPAYMENT_COMPLETED_PROCESSING')?>');
				window.location.href = json['redirect'];
			}

		});

		jqXHR.fail(function() {
			$(button).val('<?php echo JText::_('K2STORE_CHECKOUTAPIPAYMENT_ERROR_PROCESSING')?>');
		})

		jqXHR.always(function() {
			$('.wait').remove();
		 });

	})(k2store.jQuery);
}

</script>
