<?php

class osC_Payment_checkoutapipayment extends osC_Payment_Admin
{
    /**
     * The administrative title of the payment module
     *
     * @var string
     * @access private
     */
    var $_title;

    /**
     * The code of the payment module
     *
     * @var string
     * @access private
     */

    var $_code = 'checkoutapipayment';

    /**
     * The developers name
     *
     * @var string
     * @access private
     */

    var $_author_name = 'checkout.com';

    /**
     * The developers address
     *
     * @var string
     * @access private
     */

    var $_author_www = 'http://www.checkout.com';

    /**
     * The status of the module
     *
     * @var boolean
     * @access private
     */

    var $_status = false;

    public  function  __construct ()
    {
        global $osC_Language;

        $this->_title = $osC_Language->get('payment_checkoutapipayment_title');
        $this->_description = $osC_Language->get('payment_checkoutapipayment_description');
        $this->_method_title = $osC_Language->get('payment_checkoutapipayment_method_title');
        $this->_status = (defined('MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS') && (MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS == '1') ? true : false);
        $this->_sort_order = (defined('MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SORT_ORDER') ? MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SORT_ORDER : null);
    }

    public  function isInstalled()
    {
        return defined('MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS');
    }

    public  function install()
    {
        global $osC_Database, $osC_Language;
        parent::install();

        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, set_function, date_added) values
       ('* Enable Checkout.com payment', 'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS', '-1', 'Do you want to accept Checkout.com  Payments Standard payments?', '6', '0', 'osc_cfg_set_boolean_value(array(1, -1))', now())");

        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, date_added) values
       ('Sort order of display.', 'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");


        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, date_added) values
       ('* Publishable API Key', 'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PUBLISHABLE_KEY', '', 'The Checkout.com account publishable API key to use.', '6', '0', now())");


        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, date_added) values
       ('* Secret API Key', 'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SECRET_KEY', '', 'The Checkout.com account secret API key to use .', '6', '0', now())");



        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, use_function, set_function, date_added) values
       ('Payment Zone', 'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '0', 'osc_cfg_use_get_zone_class_title', 'osc_cfg_set_zone_classes_pull_down_menu', now())");


        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, set_function, use_function, date_added) values
       ('* Set Processing Order Status', 'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PROCESSING_ORDER_STATUS_ID', '" . ORDERS_STATUS_PROCESSING . "', 'When the customer is returned to the Checkout Complete page from checkout.com, this order status should be used', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");


        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, set_function, use_function, date_added) values
       ('* Set Checkout.com Acknowledged Order Status', 'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_ORDER_STATUS_ID', '" . ORDERS_STATUS_PAID . "', 'When the checkout.com payments is successfully made, this order status should be used', '6', '0', 'osc_cfg_set_order_statuses_pull_down_menu', 'osc_cfg_use_get_order_status_title', now())");


        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, set_function, date_added) values
       ('* Gateway Server', 'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_GATEWAY_SERVER', 'Live', 'Use the testing (sandbox) or live gateway server for transactions?', '6', '0', 'osc_cfg_set_boolean_value(array(\'Live\', \'Preprod\', \'Test\'))', now())");



        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, set_function, date_added) values
       ('Transaction Method', 'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_METHOD', 'Authorize', 'The processing method to use for each transaction.', '6', '0', 'osc_cfg_set_boolean_value(array(\'Authorize\', \'Authorize and Capture\'))', now())");


        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, date_added) values
       ('Set auto capture time.', 'MODULE_PAYMENT_CHECKOUAPIPAYMENT_AUTOCAPTIME', '0', 'Time taken by the gateway  to caputure the transaction.', '6', '0', now())");

        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, date_added) values
       ('Set Gateway timeout.', 'MODULE_PAYMENT_CHECKOUAPIPAYMENT_GATEWAY_TIMEOUT', '60', 'Set how long request timeout on server.', '6', '0', now())");

        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, set_function, date_added) values
       ('Enable localPayment', 'MODULE_PAYMENT_CHECKOUAPIPAYMENT_LOCALPAYMENT_ENABLE', '-1', 'Enable localpayment using the js.', '6', '0', 'osc_cfg_set_boolean_value(array(1, -1))', now())");

        $osC_Database->simpleQuery("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description,
      configuration_group_id, sort_order, set_function, date_added) values
       ('Method Type', 'MODULE_PAYMENT_CHECKOUAPIPAYMENT_TYPE', '-1', 'Verify gateway server SSL certificate on connection?.', '6', '0', 'osc_cfg_set_boolean_value(array(1, -1))', now())");

    }

    public function getKeys() {

        if (!isset($this->_keys)) {
            $this->_keys = array('MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SORT_ORDER',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PUBLISHABLE_KEY',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SECRET_KEY',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PROCESSING_ORDER_STATUS_ID',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_ORDER_STATUS_ID',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_GATEWAY_SERVER',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_ZONE',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_METHOD',
                'MODULE_PAYMENT_CHECKOUAPIPAYMENT_AUTOCAPTIME',
                'MODULE_PAYMENT_CHECKOUAPIPAYMENT_GATEWAY_TIMEOUT',
                'MODULE_PAYMENT_CHECKOUAPIPAYMENT_LOCALPAYMENT_ENABLE',
                'MODULE_PAYMENT_CHECKOUAPIPAYMENT_TYPE');
        }

        return $this->_keys;
    }

}