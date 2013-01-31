<?php 
class TIG_Buckaroo3Extended_Model_PaymentMethods_Maestro_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
		'EUR',
		'GBP',
		'USD',
		'CAD',
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
        'MXN',
        'MXP',
        'PLN',
        'CHF',
	);
	
    protected $_code = 'buckaroo3extended_maestro';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_maestro_checkout_form';
}