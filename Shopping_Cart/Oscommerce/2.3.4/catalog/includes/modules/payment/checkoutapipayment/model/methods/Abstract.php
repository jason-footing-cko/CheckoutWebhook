<?php
abstract class model_methods_Abstract {

    public $code;
    public $title;
    public $description;
    public $enabled;
    private $_check;


    public function getEnabled()
    {
        return   defined('MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS') &&
        (MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS == 'True') ? true : false;
    }



    public function javascript_validation()
    {
        return false;
    }

    public function selection()
    {
        return array();
    }
    abstract public function pre_confirmation_check();

    abstract public function confirmation();

    public function process_button()
    {

    }

    public function after_process()
    {

    }

    public function get_error()
    {

    }
    public function check()
    {
        if (!isset($this->_check)) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION .
                " where configuration_key = 'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }


    public function keys()
    {
        return array(
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PUBLISHABLE_KEY',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SECRET_KEY',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_METHOD',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_ZONE',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_SERVER',
                'MODULE_PAYMENT_CHECKOUAPIPAYMENT_TYPE',
                'MODULE_PAYMENT_CHECKOUAPIPAYMENT_LOCALPAYMENT_ENABLE',
                'MODULE_PAYMENT_CHECKOUAPIPAYMENT_GATEWAY_TIMEOUT',
                'MODULE_PAYMENT_CHECKOUAPIPAYMENT_AUTOCAPTIME',
                'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SORT_ORDER'




        );
    }
    public function remove() {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function update_status() {
        global $order;

        if ( ($this->getEnabled ()) && ((int)MODULE_PAYMENT_CHECKOUTAPIPAYMENT_ZONE > 0) && ( isset($order) && is_object($order) ) ) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_STRIPE_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
    }

    function install($parameter = null) {
        $params = $this->getParams();

        if (isset($parameter)) {
            if (isset($params[$parameter])) {
                $params = array($parameter => $params[$parameter]);
            } else {
                $params = array();
            }
        }

        foreach ($params as $key => $data) {
            $sql_data_array = array('configuration_title' => $data['title'],
                'configuration_key' => $key,
                'configuration_value' => (isset($data['value']) ? $data['value'] : ''),
                'configuration_description' => $data['desc'],
                'configuration_group_id' => '6',
                'sort_order' => '0',
                'date_added' => 'now()');

            if (isset($data['set_func'])) {
                $sql_data_array['set_function'] = $data['set_func'];
            }

            if (isset($data['use_func'])) {
                $sql_data_array['use_function'] = $data['use_func'];
            }

            tep_db_perform(TABLE_CONFIGURATION, $sql_data_array);
        }
    }

    public function getParams()
    {
        $params = array('MODULE_PAYMENT_CHECKOUTAPIPAYMENT_STATUS' => array('title' => 'Enable Checkout.com Module',
            'desc' => 'Do you want to accept Stripe payments?',
            'value' => 'True',
            'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
            'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PUBLISHABLE_KEY' => array('title' => 'Publishable API Key',
                'desc' => 'The Checkout.com account publishable API key to use.',
                'value' => ''),
            'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SECRET_KEY' => array('title' => 'Secret API Key',
                'desc' => 'The Checkout.com account secret API key to use .',
                'value' => ''),


            'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
                'desc' => 'The processing method to use for each transaction.',
                'value' => 'Authorize',
                'set_func' => 'tep_cfg_select_option(array(\'Authorize\', \'Authorize and Capture\'), '),

            'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_ZONE' => array('title' => 'Payment Zone',
                'desc' => 'If a zone is selected, only enable this payment method for that zone.',
                'value' => '0',
                'use_func' => 'tep_get_zone_class_title',
                'set_func' => 'tep_cfg_pull_down_zone_classes('),
            'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_TRANSACTION_SERVER' => array('title' => 'Transaction Server',
                'desc' => 'Perform transactions on the production server or on the testing server.',
                'value' => 'Preprod',
                'set_func' => 'tep_cfg_select_option(array(\'Live\', \'Preprod\', \'Test\'), '),
            'MODULE_PAYMENT_CHECKOUAPIPAYMENT_TYPE' => array('title' => 'Method Type',
                'desc' => 'Verify gateway server SSL certificate on connection?',
                'value' => 'True',
                'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
            'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_PROXY' => array('title' => 'Proxy Server',
                'desc' => 'Send API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)'),
            'MODULE_PAYMENT_CHECKOUAPIPAYMENT_LOCALPAYMENT_ENABLE' => array('title' => 'Enable localPayment',

                'desc' => 'Enable localpayment using the js.',
                'value' => 'False',
                'set_func' => 'tep_cfg_select_option(array(\'True\', \'False\'), '
            ),
            'MODULE_PAYMENT_CHECKOUAPIPAYMENT_GATEWAY_TIMEOUT' => array('title' => 'Set Gateway timeout.',
                'desc' => 'Set how long request timeout on server.',
                'value' => '60'),
            'MODULE_PAYMENT_CHECKOUAPIPAYMENT_AUTOCAPTIME' => array('title' => 'Set auto capture time.',
                'desc' => 'When transaction is set to authorize and caputure , the gateway will use this time to caputure the transaction.',
                'value' => '0'),
            'MODULE_PAYMENT_CHECKOUTAPIPAYMENT_SORT_ORDER' => array('title' => 'Sort order of display.',
                'desc' => 'Sort order of display. Lowest is displayed first.',
                'value' => '0'));

        return $params;

    }

}