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



        $array = array(
            $serviceCode     => array(
                'action'    => 'Pay',
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

        $this->_addCustomerVariables($vars, $this->_method);
        $this->_addCreditManagement($vars, $this->_method);
        $this->_addAfterpayVariables($vars, $this->_method);


        


        $request->setVars($vars);

        return $this;
    }

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
     * Custom push processing for Paymentguarantee. Because paymentguarantee orders should have been invoiced as
     * soon as Buckaroo said that the guarantor had approved the transaction only a note should be added to the
     * order.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function buckaroo3extended_push_custom_processing(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $push = $observer->getPush();
        $response = $observer->getResponse();
        $order = $observer->getOrder();
        $postArray = $push->getPostArray();

        $push->addNote($response['message'], $this->_method);
        
        if (
            isset($postArray['brq_payment_method']) 
            && !$order->getPaymentMethodUsedForTransaction() 
            && $postArray['brq_statuscode'] == '190'
            )
        {
            $order->setPaymentMethodUsedForTransaction($postArray['brq_payment_method']);
        } elseif (
            isset($postArray['brq_transaction_method']) 
            && !$order->getPaymentMethodUsedForTransaction()
            && $postArray['brq_statuscode'] == '190'
            )
        {
            $order->setPaymentMethodUsedForTransaction($postArray['brq_transaction_method']);
        }
        $order->save();

        $push->setCustomResponseProcessing(true);
        
        return $this;
    }

    /**
     * Custom response processing for Paymentguarantee. Because paymentguarantee orders should be invoiced as soon
     * as Buckaroo says that the guarantor has approved the transaction
     * 
     * @param Varien_Event_Observer $observer
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
			case self::BUCKAROO_FAILED:		       $updatedFailed = $pushModel->processFailed($newStates, $response['message']);
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

        //add title (frontend)

        //add gender (frontend)

        //add initials (frontend)

        //add shipping address (only when different from billing address)
        $shippingInfo = array();
        if($this->isShippingDifferent()){
            $streetFull = $this->_order->getShippingAddress()->getStreetFull();

            $shippingInfo = array(
                'AddressesDiffer' => 'true',
                'ShippingTitle' => '',
                'ShippingTitle' => '',
                'ShippingGender' => '',
                'ShippingInitials' => '',
                'ShippingLastName' => '',
                'ShippingBirthDate' => '',
                'ShippingStreet' => '',
                'ShippingHouseNumber' => '',
                'ShippingHouseNumberSuffix' => '',
                'ShippingPostalCode' => '',
                'ShippingCity' => '',
                'ShippingRegion' => '',
                'ShippingCountryCode' => '',
                'ShippingEmail' => '',
                'ShippingPhoneNumber' => '',
                'ShippingLanguage' => '',
            );
        }

        //customer account number (rekening nummer

        //customer ip-address

        //is B2B? add: companyCOCregistration, companyname, costcentre,vatnumber








        $dueDays = Mage::getStoreConfig('buckaroo/buckaroo3extended_paymentguarantee/duedate', Mage::app()->getStore()->getStoreId());
        $dueDateInvoice = date('Y-m-d', mktime(0, 0, 0, date("m")  , (date("d") + $dueDays), date("Y")));
        $dueDate = date('Y-m-d', mktime(0, 0, 0, date("m")  , (date("d") + $dueDays + 14), date("Y")));

        $VAT = 0;
        foreach($this->_order->getFullTaxInfo() as $taxRecord)
        {
            $VAT += $taxRecord['amount'];
        }

        $session = Mage::getSingleton('checkout/session');
        $additionalFields = $session->getData('additionalFields');

        $gender        = $additionalFields['BPE_Customergender'];
        $dob           = $additionalFields['BPE_customerbirthdate'];
        $accountNumber = $additionalFields['BPE_AccountNumber'];
        
        $array = array(
            'InvoiceDate'           => $dueDateInvoice,
            'DateDue'               => $dueDate,
            'AmountVat'             => $VAT,
            'CustomerGender'        => $gender,
            'CustomerBirthDate'     => $dob,
            'CustomerEmail'         => $this->_billingInfo['email'],
            'customeriban'          => $accountNumber,
            'PaymentMethodsAllowed' => $this->_getPaymentMethodsAllowed(),
            'SendMail'              => Mage::getStoreConfig('buckaroo/buckaroo3extended_paymentguarantee/sendmail', Mage::app()->getStore()->getId()) ? 'TRUE' : 'FALSE',
        );
        
        if (array_key_exists('customVars', $vars) && is_array($vars['customVars'][$this->_method])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $array);
        } else {
            $vars['customVars'][$this->_method] = $array;
        }
    }

    /**
     * Checks if shipping-address is different from billing-address
     *
     * @return bool
     */
    protected function isShippingDifferent()
    {
        //exclude keys that are different in every address
        $excludeKeys = array(
            'entity_id',
            'customer_address_id',
            'quote_address_id',
            'region_id',
            'customer_id',
            'address_type'
        );
        //get the billing and shipping address
        $billingAddress          = $this->_order->getBillingAddress()->getData();
        $shippingAddress         = $this->_order->getShippingAddress()->getData();
        //match the 2 addresses if there is a change
        $filteredBillingAddress  = array_diff_key($billingAddress, array_flip($excludeKeys));
        $filteredShippingAddress = array_diff_key($shippingAddress, array_flip($excludeKeys));
        $addressDiff             = array_diff($filteredBillingAddress, $filteredShippingAddress);

        //if the addresses are not the same then $addressDiff is an array with the key(2) and values that are not the same
        $AddressesDiffer = false;
        if( $addressDiff ) {
            $AddressesDiffer = true;
        }
        return $AddressesDiffer;
    }
}