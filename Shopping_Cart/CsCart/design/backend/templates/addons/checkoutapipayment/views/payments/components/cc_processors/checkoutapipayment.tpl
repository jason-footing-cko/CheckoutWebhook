{* $Id: quickpay_window.tpl 0002 2009-09-22 19:40:55Z murzik $ *}

<div class="control-group">
	<label class="control-label" for="merchant">{__("merchant")}:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][merchant]" id="merchant" value="{$processor_params.merchant}" class="input-text" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="language">{__("language")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][language]" id="language">
			<option value="da"{if $processor_params.language == "da"} selected="selected"{/if}>{__("danish")}</option>
			<option value="de"{if $processor_params.language == "de"} selected="selected"{/if}>{__("german")}</option>
			<option value="en"{if $processor_params.language == "en"} selected="selected"{/if}>{__("english")}</option>
			<option value="fo"{if $processor_params.language == "fo"} selected="selected"{/if}>{__("faeroese")}</option>
			<option value="fr"{if $processor_params.language == "fr"} selected="selected"{/if}>{__("french")}</option>
			<option value="it"{if $processor_params.language == "it"} selected="selected"{/if}>{__("italian")}</option>
			<option value="no"{if $processor_params.language == "no"} selected="selected"{/if}>{__("norwegian")}</option>
			<option value="nl"{if $processor_params.language == "nl"} selected="selected"{/if}>{__("dutch")}</option>
			<option value="pl"{if $processor_params.language == "pl"} selected="selected"{/if}>{__("polish")}</option>
			<option value="se"{if $processor_params.language == "se"} selected="selected"{/if}>{__("swedish")}</option>
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="currency">{__("currency")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][currency]" id="currency">
			<option value="USD"{if $processor_params.currency == "USD"} selected="selected"{/if}>{__("currency_code_usd")}</option>
			<option value="EUR"{if $processor_params.currency == "EUR"} selected="selected"{/if}>{__("currency_code_eur")}</option>
			<option value="AUD"{if $processor_params.currency == "AUD"} selected="selected"{/if}>{__("currency_code_aud")}</option>
			<option value="CAD"{if $processor_params.currency == "CAD"} selected="selected"{/if}>{__("currency_code_cad")}</option>
			<option value="CHF"{if $processor_params.currency == "CHF"} selected="selected"{/if}>{__("currency_code_chf")}</option>
			<option value="CZK"{if $processor_params.currency == "CZK"} selected="selected"{/if}>{__("currency_code_czk")}</option>
			<option value="DKK"{if $processor_params.currency == "DKK"} selected="selected"{/if}>{__("currency_code_dkk")}</option>
			<option value="FRF"{if $processor_params.currency == "FRF"} selected="selected"{/if}>{__("currency_code_frf")}</option>
			<option value="GBP"{if $processor_params.currency == "GBP"} selected="selected"{/if}>{__("currency_code_gbp")}</option>
			<option value="HKD"{if $processor_params.currency == "HKD"} selected="selected"{/if}>{__("currency_code_hkd")}</option>
			<option value="HUF"{if $processor_params.currency == "HUF"} selected="selected"{/if}>{__("currency_code_huf")}</option>
			<option value="ILS"{if $processor_params.currency == "ILS"} selected="selected"{/if}>{__("currency_code_ils")}</option>
			<option value="JPY"{if $processor_params.currency == "JPY"} selected="selected"{/if}>{__("currency_code_jpy")}</option>
			<option value="LTL"{if $processor_params.currency == "LTL"} selected="selected"{/if}>{__("currency_code_ltl")}</option>
			<option value="LVL"{if $processor_params.currency == "LVL"} selected="selected"{/if}>{__("currency_code_lvl")}</option>
			<option value="MXN"{if $processor_params.currency == "MXN"} selected="selected"{/if}>{__("currency_code_mxn")}</option>
			<option value="NOK"{if $processor_params.currency == "NOK"} selected="selected"{/if}>{__("currency_code_nok")}</option>
			<option value="NZD"{if $processor_params.currency == "NZD"} selected="selected"{/if}>{__("currency_code_nzd")}</option>
			<option value="PLN"{if $processor_params.currency == "PLN"} selected="selected"{/if}>{__("currency_code_pln")}</option>
			<option value="RUR"{if $processor_params.currency == "RUR"} selected="selected"{/if}>{__("currency_code_rur")}</option>
			<option value="SEK"{if $processor_params.currency == "SEK"} selected="selected"{/if}>{__("currency_code_sek")}</option>
			<option value="SGD"{if $processor_params.currency == "SGD"} selected="selected"{/if}>{__("currency_code_sgd")}</option>
			<option value="SKK"{if $processor_params.currency == "SKK"} selected="selected"{/if}>{__("currency_code_skk")}</option>
			<option value="THB"{if $processor_params.currency == "THB"} selected="selected"{/if}>{__("currency_code_thb")}</option>
			<option value="TRY"{if $processor_params.currency == "TRY"} selected="selected"{/if}>{__("currency_code_try")}</option>
			<option value="KPW"{if $processor_params.currency == "KPW"} selected="selected"{/if}>{__("currency_code_kpw")}</option>
			<option value="KRW"{if $processor_params.currency == "KRW"} selected="selected"{/if}>{__("currency_code_krw")}</option>
			<option value="ZAR"{if $processor_params.currency == "ZAR"} selected="selected"{/if}>{__("currency_code_zar")}</option>
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="msgtype">{__("msgtype")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][msgtype]" id="msgtype">
			<option value="authorize" {if $processor_params.msgtype == "authorize"}selected="selected"{/if}>{__("authorize")}</option>
			<option value="subscribe" {if $processor_params.msgtype == "subscribe"}selected="selected"{/if}>{__("subscribe")}</option>
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="autocapture">{__("autocapture")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][autocapture]" id="autocapture">
			<option value="1" {if $processor_params.autocapture == "1"}selected="selected"{/if}>{__("yes")}</option>
			<option value="0" {if $processor_params.autocapture == "0"}selected="selected"{/if}>{__("no")}</option>
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="cardtypelock">{__("cardtypelock")}:</label>
	<div class="controls">
		<textarea cols="60" rows="10" name="payment_data[processor_params][cardtypelock]" id="cardtypelock"></textarea>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="secret">{__("secret")}:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][secret]" id="secret" value="{$processor_params.secret}" class="input-text" />
	</div>
</div>


<div class="control-group">
	<label class="control-label" for="testmode">{__("testmode")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][testmode]" id="testmode">
			<option value="1" {if $processor_params.testmode == "1"}selected="selected"{/if}>{__("test")}</option>
			<option value="0" {if $processor_params.testmode == "0"}selected="selected"{/if}>{__("live")}</option>
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}" class="input-text" />
	</div>
</div>

