<link rel="stylesheet" type="text/css" href="view/stylesheet/checkoutapi.css" />

<?php echo $header; ?>
    <div id="content">

        <div class="breadcrumb">
            <?php foreach ($breadcrumbs as $breadcrumb)
            { ?>
                <?php echo $breadcrumb['separator']; ?>
                <a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
            <?php
            } ?>
        </div>

        <?php if ($error_warning)
        { ?>
            <div class="warning">
                    <?php echo $error_warning; ?>
            </div>
        <?php
        } ?>

        <div class="box">
            <div class="heading">
                <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
                <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a> <a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
            </div>
        </div>

        <div class="checkoutapi-wrapper">
            <a href="http://dev.checkout.com/" class="checkoutapi-logo" target="_blank">
                <img src="../admin/view/image/payment/checkout-logo@2x.png" alt="Checkout.com" border="0" />
            </a>

            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <p class="checkoutapi-intro">
                    <ul id="checkoutDevelops" class="checkoutDevelops">
                        <li>Checkout develops and operates our own payment gateway technology. No outsourcing, less risk</li>
                        <li>With PCI-Level 1 certification, we ensure the highest level of protection for merchants, consumers and data</li>
                        <li>Checkout technology supports many of the services we offer, including hosted payment pages, fraud <br> management systems, etc, helping you connect securely with your consumers</li>
                    </ul>
                </p>

                <div class="setting">
                    <h3 class="setting-header">Setting for Checkout.com Gateway 3.0</h3>
                        <div class="field">
                            <table class="form">
                                <tr>
                                    <td><?php echo $entry_test_mode; ?></td>
                                    <td><select name="test_mode">
                                            <?php if ($test_mode == 'Test') { ?>
                                            <option value="test" selected="selected"><?php echo $text_mode_test; ?></option>
                                            <?php } else { ?>
                                            <option value="test"><?php echo $text_mode_test; ?></option>
                                            <?php } ?>
                                            <?php if ($test_mode == 'Preprod') { ?>
                                            <option value="preprod" selected="selected"><?php echo $text_mode_prod; ?></option>
                                            <?php } else { ?>
                                            <option value="preprod"><?php echo $text_mode_prod; ?></option>
                                            <?php } ?>
                                            <?php if ($test_mode == 'Live') { ?>
                                            <option value="live" selected="selected"><?php echo $text_mode_live; ?></option>
                                            <?php } else { ?>
                                            <option value="live"><?php echo $text_mode_live; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_secret_key; ?></td>
                                    <td><input type="text" name="secret_key" value="<?php echo $secret_key; ?>" />
                                        <?php if ($error_secret_key) { ?>
                                        <span class="error"><?php echo $error_secret_key; ?></span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_public_key; ?></td>
                                    <td><input type="text" name="public_key" value="<?php echo $public_key; ?>" />
                                        <?php if ($error_public_key) { ?>
                                        <span class="error"><?php echo $error_public_key; ?></span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_localpayment_enable; ?></td>
                                    <td><input type="checkbox" name="localpayment_enable" value="<?php echo $localpayment_enable; ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_pci_enable; ?></td>
                                    <td><select name="pci_enable">
                                            <?php if ($pci_enable == 'Yes') { ?>
                                            <option value="yes" selected="selected"><?php echo $text_pci_yes; ?></option>
                                            <?php } else { ?>
                                            <option value="yes"><?php echo $text_pci_yes; ?></option>
                                            <?php } ?>
                                            <?php if ($pci_enable == 'No') { ?>
                                            <option value="no" selected="selected"><?php echo $text_pci_no; ?></option>
                                            <?php } else { ?>
                                            <option value="no"><?php echo $text_pci_no; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_payment_action; ?></td>
                                    <td><select name="checkout_test">
                                            <?php if ($checkout_method == 'Authorize only') { ?>
                                            <option value="authorization" selected="selected"><?php echo $text_auth_only; ?></option>
                                            <?php } else { ?>
                                            <option value="authorization"><?php echo $text_auth_only; ?></option>
                                            <?php } ?>
                                            <?php if ($checkout_method == 'Authorize and Capture') { ?>
                                            <option value="capture" selected="selected"><?php echo $text_auth_capture; ?></option>
                                            <?php } else { ?>
                                            <option value="capture"><?php echo $text_auth_capture; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_autocapture_delay; ?></td>
                                    <td><input type="text" name="autocapture_delay" value="0" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_card_type; ?></td>
                                    <td>

                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_successful_order_status; ?></td>
                                    <td><select name="checkout_successful_order">
                                            <?php foreach ($order_statuses as $order_status) { ?>
                                            <?php if ($order_status['order_status_id'] == $checkoutqiwi_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                            <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                            <?php } ?>
                                            <?php } ?>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_failed_order_status; ?></td>
                                    <td><select name="checkout_failed_order">
                                            <?php foreach ($order_statuses as $order_status) { ?>
                                            <?php if ($order_status['order_status_id'] == $checkoutqiwi_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                            <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                            <?php } ?>
                                            <?php } ?>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_status; ?></td>
                                    <td><select name="checkoutapipayment_status">
                                            <?php if ($checkoutapipayment_status) { ?>
                                            <option value="1" selected="selected"><?php echo $text_status_on; ?></option>
                                            <option value="0"><?php echo $text_status_off; ?></option>
                                            <?php } else { ?>
                                            <option value="1"><?php echo $text_status_on; ?></option>
                                            <option value="0" selected="selected"><?php echo $text_status_off; ?></option>
                                            <?php } ?>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td><?php echo $entry_sort_order; ?></td>
                                    <td><input type="text" name="checkoutapipayment_sort_order" value="<?php echo $checkoutapipayment_sort_order; ?>" size="1" /></td>
                                </tr>
                             </table>
                         </div>
                </form>
            </div>
        </div>
    </div>
<?php echo $footer;?>