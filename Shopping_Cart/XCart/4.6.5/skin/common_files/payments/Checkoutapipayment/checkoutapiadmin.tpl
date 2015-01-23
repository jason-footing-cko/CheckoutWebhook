<link rel="stylesheet" type="text/css" href="../skin/common_files/CSS/checkoutapipayment/checkoutapi.css" />
</br>
{$lng.txt_cc_configure_top_text}
<br /><br />
{capture name=dialog}

<div class="checkoutapi-wrapper">
    <a href="http://dev.checkout.com/" class="checkoutapi-logo" target="_blank">
        <img src="../images/checkoutapipayment/checkout-logo@2x.png" alt="Checkout.com" border="0" />
    </a>


    <p class="checkoutapi-intro">
        <ul id="checkoutDevelops" class="checkoutDevelops">
            <li>Checkout develops and operates our own payment gateway technology. No outsourcing, less risk</li>
            <li>With PCI-Level 1 certification, we ensure the highest level of protection for merchants, consumers and data</li>
            <li>Checkout technology supports many of the services we offer, including hosted payment pages, fraud
                <br> management systems, etc, helping you connect securely with your consumers</li>
        </ul>
    </p>
    <div class="setting">
        <h3 class="setting-header">Setting for Checkout.com Gateway 3.0</h3>
        <form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">
            <ul class="fields-set">
                <li class="field">
                    <label for="test_mode">
                        <span>Production Mode<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <select name="param01" class="input-txt required"  required>
                            <option value="Test"{if $module_data.param01 eq "Test"} selected="selected"{/if}>Test</option>
                            <option value="Preprod"{if $module_data.param01 eq "Preprod"} selected="selected"{/if}>Preprod</option>
                            <option value="Live"{if $module_data.param01 eq "Live"} selected="selected"{/if}>Live</option>
                        </select>
                    </div>
                </li>
                <li class="field">
                    <label for="secret_key">
                        <span>Secret key<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" name="param02"  class="input-txt
                        required" required  value="{$module_data.param02|escape}"/>
                    </div>
                </li>
                <li class="field">
                    <label for="">
                        <span>Public key<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" name="param03" class="input-txt
                        required" required  value="{$module_data.param03|escape}"/>
                    </div>
                </li>
                <li class="checkbox">
                    <label for="checkoutapi_localpayment_enable">
                        <span>LocalPayment Enable</span>
                    </label>
                    <div class="wrapper-field">
                        <input type="hidden" name="param04" value="N" />
                        <input type="checkbox" name="param04" value="Y"{if $module_data.param04 eq "Y"} checked="checked"{/if} />
                    </div>
                </li>
                <li class="field">
                    <label for="pci_enable">
                        <span>PCI enable</span>
                    </label>
                    <div class="wrapper-field">
                        <div class="wrapper-field">
                            <select name="param05" class="input-txt required" id="param05" required>
                                <option value="yes" {if $module_data.param05 eq "yes"} selected {/if}>Yes</option>
                                <option value="no" {if $module_data.param05 eq "no"} selected {/if}>No</option>

                            </select>
                        </div>
                    </div>
                </li>
                <li class="field">
                    <label for="payment_action">
                        <span>Payment Action<em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <select name="param06"  class="input-txt required" required >
                        <option value="Authorize"{if $module_data.param06 eq "authorize"} selected="Authorize"{/if}>Authorize Only</option>
                        <option value="Authorize and Capture"{if $module_data.param06 eq "Authorize and Capture"} selected="capture"{/if}>Authorize and Capture</option>
                        </select>
                    </div>
                </li>

                <li class="field">
                    <label for="checkoutapi_autocapture_delay">
                        <span>Auto capture time <em>*</em></span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" class="input-txt required" required name="param07"
                               value="{$module_data.param07|escape}"/>
                    </div>
                </li>
                {*<li class="field">*}
                    {*<label for="">*}
                        {*<span>Card type<em>*</em></span>*}
                    {*</label>*}
                    {*<div class="wrapper-field">*}
                        {*<ul class="card-type-list">*}
                            {*{foreach from=$cardtype item='card'}*}
                                {*<li class="card {$card.id}-carttype">*}
                                    {*<label for="cardType[{$card.id}]">*}
                                        {*<input type="checkbox" name="cardType[{$card.id}]"*}
                                               {*id="cardType[{$card.id}]"*}
                                               {*class="card-txt input-txt {if $card.selected}selected{/if}"*}
                                               {*{if $card.selected}checked="checked"{/if} value="1"/>*}
                                        {*<span style="background-image:url({$card.path})" class="{$card.id}-class {if $card.selected}selected{/if}">*}

                                        {*</span>*}


                                    {*</label>*}
                                {*</li>*}
                            {*{/foreach}*}
                        {*</ul>*}
                    {*</div>*}
                {*</li>*}

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
                    <label for="gateway_timeout">
                        <span>Gateway timeout</span>
                    </label>
                    <div class="wrapper-field">
                        <input type="text" class="input-txt required" required name="param08"
                                value="{$module_data.param08|escape}"/>
                    </div>
                </li>

                <li class="field">
                    <label for="currency">
                        <span>Currency *</span>
                    </label>
                    <div class="wrapper-field">
                        <select name="param09" class="input-txt required" required >
                            <option value="ATS"{if $module_data.param09 eq "ATS"} selected="selected"{/if}>Austrian Shilling</option>
                            <option value="AUD"{if $module_data.param09 eq "AUD"} selected="selected"{/if}>Australian Dollar</option>
                            <option value="BEF"{if $module_data.param09 eq "BEF"} selected="selected"{/if}>Belgian franc</option>
                            <option value="CAD"{if $module_data.param09 eq "CAD"} selected="selected"{/if}>Canadian Dollar</option>
                            <option value="CHF"{if $module_data.param09 eq "CHF"} selected="selected"{/if}>Swiss Franc</option>
                            <option value="CZK"{if $module_data.param09 eq "CZK"} selected="selected"{/if}>Czech Koruna</option>
                            <option value="DEM"{if $module_data.param09 eq "DEM"} selected="selected"{/if}>German mark</option>
                            <option value="DKK"{if $module_data.param09 eq "DKK"} selected="selected"{/if}>Danish Kroner</option>
                            <option value="ESP"{if $module_data.param09 eq "ESP"} selected="selected"{/if}>Spanish Peseta</option>
                            <option value="EUR"{if $module_data.param09 eq "EUR"} selected="selected"{/if}>EURO</option>
                            <option value="FIM"{if $module_data.param09 eq "FIM"} selected="selected"{/if}>Finnish Markka</option>
                            <option value="FRF"{if $module_data.param09 eq "FRF"} selected="selected"{/if}>French franc</option>
                            <option value="GBP"{if $module_data.param09 eq "GBP"} selected="selected"{/if}>British pound</option>
                            <option value="HKD"{if $module_data.param09 eq "HKD"} selected="selected"{/if}>Hong Kong Dollar</option>
                            <option value="HUF"{if $module_data.param09 eq "HUF"} selected="selected"{/if}>Hungarian Forint</option>
                            <option value="IEP"{if $module_data.param09 eq "IEP"} selected="selected"{/if}>Irish Punt</option>
                            <option value="ILS"{if $module_data.param09 eq "ILS"} selected="selected"{/if}>New Shekel</option>
                            <option value="ITL"{if $module_data.param09 eq "ITL"} selected="selected"{/if}>Italian Lira</option>
                            <option value="JPY"{if $module_data.param09 eq "JPY"} selected="selected"{/if}>Japanese Yen</option>
                            <option value="LTL"{if $module_data.param09 eq "LTL"} selected="selected"{/if}>Litas</option>
                            <option value="LUF"{if $module_data.param09 eq "LUF"} selected="selected"{/if}>Luxembourg franc</option>
                            <option value="LVL"{if $module_data.param09 eq "LVL"} selected="selected"{/if}>Lats Letton</option>
                            <option value="MXN"{if $module_data.param09 eq "MXN"} selected="selected"{/if}>Peso</option>
                            <option value="NLG"{if $module_data.param09 eq "NLG"} selected="selected"{/if}>Dutch Guilders</option>
                            <option value="NOK"{if $module_data.param09 eq "NOK"} selected="selected"{/if}>Norwegian Kroner</option>
                            <option value="NZD"{if $module_data.param09 eq "NZD"} selected="selected"{/if}>New Zealand Dollar</option>
                            <option value="PLN"{if $module_data.param09 eq "PLN"} selected="selected"{/if}>Polish Zloty</option>
                            <option value="PTE"{if $module_data.param09 eq "PTE"} selected="selected"{/if}>Portuguese Escudo</option>
                            <option value="RUR"{if $module_data.param09 eq "RUR"} selected="selected"{/if}>Rouble</option>
                            <option value="SEK"{if $module_data.param09 eq "SEK"} selected="selected"{/if}>Swedish Krone</option>
                            <option value="SGD"{if $module_data.param09 eq "SGD"} selected="selected"{/if}>Singapore Dollar</option>
                            <option value="SKK"{if $module_data.param09 eq "SKK"} selected="selected"{/if}>Couronne Slovaque</option>
                            <option value="THB"{if $module_data.param09 eq "THB"} selected="selected"{/if}>Thai Bath</option>
                            <option value="TRL"{if $module_data.param09 eq "TRL"} selected="selected"{/if}>Lire Turque</option>
                            <option value="USD"{if $module_data.param09 eq "USD"} selected="selected"{/if}>US Dollar</option>
                            <option value="ZAR"{if $module_data.param09 eq "ZAR"} selected="selected"{/if}>South African Rand</option>
                            <option value="ZAR"{if $module_data.param09 eq "AED"} selected="selected"{/if}>United Arab Emirates dirham</option>
                        </select>
                </li>
                <br>
                <li class="action">
                    <input type="submit" value="Update Settings"/>
                </li>
            </ul>
        </form>
    </div>
</div>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}