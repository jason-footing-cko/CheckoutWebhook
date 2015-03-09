<?php

require_once('../includes/modules/payment/includes/autoload.php');

require('includes/application_top.php');
require('includes/classes/currencies.php');
require('includes/classes/tax.php');
require('includes/classes/order.php');
require('includes/classes/customers.php');
require('includes/classes/payment.php');
require('includes/classes/shopping_cart_adapter.php');
require('includes/classes/products.php');
require('includes/classes/shipping.php');
require('includes/classes/gift_certificates.php');
require('includes/classes/orders_status.php');
require('includes/classes/invoices.php');

$osC_Currencies = new osC_Currencies_Admin();

if (defined('MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS') && MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS == '1') {
	global $osC_Currencies;
	function _process ()
	{
		$config['chargeId'] = $_GET['chargeId'];
		$config['authorization'] = MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_SERVER;
		$Api = CheckoutApi_Api::getApi(array( 'mode'=> MODULE_PAYMENT_CHECKOUTAPIPAYMENT_GATEWAY_SERVER));
		$respondBody    =    $Api->getCharge($config);
		$json = $respondBody->getRawOutput();
		return $json;
	}
	function _updateStatus($id, $data) {
		global $osC_Database, $osC_Language, $orders_status_array;

		$error = false;

		$osC_Database->startTransaction();

		$orders_status = osC_OrdersStatus_Admin::getData($data['status_id']);

		if ($orders_status['downloads_flag'] == 1) {
			osC_Order::activeDownloadables($id);
		}

		if ($orders_status['gift_certificates_flag'] == 1) {
			osC_Order::activeGiftCertificates($id);
		}

		if (($data['status_id'] == ORDERS_STATUS_CANCELLED) && ($data['restock_products'] == true)) {
			$Qproducts = $osC_Database->query('select orders_products_id, products_id, products_type, products_quantity from :table_orders_products where orders_id = :orders_id');
			$Qproducts->bindTable(':table_orders_products', TABLE_ORDERS_PRODUCTS);
			$Qproducts->bindInt(':orders_id', $id);
			$Qproducts->execute();

			while ($Qproducts->next()) {
				$result = osC_Product::restock($id, $Qproducts->valueInt('orders_products_id'), $Qproducts->valueInt('products_id'), $Qproducts->valueInt('products_quantity'));

				if ($result == false) {
					$error = true;
					break;
				}
			}
		}

		$Qupdate = $osC_Database->query('update :table_orders set orders_status = :orders_status, last_modified = now() where orders_id = :orders_id');
		$Qupdate->bindTable(':table_orders', TABLE_ORDERS);
		$Qupdate->bindInt(':orders_status', $data['status_id']);
		$Qupdate->bindInt(':orders_id', $id);
		$Qupdate->setLogging($_SESSION['module'], $id);
		$Qupdate->execute();

		if (!$osC_Database->isError()) {
			$Qupdate = $osC_Database->query('insert into :table_orders_status_history (orders_id, orders_status_id, date_added, customer_notified, comments) values (:orders_id, :orders_status_id, now(), :customer_notified, :comments)');
			$Qupdate->bindTable(':table_orders_status_history', TABLE_ORDERS_STATUS_HISTORY);
			$Qupdate->bindInt(':orders_id', $id);
			$Qupdate->bindInt(':orders_status_id', $data['status_id']);
			$Qupdate->bindInt(':customer_notified', ( $data['notify_customer'] === true ? '1' : '0'));
			$Qupdate->bindValue(':comments', $data['comment']);
			$Qupdate->setLogging($_SESSION['module'], $id);
			$Qupdate->execute();

			if ($osC_Database->isError()) {
				$error = true;
			}

			if ($data['notify_customer'] === true) {
				$Qorder = $osC_Database->query('select o.customers_name, o.customers_email_address, s.orders_status_name, o.date_purchased from :table_orders o, :table_orders_status s where o.orders_status = s.orders_status_id and s.language_id = :language_id and o.orders_id = :orders_id');
				$Qorder->bindTable(':table_orders', TABLE_ORDERS);
				$Qorder->bindTable(':table_orders_status', TABLE_ORDERS_STATUS);
				$Qorder->bindInt(':language_id', $osC_Language->getID());
				$Qorder->bindInt(':orders_id', $id);
				$Qorder->execute();

				require_once('../includes/classes/email_template.php');
				$email_template = toC_Email_Template::getEmailTemplate('admin_order_status_updated');
				$email_template->setData($id, osc_href_link(FILENAME_ACCOUNT, 'orders=' . $id, 'SSL', false, true, true), osC_DateTime::getLong($Qorder->value('date_purchased')), $data['append_comment'], $data['comment'], $Qorder->value('orders_status_name'), $Qorder->value('customers_name'), $Qorder->value('customers_email_address'));
				$email_template->buildMessage();
				$email_template->sendEmail();
			}
		} else {
			$error = true;
		}

		if ($error === false) {
			$osC_Database->commitTransaction();

			return true;
		}

		$osC_Database->rollbackTransaction();

		return false;
	}

	if(isset($_GET['chargeId'])) {
		$stringCharge = true;//_process();// file_get_contents("php://input");
	} else {
		$stringCharge = file_get_contents("php://input");
	}

	if($stringCharge) {
		$Api    =    CheckoutApi_Api::getApi(array( 'mode'=> MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_SERVER));
		$objectCharge = $Api->chargeToObj($stringCharge);

		$orderId =$objectCharge->getMetadata()->getTrackId();
		if($orderId) {

			$osC_Order = new osC_Order($orderId);

			if($objectCharge->getCaptured() && !$objectCharge->getRefunded()) {
				if($osC_Order->info['orders_status_id'] != ORDERS_STATUS_PAID) {
					echo "Order has #$orderId was  set complete";

					$data = array( 'status_id'        =>  ORDERS_STATUS_PAID,
					               'comment'          =>     "Order  was# $orderId   set paid by Checkout.com",
					               'restock_products' =>  false,
					               'notify_customer'  =>  false,
					               'append_comment'   =>  true
					);

					_updateStatus($orderId,$data);
				}else {
					echo  "Order has #$orderId was already set complete";
				}

			} elseif($objectCharge->getCaptured() && $objectCharge->getRefunded()) {

				foreach($osC_Order->_contents as $product) {
					$toRQty[] = $product['orders_products_id'].':'.$product['qty'];
				}
				$RQtryString = implode(';',$toRQty);

				$data = array('orders_id' => $orderId,
				              'sub_total' => $osC_Order->_sub_total,

				              'return_quantity' =>$RQtryString,
				              'comments' => 'Refund on CHeckout.com',
				              'restock_quantity' =>  true );

				if(toC_Invoices_Admin::createStoreCredit($data)){
					echo "Order has #$orderId was  set void ";
				}else {
					echo "Order has #$orderId was already set void ";
				}

			} elseif(!$objectCharge->getCaptured() && $objectCharge->getRefunded()) {
				if($orders_status_id->info['orders_status_id'] != ORDERS_STATUS_PAID) {
					$data = array (
						'status_id' => ORDERS_STATUS_CANCELLED ,
						'comment' => "Order  was #$orderId   set cancel by Checkout.com" ,
						'restock_products' => false ,
						'notify_customer' => false ,
						'append_comment' => true
					);

					_updateStatus ( $orderId , $data );
					echo "Order has #$orderId was  set void ";
				}else {
					echo "Order has #$orderId was already set void ";
				}
			}

		}
	}


}