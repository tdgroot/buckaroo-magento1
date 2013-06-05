<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Directdebit_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
		'EUR',
	);

    protected $_code = 'buckaroo3extended_directdebit';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_directdebit_checkout_form';

    public function getOrderPlaceRedirectUrl()
    {
    	$session = Mage::getSingleton('checkout/session');

    	if(isset($_POST['payment']))
    	{
            $accountNumber = $_POST['payment']['account_number'];
    		$session->setData('additionalFields', array(
    				'accountOwner'  => $_POST['payment']['account_owner'],
    				'accountNumber' => $this->_validateAccount();
    		    )
    		);
    	}

    	return parent::getOrderPlaceRedirectUrl();
    }
<<<<<<< HEAD
=======

    public function isAvailable($quote = null)
    {
        //check if max amount for Directdebit is set and if so, if the quote grandtotal exceeds that
        $maxAmount = Mage::getStoreConfig('buckaroo/buckaroo3extended_directdebit/max_amount', Mage::app()->getStore()->getStoreId());
        if (!empty($maxAmount)
            && !empty($quote)
            && $quote->getGrandTotal() > $maxAmount)
        {
            return false;
        }

        return parent::isAvailable($quote);
    }

    protected function _validateAccount($accountNumber)
    {
        $filteredAccount = str_replace('.', '', $accountNumber);

        return $filteredAccount;
    }
>>>>>>> f38edcfd655641279c5c608ef95078fe8f6ff4f7
}