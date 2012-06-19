<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Ideal_Checkout_Form extends Mage_Payment_Block_Form
{
    public function __construct()
    {
		$this->setTemplate('buckaroo3extended/ideal/checkout/form.phtml');
        parent::_construct();
    }
}