<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Giftcards_Checkout_Form extends Mage_Payment_Block_Form
{
    public function __construct()
    {
		$this->setTemplate('buckaroo3extended/giftcards/checkout/form.phtml');
        parent::_construct();
    }
}