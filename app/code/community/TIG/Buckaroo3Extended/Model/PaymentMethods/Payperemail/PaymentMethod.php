<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Payperemail_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
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

    protected $_code = 'buckaroo3extended_payperemail';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_payperemail_checkout_form';

    protected $_canUseInternal = true;

    public function assignData($data)
    {
    	if (!Mage::helper('buckaroo3extended')->isAdmin()) {
        	$session = Mage::getSingleton('checkout/session');
    	} else {
    		$session = Mage::getSingleton('core/session');
    	}

		$session->setData('additionalFields', array(
			'gender'    => $_POST['buckaroo3extended_payperemail_BPE_Customergender'],
		    'firstname' => $_POST['buckaroo3extended_payperemail_BPE_Customerfirstname'],
		    'lastname'  => $_POST['buckaroo3extended_payperemail_BPE_Customerlastname'],
		    'mail'      => $_POST['buckaroo3extended_payperemail_BPE_Customermail'],
		));
		return parent::assignData($data);
    }

    public function isAvailable($quote = null)
    {
        //check if max amount for Betaalgarant is set and if so, if the quote grandtotal exceeds that
        $maxAmount = Mage::getStoreConfig('buckaroo/buckaroo3extended_payperemail/max_amount', Mage::app()->getStore()->getStoreId());
        if (!empty($maxAmount)
            && !empty($quote)
            && $quote->getGrandTotal() > $maxAmount)
        {
            return false;
        }

        return parent::isAvailable($quote);
    }
}