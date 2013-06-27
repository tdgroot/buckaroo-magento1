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

        $accountNumber = $_POST['buckaroo3extended_empayment_BPE_Accountnumber'];

        $session->setData(
        	'additionalFields',
            array(
            	'DOM' => array(
                    'accountHolder' => $_POST['buckaroo3extended_empayment_BPE_Accountholder'],
                    'accountNumber' => $this->filterAccount($accountNumber),
                    'bankId'        => $_POST['buckaroo3extended_empayment_BPE_Bankid'],
                ),
        	)
        );

    	return parent::getOrderPlaceRedirectUrl();
    }
}