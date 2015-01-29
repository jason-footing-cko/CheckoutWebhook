{include file="common/subheader.tpl" title=__("gateway_setup") target="#text_checkoutapipayment_gatesetup"}
<div id="text_checkoutapipayment_gatesetup" class="in collapse">



{include file="common/subheader.tpl" title=__("server_configuration") target="#text_checkoutapipayment_mode"}
<div id="text_checkoutapipayment_mode" class="in collapse">

    <div class="control-group">
        <label class="control-label" for="elm_checkoutapipayment_mode">{__("Perform transactions on the production server or on the testing server.")}:</label>
        <div class="controls">
            <select name="pp_settings[pp_mode]" id="elm_checkoutapipayment_mode">

                    <option value="live" {if (isset($pp_settings.pp_mode) && $pp_settings.pp_mode== 'live')
                    }selected="selected"{/if}>live</option>
                <option value="preprod" {if (isset($pp_settings.pp_mode) && $pp_settings.pp_mode== 'preprod')
                }selected="selected"{/if}>preprod</option>
                <option value="test" {if (isset($pp_settings.pp_mode) && $pp_settings.pp_mode== 'test')
                }selected="selected"{/if}>test</option>

            </select>
        </div>
    </div>
</div>