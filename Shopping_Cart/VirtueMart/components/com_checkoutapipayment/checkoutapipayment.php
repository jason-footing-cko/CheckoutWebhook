<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
if (!class_exists('VmConfig'))
    require(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');

if (!class_exists('VmModel')) {
    require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmmodel.php');
}

$json = file_get_contents('php://input');
if ($json) {
    $post_data = json_decode($json, true);
    $virtuemart_order_id = $post_data['metadata']['trackId'];
}

$modelOrder = VmModel::getModel('orders');
$order = $modelOrder->getOrder($virtuemart_order_id);
$order_history['order_status'] = 'C';
$order_history['customer_notified'] = 1;
$order_history['comments'] = '';
$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, TRUE);