<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Directdebit_Checkout_Form extends TIG_Buckaroo3Extended_Block_PaymentMethods_Checkout_Form_Abstract
{
    public function __construct()
    {
		$this->setTemplate('buckaroo3extended/directdebit/checkout/form.phtml');
        parent::_construct();
    }
    
    public function getAccountOwner()
    {
        return $this->getSession()->getData('payment[account_owner]');
    }
    
    public function getAccountNumber()
    {
        return $this->getSession()->getData('payment[account_number]');
    }
}