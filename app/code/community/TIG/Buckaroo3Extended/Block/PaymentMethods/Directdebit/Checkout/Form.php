<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Directdebit_Checkout_Form extends TIG_Buckaroo3Extended_Block_PaymentMethods_Checkout_Form_Abstract
{
    public function __construct()
    {
		$this->setTemplate('buckaroo3extended/directdebit/checkout/form.phtml');
        parent::_construct();
    }
}