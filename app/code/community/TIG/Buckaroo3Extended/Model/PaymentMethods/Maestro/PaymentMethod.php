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

    public function isAvailable($quote = null)
    {
        //check if max amount for Maestro is set and if so, if the quote grandtotal exceeds that
        $maxAmount = Mage::getStoreConfig('buckaroo/buckaroo3extended_maestro/max_amount', Mage::app()->getStore()->getStoreId());
        if (!empty($maxAmount)
            && !empty($quote)
            && $quote->getGrandTotal() > $maxAmount)
        {
            return false;
        }

        return parent::isAvailable($quote);
    }
}