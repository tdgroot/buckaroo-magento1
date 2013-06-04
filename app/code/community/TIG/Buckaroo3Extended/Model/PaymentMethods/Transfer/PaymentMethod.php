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

    public function isAvailable($quote = null)
    {
        //check if max amount for Transfer is set and if so, if the quote grandtotal exceeds that
        $maxAmount = Mage::getStoreConfig('buckaroo/buckaroo3extended_transfer/max_amount', Mage::app()->getStore()->getStoreId());
        if (!empty($maxAmount)
            && !empty($quote)
            && $quote->getGrandTotal() > $maxAmount)
        {
            return false;
        }

        return parent::isAvailable($quote);
    }
}