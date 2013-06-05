<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Transfer_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
        'EUR',
    );

    protected $_code = 'buckaroo3extended_transfer';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_transfer_checkout_form';

    protected $_canRefund               = false;
    protected $_canRefundInvoicePartial = false;

    public function getOrderPlaceRedirectUrl()
    {
        $session = Mage::getSingleton('checkout/session');

        if (isset($_POST[$this->_code.'_BPE_Customergender'])) {
            $session->setData('additionalFields',array('BPE_Customergender' => $_POST[$this->_code.'_BPE_Customergender'],
            	'BPE_Customermail' => $_POST[$this->_code.'_BPE_Customermail'],
            	'BPE_customerbirthdate' => date('Y-m-d', strtotime($_POST[$this->_code . '_customerbirthdate']['year']
            		. '-' . $_POST[$this->_code.'_customerbirthdate']['month']
            		. '-' . $_POST[$this->_code.'_customerbirthdate']['day']))));
        }

    	return parent::getOrderPlaceRedirectUrl();
    }
}