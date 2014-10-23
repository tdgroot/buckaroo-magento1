<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Afterpay_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract
{
    protected $_code = 'buckaroo3extended_afterpay';
    protected $_method = false;

    protected function _construct()
    {
        $this->_method = Mage::getStoreConfig('buckaroo/buckaroo3extended_afterpay/paymethod', Mage::app()->getStore()->getStoreId());
    }

    public function buckaroo3extended_request_addservices(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request = $observer->getRequest();

        $vars = $request->getVars();

        if($this->_method == false){
            $this->_method = Mage::getStoreConfig('buckaroo/buckaroo3extended_afterpay/paymethod', Mage::app()->getStore()->getStoreId());
        }

        $array = array(
            $this->_method => array(
                'action'   => 'Pay',
                'version'  => '1',
            ),
        );

        if (array_key_exists('services', $vars) && is_array($vars['services'])) {
            $vars['services'] = array_merge($vars['services'], $array);
        } else {
            $vars['services'] = $array;
        }

        $request->setVars($vars);

        return $this;
    }

    public function buckaroo3extended_request_addcustomvars(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request            = $observer->getRequest();
        $this->_billingInfo = $request->getBillingInfo();
        $this->_order       = $request->getOrder();

        $vars = $request->getVars();

        if (Mage::getStoreConfig('buckaroo/buckaroo3extended_' . $this->_method . '/use_creditmanagement', Mage::app()->getStore()->getStoreId())) {
            $this->_addCustomerVariables($vars);
            $this->_addCreditManagement($vars);
            $this->_addAdditionalCreditManagementVariables($vars);
        }

        $this->_addAfterpayVariables($vars, $this->_method);

        //echo '<pre>';
        //var_dump($vars);die;
        $request->setVars($vars);

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function buckaroo3extended_request_setmethod(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request = $observer->getRequest();

        $codeBits = explode('_', $this->_code);
        $code = end($codeBits);
        $request->setMethod($code);

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function buckaroo3extended_response_custom_processing(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $responseModel = $observer->getModel();
        $response = $observer->getResponse();

        $pushModel = Mage::getModel(
        	'buckaroo3extended/response_push',
            array(
    	        'order'      => $observer->getOrder(),
    	        'postArray'  => array('brq_statuscode' => $response['code']),
    	        'debugEmail' => $responseModel->getDebugEmail(),
    	        'method'     => $this->_method,
            )
        );

        $newStates = $pushModel->getNewStates($response['status']);

        switch ($response['status'])
		{
		    case self::BUCKAROO_ERROR:
			case self::BUCKAROO_FAILED:
                                                   if(!empty($response['subCode'])){
                                                       $response['message'] = $response['message'].' - ' . $response['subCode']['code'] .' : '.  $response['subCode']['message'];
                                                   }
                                                   $updatedFailed = $pushModel->processFailed($newStates, $response['message']);
									               break;
			case self::BUCKAROO_SUCCESS:	       $updatedSuccess = $pushModel->processSuccess($newStates, $response['message']);
											       break;
			case self::BUCKAROO_NEUTRAL:           $responseModel->_addNote($response['message']);
			                                       break;
			case self::BUCKAROO_PENDING_PAYMENT:   $updatedPendingPayment = $responseModel->processPendingPayment($newStates, $response['message']);
			                                       break;
			case self::BUCKAROO_INCORRECT_PAYMENT: $updatedIncorrectPayment = $pushModel->processIncorrectPayment($newStates);
			                                       break;
		}

        $responseModel->setCustomResponseProcessing(true);
    }

    /**
     * Adds variables required for the SOAP XML for paymentguarantee to the variable array
     * Will merge with old array if it exists
     *
     * @param array $vars
     */
    protected function _addAfterpayVariables(&$vars)
    {
        $session            = Mage::getSingleton('checkout/session');
        $additionalFields   = $session->getData('additionalFields');

        $requestArray       = array();

        //add billing address
        $billingAddress     = $this->_order->getBillingAddress();
        $streetFull         = $this->_processAddress($billingAddress->getStreetFull());
        $billingPhonenumber = $this->_processPhoneNumber($billingAddress->getTelephone());

        $billingInfo = array(
            'BillingTitle'             => $billingAddress->getFirstname(),
            'BillingGender'            => $additionalFields['BPE_Customergender'],
            'BillingInitials'          => strtoupper(substr($billingAddress->getFirstname(),0,1)),
            'BillingLastName'          => $billingAddress->getLastname(),
            'BillingBirthDate'         => $additionalFields['BPE_customerbirthdate'],
            'BillingStreet'            => $streetFull['street'],
            'BillingHouseNumber'       => $streetFull['house_number'],
            'BillingHouseNumberSuffix' => $streetFull['number_addition'],
            'BillingPostalCode'        => $billingAddress->getPostcode(),
            'BillingCity'              => $billingAddress->getCity(),
            'BillingRegion'            => $billingAddress->getRegion(),
            'BillingCountryCode'       => $billingAddress->getCountryCode(),
            'BillingEmail'             => $billingAddress->getEmail(),
            'BillingPhoneNumber'       => $billingPhonenumber['clean'],
            'BillingLanguage'          => $billingAddress->getCountryCode(),
        );

        $requestArray = array_merge($requestArray,$billingInfo);

        //add shipping address (only when different from billing address)
        if($this->isShippingDifferent()){
            $shippingAddress     = $this->_order->getShippingAddress();
            $streetFull          = $this->_processAddress($shippingAddress->getStreetFull());
            $shippingPhonenumber = $this->_processPhoneNumber($shippingAddress->getTelephone());

            $shippingInfo = array(
                'AddressesDiffer'           => 'true',
                'ShippingTitle'             => $shippingAddress->getFirstname(),
                'ShippingGender'            => $additionalFields['BPE_Customergender'],
                'ShippingInitials'          => strtoupper(substr($shippingAddress->getFirstname(),0,1)),
                'ShippingLastName'          => $shippingAddress->getLastname(),
                'ShippingBirthDate'         => $additionalFields['BPE_customerbirthdate'],
                'ShippingStreet'            => $streetFull['street'],
                'ShippingHouseNumber'       => $streetFull['house_number'],
                'ShippingHouseNumberSuffix' => $streetFull['number_addition'],
                'ShippingPostalCode'        => $shippingAddress->getPostcode(),
                'ShippingCity'              => $shippingAddress->getCity(),
                'ShippingRegion'            => $shippingAddress->getRegion(),
                'ShippingCountryCode'       => $shippingAddress->getCountryCode(),
                'ShippingEmail'             => $shippingAddress->getEmail(),
                'ShippingPhoneNumber'       => $shippingPhonenumber['clean'],
                'ShippingLanguage'          => $shippingAddress->getCountryCode(),
            );
            $requestArray = array_merge($requestArray,$shippingInfo);
        }

        //customer info
        $customerInfo = array(
            'CustomerAccountNumber' => $additionalFields['BPE_AccountNumber'],
            'CustomerIPAddress'     => Mage::helper('core/http')->getRemoteAddr(),
        );

        $requestArray = array_merge($requestArray,$customerInfo);
        //is B2B
        $b2bInfo = array();
        if($additionalFields['BPE_B2B'] == 2){
            $b2bInfo = array(
                'B2B'                    => 'true',
                'CompanyCOCRegistration' => $additionalFields['BPE_CompanyCOCRegistration'],
                'CompanyName'            => $additionalFields['BPE_CompanyName'],
                'CostCentre'             => $additionalFields['BPE_CostCentre'],
                'VatNumber'              => $additionalFields['BPE_VatNumber'],
            );
            $requestArray = array_merge($requestArray,$b2bInfo);
        }
        //add all products max 10
        $products = $this->_order->getAllItems();
        $max      = 9;
        $i        = 1;

        $group = array();

        foreach($products as $item){
            /** @var $item Mage_Sales_Model_Order_Item */

            if (empty($item) || $item->hasParentItemId()) {
                continue;
            }

            // Changed calculation from unitPrice to orderLinePrice due to impossible to recalculate unitprice,
            // because of differences in outcome between TAX settings: Unit, OrderLine and Total.
            // Quantity will always be 1 and quantity ordered will be in the article description.
            $productPrice = ($item->getBasePrice() * $item->getQtyOrdered()) + $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount();
            $productPrice = round($productPrice,2);


            $article['ArticleDescription']['value'] = (int) $item->getQtyOrdered() . 'x ' . $item->getName();
            $article['ArticleId']['value']          = $item->getId();
            $article['ArticleQuantity']['value']    = 1;
            $article['ArticleUnitPrice']['value']   = $productPrice;
            $article['ArticleVatcategory']['value'] = $this->_getTaxCategory($item->getTaxClassId());

            $group[$i] = $article;


            if($i <= $max){
                $i++;
                continue;
            }
            break;
        }

        end($group);// move the internal pointer to the end of the array
        $key             = (int)key($group);
        $shippingGroupId = $key+1;
        $group[$shippingGroupId] = $this->_getShippingLine();

        $requestArray = array_merge($requestArray, array('Articles' => $group));

        if (array_key_exists('customVars', $vars) && is_array($vars['customVars'][$this->_method])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $requestArray);
        } else {
            $vars['customVars'][$this->_method] = $requestArray;
        }
    }

    protected function _getShippingLine()
    {
        $shipping  = (float) $this->_order->getBaseShippingAmount();
        $article['ArticleDescription']['value'] = 'Verzendkosten';
        $article['ArticleId']['value']          = 1;
        $article['ArticleQuantity']['value']    = 1;
        $article['ArticleUnitPrice']['value']   = round($shipping + $this->_order->getBaseShippingTaxAmount(), 0);
        $article['ArticleVatcategory']['value'] = $this->_getTaxCategory(Mage::getStoreConfig('tax/classes/shipping_tax_class', Mage::app()->getStore()->getId()));
        return $article;
    }

    protected function _getTaxCategory($taxClassId)
    {
        if (!$taxClassId) {
            return 4;
        }

        $highTaxClasses = explode(',', Mage::getStoreConfig('buckaroo/buckaroo3extended_' . $this->_method . '/high', Mage::app()->getStore()->getStoreId()));
        $lowTaxClasses  = explode(',', Mage::getStoreConfig('buckaroo/buckaroo3extended_' . $this->_method . '/low', Mage::app()->getStore()->getStoreId()));
        $zeroTaxClasses = explode(',', Mage::getStoreConfig('buckaroo/buckaroo3extended_' . $this->_method . '/zero', Mage::app()->getStore()->getStoreId()));
        $noTaxClasses   = explode(',', Mage::getStoreConfig('buckaroo/buckaroo3extended_' . $this->_method . '/no', Mage::app()->getStore()->getStoreId()));

        if (in_array($taxClassId, $highTaxClasses)) {
            return 1;
        } elseif (in_array($taxClassId, $lowTaxClasses)) {
            return 2;
        } elseif (in_array($taxClassId, $zeroTaxClasses)) {
            return 3;
        } elseif (in_array($taxClassId, $noTaxClasses)) {
            return 4;
        } else {
            Mage::throwException($this->_helper->__('Did not recognize tax class for class ID: ') . $taxClassId);
        }
    }

    protected function _processAddress($fullStreet)
    {
        //get address from billingInfo
        $address = $fullStreet;

        $ret = array();
        $ret['house_number'] = '';
        $ret['number_addition'] = '';
        if (preg_match('#^(.*?)([0-9]+)(.*)#s', $address, $matches)) {
            if ('' == $matches[1]) {
                // Number at beginning
                $ret['house_number'] = trim($matches[2]);
                $ret['street']		 = trim($matches[3]);
            } else {
                // Number at end
                $ret['street']			= trim($matches[1]);
                $ret['house_number']    = trim($matches[2]);
                $ret['number_addition'] = trim($matches[3]);
            }
        } else {
            // No number
            $ret['street'] = $address;
        }

        return $ret;
    }

    /**
     * @param $telephoneNumber
     * @return array
     */
    protected function _processPhoneNumber($telephoneNumber)
    {
        $number = $telephoneNumber;

        //the final output must like this: 0031123456789 for mobile: 0031612345678
        //so 13 characters max else number is not valid
        //but for some error correction we try to find if there is some faulty notation

        $return = array("orginal" => $number, "clean" => false, "mobile" => false, "valid" => false);
        //first strip out the non-numeric characters:
        $match = preg_replace('/[^0-9]/Uis', '', $number);
        if ($match) {
            $number = $match;
        }

        if (strlen((string)$number) == 13) {
            //if the length equal to 13 is, then we can check if its a mobile number or normal number
            $return['mobile'] = $this->_isMobileNumber($number);
            //now we can almost say that the number is valid
            $return['valid'] = true;
            $return['clean'] = $number;
        } elseif (strlen((string) $number) > 13) {
            //if the number is bigger then 13, it means that there are probably a zero to much
            $return['mobile'] = $this->_isMobileNumber($number);
            $return['clean'] = $this->_isValidNotation($number);
            if(strlen((string)$return['clean']) == 13) {
                $return['valid'] = true;
            }

        } elseif (strlen((string)$number) == 12 or strlen((string)$number) == 11) {
            //if the number is equal to 11 or 12, it means that they used a + in their number instead of 00
            $return['mobile'] = $this->_isMobileNumber($number);
            $return['clean'] = $this->_isValidNotation($number);
            if(strlen((string)$return['clean']) == 13) {
                $return['valid'] = true;
            }

        } elseif (strlen((string)$number) == 10) {
            //this means that the user has no trailing "0031" and therfore only
            $return['mobile'] = $this->_isMobileNumber($number);
            $return['clean'] = '0031'.substr($number,1);
            if (strlen((string) $return['clean']) == 13) {
                $return['valid'] = true;
            }
        } else {
            //if the length equal to 13 is, then we can check if its a mobile number or normal number
            $return['mobile'] = $this->_isMobileNumber($number);
            //now we can almost say that the number is valid
            $return['valid'] = true;
            $return['clean'] = $number;
        }

        return $return;
    }

    /**
     * Checks if shipping-address is different from billing-address
     *
     * @return bool
     */
    protected function isShippingDifferent()
    {
        return $this->_order->getShippingAddress()->getSameAsBilling();
    }
}