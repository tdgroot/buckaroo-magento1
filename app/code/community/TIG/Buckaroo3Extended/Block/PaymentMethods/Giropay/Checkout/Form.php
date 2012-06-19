<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Giropay_Checkout_Form extends Mage_Payment_Block_Form
{
    public function __construct()
    {
		$this->setTemplate('buckaroo3extended/giropay/checkout/form.phtml');
        parent::_construct();
    }
}