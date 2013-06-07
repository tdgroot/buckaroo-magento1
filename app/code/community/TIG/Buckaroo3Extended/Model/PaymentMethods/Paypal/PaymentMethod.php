<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Paypal_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
		'EUR',
		'USD',
		'GBP',
		'CAD',
		'AUD',
		'SEK'
	);

    protected $_code = 'buckaroo3extended_paypal';
    protected $_formBlockType = 'buckaroo3extended/paymentMethods_paypal_checkout_form';
}