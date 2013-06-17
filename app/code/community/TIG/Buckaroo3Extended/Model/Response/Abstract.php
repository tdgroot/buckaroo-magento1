<?php
class TIG_Buckaroo3Extended_Model_Response_Abstract extends TIG_Buckaroo3Extended_Model_Abstract
{
    protected $_debugEmail = '';
    protected $_responseXML = '';
    protected $_response = '';

    protected $_customResponseProcessing = false;

	public function setCurrentOrder($order)
    {
    	$this->_order = $order;
    }

    public function getCurrentOrder()
    {
    	return $this->_order;
    }

    public function setDebugEmail($debugEmail)
    {
    	$this->_debugEmail = $debugEmail;
    }

    public function getDebugEmail()
    {
    	return $this->_debugEmail;
    }

    public function setResponseXML($xml)
    {
        $this->_responseXML = $xml;
    }

    public function getResponseXML()
    {
        return $this->_responseXML;
    }

    public function setResponse($response)
    {
        $this->_response = $response;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function setCustomResponseProcessing($boolean)
    {
        $this->_customResponseProcessing = (bool) $boolean;
    }

    public function getCustomResponseProcessing()
    {
        return $this->_customResponseProcessing;
    }

    public function __construct($data)
    {
        parent::__construct($data['debugEmail']);
        $this->setResponse($data['response']);
        $this->setResponseXML($data['XML']);
    }

    public function processResponse()
    {
        if ($this->_response === false) {
            $this->_debugEmail .= "An error occurred in building or sending the SOAP request.. \n";
            $this->_error();
        }

        $this->_debugEmail .= "verifiying authenticity of the response... \n";
        $verified = $this->_verifyResponse();

        if ($verified !== true) {
            $this->_debugEmail .= "The authenticity of the responw could NOT be verified. \n";
            $this->_verifyError();
        }
        $this->_debugEmail .= "Verified as authentic! \n\n";

        if (!$this->_order->getTransactionKey()
		    && is_object($this->_response)
		    && isset($this->_response->Key))
		{
			$this->_order->setTransactionKey($this->_response->Key);
			$this->_order->save();
            $this->_debugEmail .= 'Transaction key saved: ' . $this->_response->Key . "\n";
		}

		//sets the currency used by Buckaroo
		if (!$this->_order->getCurrencyCodeUsedForTransaction()
		    && is_object($this->_response)
		    && isset($this->_response->Currency))
		{
		    $this->_order->setCurrencyCodeUsedForTransaction($this->_response->Currency);
		    $this->_order->save();
		}

		if (is_object($this->_response) && isset($this->_response->RequiredAction)) {
		    $requiredAction = $this->_response->RequiredAction->Type;
		} else {
		    $requiredAction = false;
		}

        $parsedResponse = $this->_parseResponse();
        $this->_addSubCodeComment($parsedResponse);

        if (!is_null($requiredAction)
            && $requiredAction !== false
            && $requiredAction == 'Redirect')
        {
            $this->_debugEmail .= "Redirecting customer... \n";
            $this->_redirectUser();
        }

        $this->_debugEmail .= "Parsed response: " . var_export($parsedResponse, true) . "\n";

        $this->_debugEmail .= "Dispatching custom order processing event... \n";
        Mage::dispatchEvent(
        	'buckaroo3extended_response_custom_processing',
            array(
        		'model'         => $this,
                'order'         => $this->getOrder(),
                'response'      => $parsedResponse,
            )
        );

        $this->_requiredAction($parsedResponse);
    }

    protected function _requiredAction($response)
    {
        switch ($response['status']) {
            case self::BUCKAROO_SUCCESS:           $this->_success();
                                                   break;
            case self::BUCKAROO_FAILED:            $this->_failed();
                                                   break;
            case self::BUCKAROO_ERROR:             $this->_error();
                                                   break;
            case self::BUCKAROO_NEUTRAL:           $this->_neutral();
                                                   break;
            case self::BUCKAROO_PENDING_PAYMENT:   $this->_pendingPayment();
                                                   break;
            case self::BUCKAROO_INCORRECT_PAYMENT: $this->_incorrectPayment();
                                                   break;
            default:                               $this->_neutral();
        }
    }

    protected function _addSubCodeComment($parsedResponse)
    {
        if (!$parsedResponse['subCode']) {
            return $this;
        }

        $subCode = $parsedResponse['subCode'];

        $this->_order->addStatusHistoryComment(
            Mage::helper('buckaroo3extended')->__(
                'Buckaroo has sent the following response: %s',
                $subCode['message']
            )
        );

        $this->_order->save();
        return $this;
    }

    protected function _redirectUser()
    {
        $redirectUrl = $this->_response->RequiredAction->RedirectURL;

        $this->_order->addStatusHistoryComment(
            Mage::helper('buckaroo3extended')->__(
                'Customer is being redirected to Buckaroo. Url: %s',
                $redirectUrl
            )
        );
        $this->_order->save();

        $this->_debugEmail .= "Redirecting user to…" . $redirectUrl . "\n";

        $this->sendDebugEmail();

        header('Location:' . $redirectUrl);
        exit;
    }

    protected function _success()
    {
        $this->_debugEmail .= "The response indicates a successful request. \n";

        $this->_order->addStatusHistoryComment(
            Mage::helper('buckaroo3extended')->__(
                'The payment request has been successfully recieved by Buckaroo.'
            )
        );
        $this->_order->save();

        if(!$this->_order->getEmailSent())
        {
        	$this->sendNewOrderEmail();
        }

        $this->emptyCart();

		Mage::getSingleton('core/session')->addSuccess(
		    Mage::helper('buckaroo3extended')->__('Your order has been placed succesfully.')
		);

		$returnLocation = Mage::getStoreConfig('buckaroo/buckaroo3extended_advanced/success_redirect', $this->_order->getStoreId());
        $returnUrl = Mage::getUrl($returnLocation, array('_secure' => true));

        $this->_debugEmail .= 'Redirecting user to...' . $returnUrl . "\n";

        $this->sendDebugEmail();

		header('Location:' . $returnUrl);
		exit;
    }

    protected function _failed()
    {
        $this->_debugEmail .= 'The transaction was unsuccessful. \n';

        $this->_order->addStatusHistoryComment(
            Mage::helper('buckaroo3extended')->__(
                'The payment request has been denied by Buckaroo.'
            )
        );
        $this->_order->save();

        $this->restoreQuote();

        Mage::getSingleton('core/session')->addError(
            Mage::helper('buckaroo3extended')->__('Your payment was unsuccesful. Please try again or choose another payment method.')
        );

        if (Mage::getStoreConfig('buckaroo/buckaroo3extended_advanced/cancel_on_failed', $this->_order->getStoreId())) {
            $this->_order->cancel()->save();
        }

        $returnLocation = Mage::getStoreConfig('buckaroo/buckaroo3extended_advanced/failure_redirect', $this->_order->getStoreId());
        $returnUrl = Mage::getUrl($returnLocation, array('_secure' => true));

        $this->_debugEmail .= 'Redirecting user to...' . $returnUrl . "\n";

        $this->sendDebugEmail();
        header('Location:' . $returnUrl);
        exit;
    }

    protected function _error()
    {
        $this->_debugEmail .= "The transaction generated an error. \n";

        $this->_order->addStatusHistoryComment(
            Mage::helper('buckaroo3extended')->__(
                'A technical error has occurred.'
            )
        );

        Mage::getSingleton('core/session')->addError(
            Mage::helper('buckaroo3extended')->__('A technical error has occurred. Please try again. If this problem persists, please contact the shop owner.')
        );

        $this->_order->cancel()->save();
        $this->_debugEmail .= "The order has been cancelled. \n";
        $this->restoreQuote();
        $this->_debugEmail .= "The quote has been restored. \n";

        $returnLocation = Mage::getStoreConfig('buckaroo/buckaroo3extended_advanced/failure_redirect', $this->_order->getStoreId());
        $returnUrl = Mage::getUrl($returnLocation, array('_secure' => true));

        $this->_debugEmail .= 'Redirecting user to...' . $returnUrl . "\n";

        $this->sendDebugEmail();
        header('Location:' . $returnUrl);
        exit;
    }

    protected function _neutral()
    {
        $this->_debugEmail .= "The response is neutral (not successful, not unsuccessful). \n";

        $this->_order->addStatusHistoryComment(
            Mage::helper('buckaroo3extended')->__(
                'The payment request has been recieved by Buckaroo.'
            )
        );
        $this->_order->save();

		Mage::getSingleton('core/session')->addSuccess(
		    Mage::helper('buckaroo3extended')->__(
		    	'Your order has been placed succesfully. You will recieve an e-mail containing further payment instructions shortly.'
		    )
		);

        $returnLocation = Mage::getStoreConfig('buckaroo/buckaroo3extended_advanced/success_redirect', $this->_order->getStoreId());
        $returnUrl = Mage::getUrl($returnLocation, array('_secure' => true));

        $this->_debugEmail .= 'Redirecting user to...' . $returnUrl . '\n';

        $this->sendDebugEmail();
		header('Location:' . $returnUrl);
		exit;
    }

    protected function _pendingPayment()
    {
        $this->_success();
    }

    protected function _incorrectPayment()
    {
        $this->_error();
    }

    protected function _verifyError()
    {
        $this->_debugEmail .= "The transaction's authenticity was not verified. \n";
        Mage::getSingleton('core/session')->addNotice(
            Mage::helper('buckaroo3extended')->__('We are currently unable to retrieve the status of your transaction. If you do not recieve an e-mail regarding your order within 30 minutes, please contact the shop owner.')
        );

        $returnLocation = Mage::getStoreConfig('buckaroo/buckaroo3extended_advanced/failure_redirect', $this->_order->getStoreId());
        $returnUrl = Mage::getUrl($returnLocation, array('_secure' => true));

        $this->_debugEmail .= 'Redirecting user to...' . $returnUrl . "\n";

        $this->sendDebugEmail();
        header('Location:' . $returnUrl);
        exit;
    }

    protected function _verifyResponse()
    {
        $verified = false;

        $verifiedSignature = $this->_verifySignature();
        $verifiedDigest = $this->_verifyDigest();

        if ($verifiedSignature === true && $verifiedDigest === true) {
            $verified =  true;
        }

        return $verified;
    }

    protected function _verifySignature()
    {
        $verified = false;

        //save response XML to string
        $responseDomDoc = $this->_responseXML;
        $responseString = $responseDomDoc->saveXML();

        //retrieve the signature value
        $sigatureRegex = "#<SignatureValue>(.*)</SignatureValue>#ims";
        $signatureArray = array();
        preg_match_all($sigatureRegex, $responseString, $signatureArray);

        //decode the signature
        $signature = $signatureArray[1][0];
        $sigDecoded = base64_decode($signature);

        $xPath = new DOMXPath($responseDomDoc);

    	//register namespaces to use in xpath query's
    	$xPath->registerNamespace('wsse','http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');
    	$xPath->registerNamespace('sig','http://www.w3.org/2000/09/xmldsig#');
    	$xPath->registerNamespace('soap','http://schemas.xmlsoap.org/soap/envelope/');

        //Get the SignedInfo nodeset
    	$SignedInfoQuery = '//wsse:Security/sig:Signature/sig:SignedInfo';
    	$SignedInfoQueryNodeSet = $xPath->query($SignedInfoQuery);
    	$SignedInfoNodeSet = $SignedInfoQueryNodeSet->item(0);

    	//Canonicalize nodeset
    	$signedInfo = $SignedInfoNodeSet->C14N(true, false);

    	//get the public key
		$pubKey = openssl_get_publickey(openssl_x509_read(file_get_contents(CERTIFICATE_DIR . DS .'Checkout.pem')));

		//verify the signature
    	$sigVerify = openssl_verify($signedInfo, $sigDecoded, $pubKey);

    	if ($sigVerify === 1) {
    	    $verified = true;
    	}

    	return $verified;
    }

    protected function _verifyDigest()
    {
        $verified = false;

        //save response XML to string
        $responseDomDoc = $this->_responseXML;
        $responseString = $responseDomDoc->saveXML();

        //retrieve the signature value
        $digestRegex = "#<DigestValue>(.*?)</DigestValue>#ims";
        $digestArray = array();
        preg_match_all($digestRegex, $responseString, $digestArray);

        $digestValues = array();
        foreach($digestArray[1] as $digest) {
            $digestValues[] = $digest;
        }

        $xPath = new DOMXPath($responseDomDoc);

    	//register namespaces to use in xpath query's
    	$xPath->registerNamespace('wsse','http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');
    	$xPath->registerNamespace('sig','http://www.w3.org/2000/09/xmldsig#');
    	$xPath->registerNamespace('soap','http://schemas.xmlsoap.org/soap/envelope/');

    	$controlHashReference = $xPath->query('//*[@Id="_control"]')->item(0);
    	$controlHashCanonical = $controlHashReference->C14N(true, false);
    	$controlHash = base64_encode(pack('H*',sha1($controlHashCanonical)));

    	$bodyHashReference = $xPath->query('//*[@Id="_body"]')->item(0);
    	$bodyHashCanonical = $bodyHashReference->C14N(true, false);
    	$bodyHash = base64_encode(pack('H*',sha1($bodyHashCanonical)));

    	if (in_array($controlHash, $digestValues) === true && in_array($bodyHash, $digestValues) === true) {
    	    $verified = true;
    	}

    	return $verified;
    }

    public function sendNewOrderEmail()
    {
        $currentStore = Mage::app()->getStore()->getId();
        $orderStore = $this->_order->getStoreId();

        Mage::app()->setCurrentStore($orderStore);

        $this->_order->sendNewOrderEmail();

        Mage::app()->setCurrentStore($currentStore);

        return $this;
    }
}