<?php 
class TIG_Buckaroo3Extended_Model_PaymentMethods_Directdebit_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    public $allowedCurrencies = array(
		'EUR',
	);
	
    /**
    * unique internal payment method identifier
    * 
    * @var string [a-z0-9_]
    */
    protected $_code = 'buckaroo3extended_directdebit';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_directdebit_checkout_form';
    /**
     * Here are examples of flags that will determine functionality availability
     * of this module to be used by frontend and backend.
     * 
     * @see all flags and their defaults in Mage_Payment_Model_Method_Abstract
     *
     * It is possible to have a custom dynamic logic by overloading
     * public function can* for each flag respectively
     */
     
    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
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
    	
    	if(isset($_POST['payment']))
    	{
    		$session->setData('additionalFields', array(
    				'accountOwner'  => $_POST['payment']['account_owner'],
    				'accountNumber' => $_POST['payment']['account_number'],
    		    )
    		);
    	}
    	
    	return Mage::getUrl('buckaroo3extended/checkout/checkout', array('_secure' => true, 'method' => $this->_code));
    }
    
    public function isAvailable($quote = null)
    {
        if (!TIG_Buckaroo3Extended_Model_Request_Availability::canUseBuckaroo()) {
    		return false;
    	}
    
    	//check if the module is set to enabled
    	if (!Mage::getStoreConfig('buckaroo/buckaroo3extended_directdebit/active', Mage::app()->getStore()->getStoreId())) {
    		return false;
    	}
    	
    	//check if the country specified in the billing address is allowed to use this payment method
    	if (Mage::getStoreConfig('buckaroo/buckaroo3extended_directdebit/allowspecific', Mage::app()->getStore()->getStoreId()) == 1
    		&& $quote->getBillingAddress()->getCountry()) 
    	{
    		$allowedCountries = explode(',',Mage::getStoreConfig('buckaroo/buckaroo3extended_directdebit/specificcountry', Mage::app()->getStore()->getStoreId()));
    		$country = $quote->getBillingAddress()->getCountry();
    		
    		if (!in_array($country,$allowedCountries)) {
    			return false;
    		}
    	}
    	
    	//limit by ip
    	if (mage::getStoreConfig('dev/restrict/allow_ips') && Mage::getStoreConfig('buckaroo/buckaroo3extended_directdebit/limit_by_ip'))
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