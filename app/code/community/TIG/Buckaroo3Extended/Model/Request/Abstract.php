<?php
class TIG_Buckaroo3Extended_Model_Request_Abstract extends TIG_Buckaroo3Extended_Model_Abstract
{
    protected $_vars;
    protected $_method;

    public function getVars()
    {
        return $this->_vars;
    }

    public function setVars($vars = array())
    {
        $this->_vars = $vars;
        
        return $this;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function setMethod($method = '')
    {
        $this->_method = $method;
        
        return $this;
    }

    public function __construct() {
        parent::__construct();

        $this->setVars(array());
    }

    public function sendRequest()
    {
    	if (empty($this->_order)) {
    		$this->_debugEmail .= "No order was set! :( \n";
    		Mage::getModel('buckaroo3extended/response_abstract', array('response' => false, 'XML' => false, 'debugEmail' => $this->_debugEmail))->processResponse();
    	}
    	
        Mage::dispatchEvent('buckaroo3extended_request_setmethod', array('request' => $this, 'order' => $this->_order));
        
        $responseModelClass = Mage::helper('buckaroo3extended')->isAdmin() ? 'buckaroo3extended/response_backendOrder' : 'buckaroo3extended/response_abstract';
        
        $this->_debugEmail .= 'Chosen payment method: ' . $this->_method . "\n";

        //if no method has been set (no payment method could identify the chosen method) process the order as if it had failed
        if (empty($this->_method)) {
            $this->_debugEmail .= "No method was set! :( \n";
            $responseModel = Mage::getModel(
            	$responseModelClass, 
            	array(
            		'response' => false, 
            		'XML' => false, 
            		'debugEmail' => $this->_debugEmail
            	)
            );
            if (!$responseModel->getOrder()) {
            	$responseModel->setOrder($this->_order);
            }
            
            $responseModel->processResponse();
        }

        //hack to prevent SQL errors when using onestepcheckout
        Mage::getSingleton('checkout/session')->getQuote()->setReservedOrderId(null)->save();

        $this->_debugEmail .= "\n";
        //forms an array with all payment-independant variables (such as merchantkey, order id etc.) which are required for the transaction request
        $this->_addBaseVariables();
        $this->_addOrderVariables();
        $this->_addShopVariables();
        $this->_addSoftwareVariables();

        $this->_debugEmail .= "Firing request events. \n";
        //event that allows individual payment methods to add additional variables such as bankaccount number
        Mage::dispatchEvent('buckaroo3extended_request_addservices', array('request' => $this, 'order' => $this->_order));
        Mage::dispatchEvent('buckaroo3extended_request_addcustomvars', array('request' => $this, 'order' => $this->_order));

        $this->_debugEmail .= "Events fired! \n";

        //clean the array for a soap request
        $this->setVars($this->_cleanArrayForSoap($this->getVars()));

        $this->_debugEmail .= "Variable array:" . var_export($this->_vars, true) . "\n\n";
        $this->_debugEmail .= "Building SOAP request... \n";

        //send the transaction request using SOAP
        $soap = Mage::getModel('buckaroo3extended/soap', array('vars' => $this->getVars(), 'method' => $this->getMethod()));
        list($response, $responseXML, $requestXML) = $soap->transactionRequest();


        $this->_debugEmail .= "The SOAP request has been sent. \n";
        
        if (!is_object($requestXML) || !is_object($responseXML)) { 
            $this->_debugEmail .= "Request or response was not an object \n";
        } else {
            $this->_debugEmail .= "Request: " . var_export($requestXML->saveXML(), true) . "\n";
            $this->_debugEmail .= "Response: " . var_export($response, true) . "\n";
            $this->_debugEmail .= "Response XML:" . var_export($responseXML->saveXML(), true) . "\n\n";
        }

        $this->_debugEmail .= "Processing response... \n";
        
        //process the response
        $responseModel = Mage::getModel(
            $responseModelClass,
            array(
                'response'   => $response,
                'XML'        => $responseXML,
                'debugEmail' => $this->_debugEmail,
            )
        );
        
        if (!$responseModel->getOrder()) {
            $responseModel->setOrder($this->_order);
        }
        $responseModel->processResponse();
    }

    protected function _addServices()
    {
        $this->_vars['services'][$this->_method] = array(
            'action'	=> 'Pay',
            'version'   => 1,
        );
    }

    protected function _addBaseVariables()
    {
        list($country, $locale, $lang) = $this->_getLocale();

	    //test mode can be set in the general config options, but also in the config options for the individual payment options.
		//The latter overwrites the first if set to true
		$test = Mage::getStoreConfig('buckaroo/buckaroo3extended/mode', Mage::app()->getStore()->getStoreId());

		if (Mage::getStoreConfig('buckaroo/buckaroo3extended' . $this->_code . '/mode', Mage::app()->getStore()->getStoreId())) {
			$test = '1';
		}

		$this->_vars['country']        = $country;
		$this->_vars['locale']         = $locale;
		$this->_vars['lang']           = $lang;
		$this->_vars['test']           = $test;

        $this->_debugEmail .= "Base variables added! \n";
    }

    protected function _addShopVariables()
    {
        $returnUrl = Mage::getUrl('buckaroo3extended/notify/return', array('_secure' => true));

		$merchantKey = Mage::getStoreConfig('buckaroo/buckaroo3extended/key', Mage::app()->getStore()->getStoreId());
		$description = Mage::getStoreConfig('buckaroo/buckaroo3extended/payment_description', Mage::app()->getStore()->getStoreId());
		$thumbprint  = Mage::getStoreConfig('buckaroo/buckaroo3extended/thumbprint', Mage::app()->getStore()->getStoreId());

		$this->_vars['returnUrl']      = $returnUrl;
		$this->_vars['merchantKey']    = $merchantKey;
		$this->_vars['description']    = $description;
		$this->_vars['thumbprint']     = $thumbprint;

        $this->_debugEmail .= "Shop variables added! \n";
    }
    
    protected function _addSoftwareVariables()
    {
        $platformName = 'Magento';
        
        if (method_exists('Mage', 'getEdition')) {
            $platformName .= ' ' . Mage::getEdition();
        }
        
        $platformVersion = Mage::getVersion();
        $moduleSupplier = 'Total Internet Group';
        $moduleName = 'Buckaroo3Extended';
        $moduleVersion = (string) Mage::getConfig()->getModuleConfig("TIG_Buckaroo3Extended")->version;
        
        $array = array(
            'PlatformName'    => $platformName,
            'PlatformVersion' => $platformVersion,
            'ModuleSupplier'  => $moduleSupplier,
            'ModuleName'      => $moduleName,
            'ModuleVersion'   => $moduleVersion,
        );
        
        $this->_vars['Software'] = $array;
        
        $this->_debugEmail .= "Software variables added! \n";
    }

    protected function _addOrderVariables()
    {
        list($currency, $totalAmount) = $this->_determineAmountAndCurrency();

        $this->_vars['currency']     = $currency;
        $this->_vars['amountCredit'] = 0;
        $this->_vars['amountDebit']  = $totalAmount;
        $this->_vars['orderId']      = $this->_order->getIncrementId();

        $this->_debugEmail .= "Order variables added! \n";
    }

    protected function _addRefundVariables()
    {
        $this->_vars['OriginalTransactionKey'] = $this->_invoice->getTransactionId();
        
        $this->_debugEmail .= "Refund variables added! \n";
    }
}