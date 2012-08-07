<?php

final class TIG_Buckaroo3Extended_Model_Soap extends TIG_Buckaroo3Extended_Model_Abstract
{
    private $_vars;
    private $_method;
    
    protected $_debugEmail;
    
    public function setVars($vars = array())
    {
        $this->_vars = $vars;
    }
    
    public function getVars()
    {
        return $this->_vars;
    }
    
    public function __construct($data = array())
    {
        $this->setVars($data['vars']);
        $this->setMethod($data['method']);
    }
    
    public function getMethod()
    {
        return $this->_method;
    }
    
    public function setMethod($method = '')
    {
        $this->_method = $method;
    }
    
    public function transactionRequest()
    {
        $wsdl_url = 'https://checkout.buckaroo.nl/soap/soap.svc?wsdl';
        
        try
        {
        	$client = new SoapClientWSSEC(
                $wsdl_url,
                array(
                	'trace' => 1,
                    'cache_wsdl' => WSDL_CACHE_DISK,
            ));
        }
        catch (SoapFault $e)
        {
            try {
                ini_set('soap.wsdl_cache_ttl', 1);
                $client = new SoapClientWSSEC(
                    $wsdl_url,
                    array(
                    	'trace' => 1,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                ));
            } catch (SoapFault $e) {
                var_dump($e->getMessage());exit;
            	return $this->_error($client);
            }
        }
        
        $client->thumbprint = $this->_vars['thumbprint'];
        
        $TransactionRequest = new Body();
        $TransactionRequest->Currency = $this->_vars['currency'];
        $TransactionRequest->AmountDebit = round($this->_vars['amountDebit'], 2);
        $TransactionRequest->AmountCredit = round($this->_vars['amountCredit'], 2);
        $TransactionRequest->Invoice = $this->_vars['orderId'];
        $TransactionRequest->Order = $this->_vars['orderId'];
        $TransactionRequest->Description = $this->_vars['description'];
        $TransactionRequest->ReturnURL = $this->_vars['returnUrl'];
        $TransactionRequest->StartRecurrent = FALSE;
        $TransactionRequest->OriginalTransactionKey = $this->_vars['OriginalTransactionKey'];
        
        $TransactionRequest->Services = new Services();
        
        $this->_addServices($TransactionRequest);
        
        $TransactionRequest->ClientIP = new IPAddress();
        $TransactionRequest->ClientIP->Type = 'IPv4';
        $TransactionRequest->ClientIP->_ = $_SERVER['REMOTE_ADDR'];
        
        foreach ($TransactionRequest->Services->Service as $key => $service) {
            $this->_addCustomFields($TransactionRequest, $key, $service->Name);
        }
        
        $Header = new Header();
        $Header->MessageControlBlock = new MessageControlBlock();
        $Header->MessageControlBlock->Id = '_control';
        $Header->MessageControlBlock->WebsiteKey = $this->_vars['merchantKey']; 
        $Header->MessageControlBlock->Culture = $this->_vars['locale'];
        $Header->MessageControlBlock->TimeStamp = time();
        $Header->MessageControlBlock->Channel = 'Web';
        $Header->Security = new SecurityType();
        $Header->Security->Signature = new SignatureType();
        
        $Header->Security->Signature->SignedInfo = new SignedInfoType();
        $Header->Security->Signature->SignedInfo->CanonicalizationMethod = new CanonicalizationMethodType();
        $Header->Security->Signature->SignedInfo->CanonicalizationMethod->Algorithm = 'http://www.w3.org/2001/10/xml-exc-c14n#';
        $Header->Security->Signature->SignedInfo->SignatureMethod = new SignatureMethodType();
        $Header->Security->Signature->SignedInfo->SignatureMethod->Algorithm = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
        
        $Reference = new ReferenceType();
        $Reference->URI = '#_body';
        $Transform = new TransformType();
        $Transform->Algorithm = 'http://www.w3.org/2001/10/xml-exc-c14n#'; 
        $Reference->Transforms=array($Transform);
        
        $Reference->DigestMethod = new DigestMethodType();
        $Reference->DigestMethod->Algorithm = 'http://www.w3.org/2000/09/xmldsig#sha1'; 
        $Reference->DigestValue = '';
        
        $Transform2 = new TransformType();
        $Transform2->Algorithm = 'http://www.w3.org/2001/10/xml-exc-c14n#'; 
        $ReferenceControl = new ReferenceType();
        $ReferenceControl->URI = '#_control';
        $ReferenceControl->DigestMethod = new DigestMethodType();
        $ReferenceControl->DigestMethod->Algorithm = 'http://www.w3.org/2000/09/xmldsig#sha1'; 
        $ReferenceControl->DigestValue = '';
        $ReferenceControl->Transforms=array($Transform2);
        
        $Header->Security->Signature->SignedInfo->Reference = array($Reference,$ReferenceControl);
        $Header->Security->Signature->SignatureValue = ''; 
        
        $soapHeaders = array();
        $soapHeaders[] = new SOAPHeader('https://checkout.buckaroo.nl/PaymentEngine/', 'MessageControlBlock', $Header->MessageControlBlock); 
        $soapHeaders[] = new SOAPHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'Security', $Header->Security); 
        $client->__setSoapHeaders($soapHeaders);
        
