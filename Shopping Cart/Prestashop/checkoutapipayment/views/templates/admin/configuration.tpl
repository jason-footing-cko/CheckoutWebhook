<div class="checkoutapi-wrapper">
    {if $respond}
        <div class="message state-{$respond.status}">
            <span>{$respond.message}</span>
        </div>
    {/if}
    <a href="http://dev.checkout.com/" class="checkoutapi-logo" target="_blank"><img src="{$module_dir}skin/img/checkout-logo@2x.png" alt="Checkout.com" border="0" /></a>
    <p class="checkoutapi-intro">

        <ul id="checkoutDevelops" class="checkoutDevelops">
            <li>Checkout develops and operates our own payment gateway technology. No outsourcing, less risk</li>
            <li>With PCI-Level 1 certification, we ensure the highest level of protection for merchants, consumers and data</li>
            <li>Checkout technology supports many of the services we offer, including hosted payment pages, fraud <br> management systems, etc, helping you connect securely with your consumers</li>
        </ul>

    </p>
    <div class="setting">
        <h3 class="setting-header"> {l s='Setting for Checkout.com Gateway 3.0' mod='checkoutAPI'}</h3>
        <form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
            <ul class="fields-set">
                <li class="field">
                    <label for="checkoutapi_test_mode">
                        <span>Production Mode<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <select name="checkoutapi_test_mode" class="input-txt required" id="checkoutapi_test_mode" required>
                            <option value="test"  {if $CHECKOUTAPI_TEST_MODE =='test'}selected{/if}>Test</option>
                            <option value="preprod" {if $CHECKOUTAPI_TEST_MODE =='preprod'}selected{/if}>Preprod</option>
                            <option value="live" {if $CHECKOUTAPI_TEST_MODE =='live'}selected{/if}>Live</option>
                        </select>
                    </div>
                </li>
              <li class="field">
                    <label for="">
                        <span>Secret key<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" name="checkoutapi_secret_key" id="checkoutapi_secret_key" class="input-txt
                        required" required  value="{$CHECKOUTAPI_SECRET_KEY}"/>
                    </div>
                </li>
              <li class="field">
                    <label for="">
                        <span>Public key<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" name="checkoutapi_public_key" id="checkoutapi_public_key" class="input-txt
                        required" required  value="{$CHECKOUTAPI_PUBLIC_KEY}"/>
                    </div>
                </li>
              <li class="field">
                    <label for="checkoutapi_localpayment_enable">
                        <span>LocalPayment Enable</span>
                    </label>
                    <div class="wrapper-field">
                        <input type="checkbox" name="checkoutapi_localpayment_enable" id="checkoutapi_localpayment_enable"
                               value="{$CHECKOUTAPI_LOCALPAYMENT_ENABLE}" >
                    </div>
                </li>
              <li class="field">
                    <label for="checkoutapi_pci_enable">
                        <span>PCI enable</span>
                    </label>
                    <div class="wrapper-field">
                        <div class="wrapper-field">
                            <select name="checkoutapi_pci_enable" class="input-txt required" id="checkoutapi_pci_enable" required>
                                <option value="0"  {if $CHECKOUTAPI_PCI_ENABLE ==0}selected{/if}>No</option>
                                <option value="1" {if $CHECKOUTAPI_PCI_ENABLE ==1}selected{/if}>Yes</option>
                            </select>
                        </div>
                    </div>
                </li>
              <li class="field">
                    <label for="checkoutapi_payment_action">
                        <span>Payment Action<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <select name="checkoutapi_payment_action" id="checkoutapi_payment_action"
                                class="input-txt required" required >
                            {foreach from=$transactionType item='transaction'}
                                <option value="{$transaction.value}" {if $CHECKOUTAPI_PAYMENT_ACTION =='$transaction.value'}slected{/if}>{$transaction.label}</option>
                            {/foreach}
                        </select>
                    </div>
                </li>

                <li class="field">
                    <label for="checkoutapi_autocapture_delay">
                        <span>Auto capture time <em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" class="input-txt required" required name="checkoutapi_autocapture_delay"
                                id="checkoutapi_autocapture_delay" value="{$CHECKOUTAPI_AUTOCAPTURE_DELAY}"/>
                    </div>
                </li>
              <li class="field">
                    <label for="">
                        <span>Card type<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <ul class="card-type-list">
                            {foreach from=$cardtype item='card'}
                                <li class="card {$card.id}-carttype">
                                    <label for="cardType[{$card.id}]">
                                        <input type="checkbox" name="cardType[{$card.id}]"
                                               id="cardType[{$card.id}]"
                                               class="card-txt input-txt {if $card.selected}selected{/if}"
                                               {if $card.selected}checked="checked"{/if} value="1"/>
                                        <span style="background-image:url({$card.path})" class="{$card.id}-class {if $card.selected}selected{/if}">

                                        </span>


                                    </label>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                </li>

              {*<li class="field">*}
                    {*<label for="checkoutapi_hold_review_os">*}
                        {*<span>Order status:  "Hold for Review"<em>*</em></span>*}
                    {*</label>*}
                    {*<div class="wrapper-field">*}
                        {*<select id="checkoutapi_hold_review_os" name="checkoutapi_hold_review_os" class="input-txt required">*}
                            {*// Hold for Review order state selection*}
                            {*{foreach from=$order_states item='os'}*}
                                {*<option value="{if $os.id_order_state|intval}" {((int)$os.id_order_state == $CHECKOUTAPI_HOLD_REVIEW_OS)} selected{/if}>*}
                                    {*{$os.name|stripslashes}*}
                                {*</option>*}
                            {*{/foreach}*}
                        {*</select>*}
                    {*</div>*}
                {*</li>*}

              <li class="field">
                    <label for="checkoutapi_gateway_timeout">
                        <span>Gateway timeout</span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" class="input-txt required" required name="checkoutapi_gateway_timeout"
                               id="checkoutapi_gateway_timeout" value="{$CHECKOUTAPI_GATEWAY_TIMEOUT}"/>
                    </div>
                </li>

                <li class="action">

                    <div class="wrapper-field">
                        <button name="submitPayment" type="submit" >
                            <span><span>Update settings</span></span>
                        </button>
                    </div>
                </li>

            </ul>

        </form>
    </div>
</div>