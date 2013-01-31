<?php 
class TIG_Buckaroo3Extended_Model_PaymentMethods_Giropay_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
		'EUR',
	);
	
    protected $_code = 'buckaroo3extended_giropay';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_giropay_checkout_form';
}