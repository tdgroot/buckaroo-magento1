<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Amex_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
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

    protected $_code = 'buckaroo3extended_amex';
    protected $_formBlockType = 'buckaroo3extended/paymentMethods_amex_checkout_form';

    public function isAvailable($quote = null)
    {
        //check if max amount for Amex is set and if so, if the quote grandtotal exceeds that
        $maxAmount = Mage::getStoreConfig('buckaroo/buckaroo3extended_amex/max_amount', Mage::app()->getStore()->getStoreId());
        if (!empty($maxAmount)
            && !empty($quote)
            && $quote->getGrandTotal() > $maxAmount)
        {
            return false;
        }

        return parent::isAvailable($quote);
    }
}