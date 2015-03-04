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

        <table class="table">
            <tr>
                <td class="field_name"><?php echo JText::_( 'K2STORE_CHECKOUTAPIPAYMENT_CREDITCARD_TYPE' ) ?></td>
                <td><?php echo $vars->cardtype;
			//echo $vars->cardtype; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'K2STORE_CHECKOUTAPIPAYMENT_CARD_NUMBER' ) ?></td>
                <td>************<?php echo $vars->cardnum_last4; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'K2STORE_CHECKOUTAPIPAYMENT_EXPIRATION_DATE' ) ?></td>
                <td><?php echo $vars->cardexp; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'K2STORE_CHECKOUTAPIPAYMENT_CARD_CVV' ) ?></td>
                <td>****</td>
            </tr>
        </table>

        <div class="plugin_error_div">
			<span class="plugin_error"></span>
			<span class="plugin_error_instruction"></span>
		</div>

		<br />

<input type='hidden' name='cardname' value='<?php echo @$vars->cardname; ?>' />
    <input type='hidden' name='cardtype' value='<?php echo @$vars->cardtype; ?>' />
    <input type='hidden' name='cardnum' value='<?php echo @$vars->cardnum; ?>' />
    <input type='hidden' name='cardexp' value='<?php echo @$vars->cardexp; ?>' />
    <input type='hidden' name='cardcvv' value='<?php echo @$vars->cardcvv; ?>' />
        <input type='hidden' name='cardmonth' value='<?php echo @$vars->cardmonth; ?>' />
        <input type='hidden' name='cardyear' value='<?php echo @$vars->cardyear; ?>' />

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
