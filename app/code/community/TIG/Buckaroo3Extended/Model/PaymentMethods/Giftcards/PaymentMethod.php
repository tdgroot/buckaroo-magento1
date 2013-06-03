<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Giftcards_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
		'EUR',
	);

    protected $_code = 'buckaroo3extended_giftcards';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_giftcards_checkout_form';

    protected $_canRefund               = false;
    protected $_canRefundInvoicePartial = false;

    public function isAvailable($quote = null)
    {
        //check if max amount for Giftcards is set and if so, if the quote grandtotal exceeds that
        $maxAmount = Mage::getStoreConfig('buckaroo/buckaroo3extended_giftcards/max_amount', Mage::app()->getStore()->getStoreId());
        if (!empty($maxAmount)
            && !empty($quote)
            && $quote->getGrandTotal() > $maxAmount)
        {
            return false;
        }

        return parent::isAvailable($quote);
    }
}