        //if the module is set to testmode, use the test gateway. Otherwise, use the default gateway
        if (Mage::getStoreConfig('buckaroo/buckaroo3extended/mode', Mage::app()->getStore()->getStoreId())
            || Mage::getStoreConfig('buckaroo/buckaroo3extended_' . $this->_method . '/mode', Mage::app()->getStore()->getStoreId())
        ) {
            $location = 'https://testcheckout.buckaroo.nl/soap/';
        } else {
            $location = 'https://checkout.buckaroo.nl/soap/';
        }
        
        $client->__SetLocation($location);
        
        try
        {
        	$response = $client->TransactionRequest($TransactionRequest);
        }
        catch ( SoapFault $e )
        {
            $this->logException($e->getMessage());
        	return $this->_error($client);
        }
        
        if (is_null($response)) {
            $response = false;
        }
        
        $responseXML = $client->__getLastResponse();
        $requestXML = $client->__getLastRequest();
        
        $responseDomDOC = new DOMDocument();
        $responseDomDOC->loadXML($responseXML);
		$responseDomDOC->preserveWhiteSpace = FALSE;
		$responseDomDOC->formatOutput = TRUE;
		
		$requestDomDOC = new DOMDocument();
        $requestDomDOC->loadXML($requestXML);
		$requestDomDOC->preserveWhiteSpace = FALSE;
		$requestDomDOC->formatOutput = TRUE;
        
        return array($response, $responseDomDOC, $requestDomDOC);
    }
    
    protected function _error($client = false)
    {
        $response = false;
        
        if ($client) {
            $responseXML = $client->__getLastResponse();
            $requestXML = $client->__getLastRequest();
        
            $responseDomDOC = new DOMDocument();
            $responseDomDOC->loadXML($responseXML);
    		$responseDomDOC->preserveWhiteSpace = FALSE;
    		$responseDomDOC->formatOutput = TRUE;
    		
    		$requestDomDOC = new DOMDocument();
            $requestDomDOC->loadXML($requestXML);
    		$requestDomDOC->preserveWhiteSpace = FALSE;
    		$requestDomDOC->formatOutput = TRUE;
        }
        
        return array($response, $responseDomDOC, $requestDomDOC);
    }
    
    protected function _addServices(&$TransactionRequest)
    {
        $services = array();
        foreach($this->_vars['services'] as $fieldName => $value) {
            $service          = new Service();
            $service->Name    = $fieldName;
            $service->Action  = $value['action'];
            $service->Version = $value['version'];
            
            $services[] = $service;
        }
        
        $TransactionRequest->Services->Service = $services;
    }
    
    protected function _addCustomFields(&$TransactionRequest, $key, $name) 
    {
        if (
            !isset($this->_vars['customVars']) 
            || !isset($this->_vars['customVars'][$name])
            || empty($this->_vars['customVars'][$name])
        ) {
            unset($TransactionRequest->Services->Service->RequestParameter);
            return;
        }
        
        $requestParameters = array();
        foreach($this->_vars['customVars'][$name] as $fieldName => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }
            
            $requestParameter = new RequestParameter();
            $requestParameter->Name = $fieldName;
            $requestParameter->_ = $value;
            
            $requestParameters[] = $requestParameter;
        }
        
        if (empty($requestParameters)) {
            unset($TransactionRequest->Services->Service->RequestParameter);
            return;
        } else {
            $TransactionRequest->Services->Service[$key]->RequestParameter = $requestParameters;
        }
    }
}


