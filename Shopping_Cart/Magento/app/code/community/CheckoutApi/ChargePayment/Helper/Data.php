<?php

class CheckoutApi_ChargePayment_Helper_Data  extends Mage_Core_Helper_Abstract
{
    public function getConfigData($field,$section,$storeId = null)
    {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore();
        }
        $path = "payment/$section/".$field;
        return Mage::getStoreConfig($path, $storeId);
    }
}