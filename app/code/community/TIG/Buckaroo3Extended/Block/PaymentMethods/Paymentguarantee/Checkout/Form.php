<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Paymentguarantee_Checkout_Form extends Mage_Payment_Block_Form
{
    public function __construct()
    {
		$this->setTemplate('buckaroo3extended/paymentguarantee/checkout/form.phtml');
        parent::_construct();
    }
}