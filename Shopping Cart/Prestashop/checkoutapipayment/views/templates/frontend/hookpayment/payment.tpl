<p class="payment_module" >
    {if $hasError == 1}
        <p class="error">
            {if !empty($smarty.get.message)}
                {l s='Error detail from Checkout.com : ' mod='checkoutapipayment'}
                {$smarty.get.message|htmlentities}
            {else}
                {l s='Error, please verify the card details' mod='checkoutapipayment'}
            {/if}

        </p>
    {/if}
    <div style="" class="checkoutapi-info">
			<a id="click_checkoutprestashop" href="javascript:void(0)" title="{l s='Pay with Checkout.com' mod='checkoutprestashop'}" style="">
                <img src="{$module_dir}skin/img/checkout-logo@2x.png" alt="Pay through Checkout.com" border="0" align="absmiddle" width="360" class="img-logo"/>
                <span class="span-desc">{l s='Secured credit/debit card payment with Checkout.com' mod='checkoutprestashop'}</span>
                {if $template}
                     {include file="../hookpayment/js/$template"}
                {/if}

            </a>
    </div>
</p>
