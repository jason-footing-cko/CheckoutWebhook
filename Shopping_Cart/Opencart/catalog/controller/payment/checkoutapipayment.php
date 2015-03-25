<?php
include ('includes/autoload.php');
class ControllerPaymentcheckoutapipayment extends Controller_Model
{
    protected function index()
    {
        parent::index();
       //

        $this->render();
    }


    public function webhook()
    {
        $stringCharge     =    file_get_contents("php://input");
        $Api = CheckoutApi_Api::getApi(array('mode'=> $this->config->get('test_mode')));

        $objectCharge = $Api->chargeToObj($stringCharge);

        if($objectCharge->isValid()) {
          //  $this->load->model('sale/order');
           /*
            * Need to get track id
            */
            $order_id = $objectCharge->getMetadata()->getTrackId();

            $modelOrder = $this->load->model('checkout/order');

            $order_statuses = $this->getOrderStatuses();
            $status_mapped = array();

            foreach($order_statuses as $status){
                $status_mapped[$status['name']] = $status['order_status_id'];
            }


            if ( $objectCharge->getCaptured ()) {
                $this->model_checkout_order->update(
                    $order_id,
                    $status_mapped['Complete'],
                    "",
                    true
                );
                echo "Order has been captured";

            } elseif ( $objectCharge->getRefunded () ) {
                $this->model_checkout_order->update(
                    $order_id,
                    $status_mapped['Refunded'],
                    "",
                    true
                );
                echo "Order has been refunded";

            } else {
                $this->model_checkout_order->update(
                    $order_id,
                    $this->config->get('checkout_failed_order'),
                    "",
                    true
                );
                echo "Order has been Cancel";
            }
        }
    }

    private function _process()
    {
        $config['chargeId']    =    $_GET['chargeId'];
        $config['authorization']    =    $this->config->get('secret_key');
        $Api = CheckoutApi_Api::getApi(array('mode'=> $this->config->get('test_mode')));
        $respondBody    =    $Api->getCharge($config);

        $json = $respondBody->getRawOutput();
        return $json;
    }

    private function getOrderStatuses($data = array()) {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "order_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'";

            $sql .= " ORDER BY name";

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);

            return $query->rows;
        } else {
            $order_status_data = $this->cache->get('order_status.' . (int)$this->config->get('config_language_id'));

            if (!$order_status_data) {
                $query = $this->db->query("SELECT order_status_id, name FROM " . DB_PREFIX . "order_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name");

                $order_status_data = $query->rows;

                $this->cache->set('order_status.' . (int)$this->config->get('config_language_id'), $order_status_data);
            }

            return $order_status_data;
        }
    }

}
