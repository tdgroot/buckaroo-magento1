<?php 
class TIG_Buckaroo3Extended_Model_PaymentMethods_Empayment_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
		'EUR',
	);
	
    protected $_code = 'buckaroo3extended_empayment';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_empayment_checkout_form';
    
    public function getOrderPlaceRedirectUrl()
    {
        $session = Mage::getSingleton('checkout/session');
        
        $session->setData(
        	'additionalFields', 
            array(
            	'DOM' => array(
                    'accountHolder' => $_POST['buckaroo3extended_empayment_BPE_Accountholder'],
                    'accountNumber' => $_POST['buckaroo3extended_empayment_BPE_Accountnumber'],
                    'bankId'        => $_POST['buckaroo3extended_empayment_BPE_Bankid'],
                ),
        	)
        );  	
        		
    	return parent::getOrderPlaceRedirectUrl();
    }
    
    public function isAvailable($quote = null)
    {
        //check if max amount for Betaalgarant is set and if so, if the quote grandtotal exceeds that
    	$maxAmount = Mage::getStoreConfig('buckaroo/buckaroo3extended_empayment/max_amount', Mage::app()->getStore()->getStoreId());
    	if (!empty($maxAmount)
    		&& !empty($quote) 
    		&& $quote->getGrandTotal() > $maxAmount) 
    	{
    		return false;
    	}
                  
        return parent::isAvailable($quote);
    }
}