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
    		$session->setData('additionalFields', array(
    				'accountOwner'  => $_POST['payment']['account_owner'],
    				'accountNumber' => $_POST['payment']['account_number'],
    		    )
    		);
    	}
    	
    	return parent::getOrderPlaceRedirectUrl();
    }
}