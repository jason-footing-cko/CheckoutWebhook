<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

use Tygh\BlockManager\RenderManager;
use Tygh\BlockManager\Block;
use Tygh\BlockManager\SchemesManager;
include DIR_ROOT.'/app/payments/CheckoutApi/autoload.php';

function smarty_function_checkoutapijs($params, &$smarty)
{
    $adminSetting = fn_get_checkoutapipayment_settings();

    $adminSetting['server_type'] = 'no';
    $cart = $_SESSION['cart'];
    $processorModel = CheckoutApi_Lib_Factory::getInstance('Model')->getInstance($adminSetting);


    return $processorModel->getExtraHtml($cart,$adminSetting);
}
