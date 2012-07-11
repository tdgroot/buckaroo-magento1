<?php
class TIG_Buckaroo3Extended_Model_Refund_Request_Abstract extends TIG_Buckaroo3Extended_Model_Request_Abstract
{
    public function __construct($payment) {
        if (strpos(__DIR__, '/Model') !== false) {
	        $dir = str_replace('/Model/Refund/Request', '/certificate', __DIR__);
	    } else {
	        $dir = str_replace('/includes/src', '/app/code/community/TIG/Buckaroo3Extended/certificate', __DIR__);
	    }
	    define('CERTIFICATE_DIR', $dir);
	    
		$this->setOrder($payment->getOrder());
		$this->setSession(Mage::getSingleton('core/session'));
		$this->_setOrderBillingInfo();
		$this->setDebugEmail('');
		
		$this->_checkExpired();

        Mage::dispatchEvent('buckaroo3extended_request_setmethod', array('request' => $this, 'order' => $this->_order));

        $this->setVars(array());
    }

    public function sendRefundRequest()
    {
        $this->_debugEmail .= 'Chosen payment method: ' . $this->_method . "\n";

        //if no method has been set (no payment method could identify the chosen method) process the order as if it had failed
        if (empty($this->_method)) {
            $this->_debugEmail .= "No method was set! :( \n";
            Mage::getModel('buckaroo3extended/refund_response_abstract', array('response' => false, 'XML' => false))->processResponse();
        }

        $this->_debugEmail .= "\n";
        //forms an array with all payment-independant variables (such as merchantkey, order id etc.) which are required for the transaction request
        $this->_addBaseVariables();
        $this->_addOrderVariables();
        $this->_addShopVariables();

        $this->_debugEmail .= "Firing request events. \n";
        //event that allows individual payment methods to add additional variables such as bankaccount number
        Mage::dispatchEvent('buckaroo3extended_refund_request_addservices', array('request' => $this, 'order' => $this->_order));
        Mage::dispatchEvent('buckaroo3extended_refund_request_addcustomvars', array('request' => $this, 'order' => $this->_order));
        
        $this->_debugEmail .= "Events fired! \n";

        //clean the array for a soap request
        $this->setVars($this->_cleanArrayForSoap($this->getVars()));

        $this->_debugEmail .= "Variable array:" . var_export($this->_vars, true) . "\n\n";
        $this->_debugEmail .= "Time to build the soap request... \n";

        //send the transaction request using SOAP
        $soap = Mage::getModel('buckaroo3extended/soap', array('vars' => $this->getVars(), 'method' => $this->getMethod()));
        list($response, $responseXML, $requestXML) = $soap->transactionRequest();

        echo '<pre>';var_dump(array(
        $this->_vars,
        $requestXML->saveXML(),
        $response,
        $responseXML->saveXML()
        ));
        exit;

        $this->_debugEmail .= "Soap sent! \n";
        $this->_debugEmail .= "Request: " . var_export($requestXML->saveXML(), true) . "\n";
        $this->_debugEmail .= "Response: " . var_export($response, true) . "\n";
        $this->_debugEmail .= "Response XML:" . var_export($responseXML->saveXML(), true) . "\n\n";

        $this->_debugEmail .= "Let's process that beautiful response! \n";
        //process the response
        Mage::getModel(
            'buckaroo3extended/response_abstract',
            array(
                'response'   => $response,
                'XML'        => $responseXML,
                'debugEmail' => $this->_debugEmail,
            )
        )->processResponse();
    }
}