<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Sofortueberweisung_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
		'EUR',
	);

    protected $_code = 'buckaroo3extended_sofortueberweisung';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_sofortueberweisung_checkout_form';

    public function isAvailable($quote = null)
    {
        //check if max amount for Sofortueberweisung is set and if so, if the quote grandtotal exceeds that
        $maxAmount = Mage::getStoreConfig('buckaroo/buckaroo3extended_sofortueberweisung/max_amount', Mage::app()->getStore()->getStoreId());
        if (!empty($maxAmount)
            && !empty($quote)
            && $quote->getGrandTotal() > $maxAmount)
        {
            return false;
        }

        return parent::isAvailable($quote);
    }
}