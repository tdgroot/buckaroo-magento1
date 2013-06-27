<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Paymentguarantee_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
		'EUR',
	);

    protected $_code = 'buckaroo3extended_paymentguarantee';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_paymentguarantee_checkout_form';

    protected $_canRefund               = false;
    protected $_canRefundInvoicePartial = false;

    public function getOrderPlaceRedirectUrl()
    {
        $session = Mage::getSingleton('checkout/session');

        $accountNumber = $_POST[$this->_code.'_bpe_customer_account_number'];

        $session->setData(
        	'additionalFields',
            array(
            	'BPE_Customergender'    => $_POST[$this->_code.'_BPE_Customergender'],
                'BPE_AccountNumber'     => $this->filterAccount($accountNumber),
                'BPE_PhoneNumber'     => $_POST[$this->_code.'_bpe_customer_phone_number'],
        		'BPE_customerbirthdate' => date(
        			'Y-m-d', strtotime($_POST[$this->_code . '_customerbirthdate']['year']
        		    . '-' . $_POST[$this->_code.'_customerbirthdate']['month']
        		    . '-' . $_POST[$this->_code.'_customerbirthdate']['day'])
        		)
        	)
        );

    	return parent::getOrderPlaceRedirectUrl();
    }

    public function validate()
    {
        $postData = Mage::app()->getRequest()->getPost();
        if (
            !array_key_exists('buckaroo3extended_paymentguarantee_bpe_terms_and_conditions', $postData)
            || $postData['buckaroo3extended_paymentguarantee_bpe_terms_and_conditions'] != 'checked'
        ) {
            Mage::throwException(
                Mage::helper('buckaroo3extended')->__('Please accept the terms and conditions.')
            );
        }

        $this->getInfoInstance()->setAdditionalInformation('checked_terms_and_conditions', true);

        return parent::validate();
    }
}