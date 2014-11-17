<?php
class CheckoutapipaymentPaymentModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();

        $checkoutApiModule =  models_FactoryInstance::getInstance( 'checkoutapipayment' );

        $smartyParam = $checkoutApiModule->getInstanceMethod()->hookPayment(array());

        $smartyParam['local_path'] = $this->module->getPathUri();
        $smartyParam['module_dir'] = $this->module->getPathUri();
        $this->context->smarty->assign($smartyParam);

        $this->setTemplate('confirmation.tpl');
    }
}