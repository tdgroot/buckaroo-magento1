<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_paymentguarantee_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract
{
    protected $_code = 'buckaroo3extended_paymentguarantee';
    protected $_method = 'paymentguarantee';

    public function buckaroo3extended_request_addservices(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request = $observer->getRequest();

        $vars = $request->getVars();

        $vars['services'][$this->_method] = array(
            'action'	=> 'PaymentInvitation',
            'version'   => 1,
        );

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
        $this->_addPaymentGuaranteeVariables($vars);

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

    public function buckaroo3extended_push_custom_processing(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $push = $observer->getPush();
        $response = $observer->getResponse();

        $push->addNote($response['message'], $this->_method);

        $push->setCustomResponseProcessing(true);
    }

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

    protected function _addPaymentGuaranteeVariables(&$vars)
    {
        $dueDays = Mage::getStoreConfig('buckaroo/buckaroo3extended_paymentguarantee/due_date', Mage::app()->getStore()->getStoreId());
        $dueDateInvoice = date('Y-m-d', mktime(0, 0, 0, date("m")  , (date("d") + $dueDays), date("Y")));
        $dueDate = date('Y-m-d', mktime(0, 0, 0, date("m")  , (date("d") + $dueDays + 14), date("Y")));

        $VAT = 0;
        foreach($this->_order->getFullTaxInfo() as $taxRecord)
        {
            $VAT += $taxRecord['amount'];
        }
        $VAT = round($VAT * 100,0);

        $session = Mage::getSingleton('checkout/session');
        $additionalFields = $session->getData('additionalFields');

        $gender        = $additionalFields['BPE_Customergender'];
        $dob           = $additionalFields['BPE_customerbirthdate'];
        $accountNumber = $additionalFields['BPE_AccountNumber'];

        $vars['customVars'][$this->_method]['InvoiceDate']           = $dueDateInvoice;
        $vars['customVars'][$this->_method]['DateDue']               = $dueDate;
        $vars['customVars'][$this->_method]['AmountVat']             = $VAT;
        $vars['customVars'][$this->_method]['CustomerGender']        = $gender;
        $vars['customVars'][$this->_method]['CustomerBirthDate']     = $dob;
        $vars['customVars'][$this->_method]['CustomerEmail']         = $this->_billingInfo['email'];
        $vars['customVars'][$this->_method]['CustomerAccountNumber'] = $accountNumber;
        $vars['customVars'][$this->_method]['PaymentMethodsAllowed'] = $this->_getPaymentMethodsAllowed();
    }
}