<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Payperemail_Checkout_Form extends Mage_Payment_Block_Form
{
    public function __construct()
    {
		$this->setTemplate('buckaroo3extended/payperemail/checkout/form.phtml');
        parent::_construct();
    }
}