<?php 
class TIG_Buckaroo3Extended_Model_PaymentMethods_Giropay_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    protected $_payment;
    
    public function setPayment($payment)
    {
        $this->_payment = $payment;
    }
    
    public function getPayment()
    {
        return $this->_payment;
    }
    
    public $allowedCurrencies = array(
		'EUR',
	);
	
    protected $_code = 'buckaroo3extended_giropay';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_giropay_checkout_form';
    
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canSaveCc 				= false;
    
    public function getAllowedCurrencies()
    {
        return $this->allowedCurrencies;
    }
    
    public function setAllowedCurrencies($allowedCurrencies)
    {
        $this->allowedCurrencies = $allowedCurrencies;
    }
    
    public function getOrderPlaceRedirectUrl()
    {
    	$session = Mage::getSingleton('checkout/session');
    	
    	if(isset($_POST[$this->_code.'_BPE_Bankleitzahl']))
    	{
    		$session->setData('additionalFields', array('Bankleitzahl' => $_POST['buckaroo3extended_giropay_BPE_Bankleitzahl']));
    	}
    	
    	return Mage::getUrl('buckaroo3extended/checkout/checkout', array('_secure' => true, 'method' => $this->_code));
    }
    
    public function refund(Varien_Object $payment, $amount)
    {
        if (!$this->canRefund() || !$this->isRefundAvailable($payment)) {
            Mage::throwException($this->_getHelper()->__('Refund action is not available.'));
        }
        
        $refundRequest = Mage::getModel(
            'buckaroo3extended/refund_request_abstract', 
            array(
                'payment' => $payment, 
                'amount' => $amount
            )
        );
        
        try {
            $refundRequest->sendRefundRequest();
            $this->setPayment($refundRequest->getPayment());
        } catch (Exception $e) {
            Mage::helper('buckaroo3extended')->logException($e);
            Mage::throwException($e->getMessage());
        }
        
        return $this;
    }
    
    public function isRefundAvailable($payment)
    {
        if (!$payment->getOrder()->getTransactionKey()) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('buckaroo3extended')->__(
                        'The order is missing a transaction key. Possibly this order was created using an older version of the Buckaroo module that did not yet support refunding.'
                    )
                );
            throw new Exception('The order is missing a transaction key. Possibly this order was created using an older version of the Buckaroo module that did not yet support refunding.');
            return false;
        }
        
        if (!Mage::getStoreConfig('buckaroo/buckaroo3extended_refund/active', Mage::app()->getStore()->getStoreId())) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('buckaroo3extended')->__(
                        'Buckaroo refunding is currently disabled in the configuration menu.'
                    )
                );
            throw new Exception('Buckaroo refunding is currently disabled in the configuration menu.');
            return false;
        }
        
        return true;
    }
    
    public function isAvailable($quote = null)
    {
        if (!TIG_Buckaroo3Extended_Model_Request_Availability::canUseBuckaroo($quote)) {
    		return false;
    	}
    
    	//check if the country specified in the billing address is allowed to use this payment method
    	if (Mage::getStoreConfig('buckaroo/buckaroo3extended_giropay/allowspecific', Mage::app()->getStore()->getStoreId()) == 1
    		&& $quote->getBillingAddress()->getCountry()) 
    	{
    		$allowedCountries = explode(',',Mage::getStoreConfig('buckaroo/buckaroo3extended_giropay/specificcountry', Mage::app()->getStore()->getStoreId()));
    		$country = $quote->getBillingAddress()->getCountry();
    		
    		if (!in_array($country,$allowedCountries)) {
    			return false;
    		}
    	}
    	
    	//check if the module is set to enabled
    	if (!Mage::getStoreConfig('buckaroo/buckaroo3extended_giropay/active', Mage::app()->getStore()->getStoreId())) {
    		return false;
    	}
    	
    	//limit by ip
    	if (mage::getStoreConfig('dev/restrict/allow_ips') && Mage::getStoreConfig('buckaroo/buckaroo3extended_giropay/limit_by_ip'))
    	{
    		$allowedIp = explode(',', mage::getStoreConfig('dev/restrict/allow_ips'));
    		if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIp))
    		{
    			return false;
    		}
    	}
    	
        // get current currency code
        $currency = Mage::app()->getStore()->getBaseCurrencyCode();

        // currency is not available for this module
        if (!in_array($currency, $this->allowedCurrencies))
        {
            return false;
        }
                  
        return parent::isAvailable($quote);
    }
}