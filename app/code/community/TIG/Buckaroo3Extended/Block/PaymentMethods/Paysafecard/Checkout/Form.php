<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Paysafecard_Checkout_Form extends Mage_Payment_Block_Form
{
    public function __construct()
    {
		$this->setTemplate('buckaroo3extended/paysafecard/checkout/form.phtml');
        parent::_construct();
    }
}