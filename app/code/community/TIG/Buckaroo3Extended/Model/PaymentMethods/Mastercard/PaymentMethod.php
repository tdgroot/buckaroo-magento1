<?php 
class TIG_Buckaroo3Extended_Model_PaymentMethods_Mastercard_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
		'EUR',
		'GBP',
		'USD',
		'CAD',
		'SHR',
		'NOK',
		'SEK',
		'DKK',
        'ARS',
        'BRL',
        'HRK',
        'LTL',
        'TRY',
        'TRL',
        'AUD',
        'CNY',
        'LVL',
        'MXN',
        'MXP',
        'PLN',
        'CHF',
	);
	
    protected $_code = 'buckaroo3extended_mastercard';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_mastercard_checkout_form';
}