class SoapClientWSSEC extends SoapClient
{
	/**
	 * Contains the request XML
	 * @var DOMDocument
	 */
	private $document;
	
	/**
	 * Path to the privateKey file
	 * @var string 
	 */
	public $privateKey = '';
	
	/**
	 * Password for the privatekey
	 * @var string 
	 */
	public $privateKeyPassword = '';
	
	/**
	 * Thumbprint from Payment Plaza
	 * @var type 
	 */	
	public $thumbprint = '';
	
    public function __doRequest ($request , $location , $action , $version , $one_way = 0 )
	{
		// Add code to inspect/dissect/debug/adjust the XML given in $request here
		$domDOC = new DOMDocument();
		$domDOC->preserveWhiteSpace = FALSE;
		$domDOC->formatOutput = TRUE;
		$domDOC->loadXML($request);	
		
		//Sign the document					
		$domDOC = $this->SignDomDocument($domDOC);
		
		// Uncomment the following line, if you actually want to do the request
		return parent::__doRequest($domDOC->saveXML($domDOC->documentElement), $location, $action, $version, $one_way);
	}
	
	//Get nodeset based on xpath and ID
	private function getReference($ID, $xPath)
	{	
		$query = '//*[@Id="'.$ID.'"]';
		$nodeset = $xPath->query($query);
		return $nodeset->item(0);
	}

	//Canonicalize nodeset
	private function getCanonical($Object)
	{
		return $Object->C14N(true, false);
	}

	//Calculate digest value (sha1 hash)
	private function calculateDigestValue($input)
	{
		return base64_encode(pack('H*',sha1($input)));
	}
	
