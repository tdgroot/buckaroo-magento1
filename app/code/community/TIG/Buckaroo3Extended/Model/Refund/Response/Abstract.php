<?php
class TIG_Buckaroo3Extended_Model_Refund_Response_Abstract extends TIG_Buckaroo3Extended_Model_Response_Abstract
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
    
    public function __construct($data)
    {
        $this->setOrder($data['order']);
        $this->setPayment($data['payment']);
        parent::__construct($data);
    }
    
    public function processResponse()
    {
        if ($this->_response === false) {
            $this->_debugEmail .= "The transaction generated an error! :'( \n";
            $this->_error();
        }

        $this->_debugEmail .= "verifiying authenticity of the response... \n";
        $verified = $this->_verifyResponse();

        if ($verified !== true) {
            $this->_debugEmail .= "I don't know where you got that message, but it sure wasn't authentic! \n";
            $this->_verifyError();
        }
        $this->_debugEmail .= "Verified as authentic! \n\n";

        //sets the transaction key if its defined ($trx)
		//will retrieve it from the response array, if response actually is an array
		$additionalInformation = $this->_payment->getAdditionalInformation();
		if (!$additionalInformation['buckaroo_transaction_key'] || true) {
			$this->_payment->setAdditionalInformation('buckaroo_last_transaction_key', $this->_response->Key)->save();
            $this->_debugEmail .= 'Transaction key saved: ' . $this->_response->Key . "\n";
		}

        $parsedResponse = $this->_parseResponse();
        $this->_debugEmail .= "Remember that big XMl message from a couple of lines up? This is what that whole thing comes down to: " . var_export($parsedResponse, true) . "\n";

        $this->_debugEmail .= "Dispatching custom order rpocessing event... \n";
        
        Mage::dispatchEvent(
        	'buckaroo3extended_refund_response_custom_processing',
            array(
        		'model' => $this,
                'order'         => $this->getOrder(),
                'response'      => $parsedResponse,
            )
        );

        $this->_requiredAction($parsedResponse);
        return $this;
    }
    
    
    protected function _success()
    {
        $this->_debugEmail .= 'The refund request has been accepted \n';
        
	    $this->_payment->setAdditionalInformation('buckaroo_refund_state', 'success')->save();
	    $this->_payment->setAdditionalInformation('buckaroo_transaction_key', $this->_response->Key)->save();
        
        $this->sendDebugEmail();
	    
        return $this;
    }

    protected function _failed()
    {
        $this->_debugEmail .= 'Oh no! A failed response :( \n';
        
	    $this->_payment->setAdditionalInformation('buckaroo_refund_state', 'failed')->save();
	    
        Mage::throwException(Mage::helper('buckaroo3extended')->__($this->_response->Status->Code->_));
        
        $this->sendDebugEmail();
    }

    protected function _error()
    {
        $this->_debugEmail .= 'Oh no! A failed response :( \n';
        
	    $this->_payment->setAdditionalInformation('buckaroo_refund_state', 'error')->save();
	    
        Mage::throwException(Mage::helper('buckaroo3extended')->__($this->_response->Status->Code->_));
        
        $this->sendDebugEmail();
    }

    protected function _neutral()
    {
        $this->_failed();
    }
    
    protected function _verifyError()
    {
        $this->_failed();
    }

    protected function _pendingPayment()
    {
        $this->_debugEmail .= 'This refund request has been put on hold. \n';
        
	    $this->_payment->setAdditionalInformation('buckaroo_refund_state', 'failed')->save();
	    
        Mage::throwException(Mage::helper('buckaroo3extended')->__("This refund request has been put on hold by Buckaroo. You can find out details regarding the action and complete the refund in Buckaroo Payment Plaza."));
        
        $this->sendDebugEmail();
    }
}