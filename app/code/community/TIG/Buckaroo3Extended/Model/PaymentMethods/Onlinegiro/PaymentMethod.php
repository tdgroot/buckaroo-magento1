<?php 
class TIG_Buckaroo3Extended_Model_PaymentMethods_Onlinegiro_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
		'EUR',
	);
	
    protected $_code = 'buckaroo3extended_onlinegiro';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_onlinegiro_checkout_form';
        
    public function getOrderPlaceRedirectUrl()
    {
        $session = Mage::getSingleton('checkout/session');

		$session->setData('additionalFields', array(
			'gender'    => $_POST['buckaroo3extended_onlinegiro_BPE_Customergender'],
		    'firstname' => $_POST['buckaroo3extended_onlinegiro_BPE_Customerfirstname'],
		    'lastname'  => $_POST['buckaroo3extended_onlinegiro_BPE_Customerlastname'],
		    'mail'      => $_POST['buckaroo3extended_onlinegiro_BPE_Customermail'],
		));
    	
    	return parent::getOrderPlaceRedirectUrl();
    }
}