	private function signDomDocument($domDocument)
	{
	    //create xPath
    	$xPath = new DOMXPath($domDocument);
    		
    	//register namespaces to use in xpath query's
    	$xPath->registerNamespace('wsse','http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');
    	$xPath->registerNamespace('sig','http://www.w3.org/2000/09/xmldsig#');
    	$xPath->registerNamespace('soap','http://schemas.xmlsoap.org/soap/envelope/');
    	
    	//Set id on soap body to easily extract the body later.
    	$bodyNodeList = $xPath->query('/soap:Envelope/soap:Body');
    	$bodyNode = $bodyNodeList->item(0);
    	$bodyNode->setAttribute('Id','_body');
    	
    	//Get the digest values
    	$controlHash = $this->CalculateDigestValue($this->GetCanonical($this->GetReference('_control', $xPath)));
    	$bodyHash = $this->CalculateDigestValue($this->GetCanonical($this->GetReference('_body', $xPath)));
    	
    	//Set the digest value for the control reference
    	$Control = '#_control';
    	$controlHashQuery = $query = '//*[@URI="'.$Control.'"]/sig:DigestValue';
    	$controlHashQueryNodeset = $xPath->query($controlHashQuery);
    	$controlHashNode = $controlHashQueryNodeset->item(0);
    	$controlHashNode->nodeValue = $controlHash;
    	
    	//Set the digest value for the body reference
    	$Body = '#_body';
    	$bodyHashQuery = $query = '//*[@URI="'.$Body.'"]/sig:DigestValue';
    	$bodyHashQueryNodeset = $xPath->query($bodyHashQuery);
    	$bodyHashNode = $bodyHashQueryNodeset->item(0);
    	$bodyHashNode->nodeValue = $bodyHash;
    	
    	//Get the SignedInfo nodeset
    	$SignedInfoQuery = '//wsse:Security/sig:Signature/sig:SignedInfo';
    	$SignedInfoQueryNodeSet = $xPath->query($SignedInfoQuery);
    	$SignedInfoNodeSet = $SignedInfoQueryNodeSet->item(0);
    		
    	//Canonicalize nodeset
    	$signedINFO = $this->GetCanonical($SignedInfoNodeSet);
    	
    	//Get privatekey certificate
    	$fp = fopen(CERTIFICATE_DIR . '/BuckarooPrivateKey.pem', "r");
    	$priv_key = fread($fp, 8192);
    	if ($priv_key === false) {
    	    echo 'cant read';exit;
    	}
    	fclose($fp);
    	
    	$pkeyid = openssl_get_privatekey($priv_key, '');	
	    if ($pkeyid === false) {
    	    echo 'no pkeyid';exit;
    	}
    	
    	//Sign signedinfo with privatekey
    	$signature2 = null;
    	$signatureCreate = openssl_sign($signedINFO, $signature2, $pkeyid);
    	
    	//Add signature value to xml document
    	$sigValQuery = '//wsse:Security/sig:Signature/sig:SignatureValue';
    	$sigValQueryNodeset = $xPath->query($sigValQuery);
    	$sigValNodeSet = $sigValQueryNodeset->item(0);	
    	$sigValNodeSet->nodeValue = base64_encode($signature2);
    	
    	//Get signature node
    	$sigQuery = '//wsse:Security/sig:Signature';
    	$sigQueryNodeset = $xPath->query($sigQuery);
    	$sigNodeSet = $sigQueryNodeset->item(0);	
    	
    	//Create keyinfo element and Add public key to KeyIdentifier element
    	$KeyTypeNode = $domDocument->createElementNS("http://www.w3.org/2000/09/xmldsig#","KeyInfo");	
    	$SecurityTokenReference = $domDocument->createElementNS('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd','SecurityTokenReference');
    	$KeyIdentifier = $domDocument->createElement("KeyIdentifier");
    	$KeyIdentifier->nodeValue = $this->thumbprint;
    	$KeyIdentifier->setAttribute('ValueType','http://docs.oasis-open.org/wss/oasis-wss-soap-message-security-1.1#ThumbPrintSHA1');
    	$SecurityTokenReference->appendChild($KeyIdentifier);	
    	$KeyTypeNode->appendChild($SecurityTokenReference);		
    	$sigNodeSet->appendChild($KeyTypeNode);		
    	
    	return $domDocument;
	}
}

class Header
{
	public $MessageControlBlock;
}

class SecurityType
{
	public $Signature;
}

class SignatureType
{
	public $SignedInfo;
	public $SignatureValue;
	public $KeyInfo;
}

class SignedInfoType
{
	public $CanonicalizationMethod;
	public $SignatureMethod;
	public $Reference;
}

class ReferenceType
{
	public $Transforms;
	public $DigestMethod;
	public $DigestValue;
	public $URI;
	public $Id;
}


class TransformType
{
	public $Algorithm;
}

class DigestMethodType
{
	public $Algorithm;	
}


class SignatureMethodType
{
	public $Algorithm;
}

class CanonicalizationMethodType
{
	public $Algorithm;
}

class MessageControlBlock
{
	public $Id;
	public $WebsiteKey;
	public $Culture;
	public $TimeStamp;
	public $Channel;
}

class Body
{
	public $Currency;
	public $AmountDebit;
 	public $AmountCredit;
 	public $Invoice;
 	public $Order;
 	public $Description;
 	public $ClientIP;
 	public $ReturnURL;
 	public $ReturnURLCancel;
 	public $ReturnURLError;
 	public $ReturnURLReject;
 	public $OriginalTransactionKey;
 	public $StartRecurrent;
 	public $Services;
 	//public $CustomParameters;
 	//public $AdditionalParameters;
}

class Services
{
 	public $Global;
 	public $Service;
}

class Service
{
	public $RequestParameter;
	public $Name;
	public $Action;
	public $Version;
}

class RequestParameter
{
 	public $_;
 	public $Name;
 	public $Group;
}

class IPAddress
{
 	public $_;
 	public $Type;
}
