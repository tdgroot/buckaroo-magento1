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

    public function isAvailable($quote = null)
    {
        //check if max amount for Paypal is set and if so, if the quote grandtotal exceeds that
        $maxAmount = Mage::getStoreConfig('buckaroo/buckaroo3extended_paypal/max_amount', Mage::app()->getStore()->getStoreId());
        if (!empty($maxAmount)
            && !empty($quote)
            && $quote->getGrandTotal() > $maxAmount)
        {
            return false;
        }

        return parent::isAvailable($quote);
    }
}