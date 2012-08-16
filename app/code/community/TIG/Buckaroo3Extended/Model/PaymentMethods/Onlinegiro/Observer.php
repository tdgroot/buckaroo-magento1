<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Onlinegiro_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract 
{    
    protected $_code = 'buckaroo3extended_onlinegiro';
    protected $_method = 'onlinegiro';
    
    public function buckaroo3extended_request_addservices(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }
        
        $request = $observer->getRequest();
        
        $vars = $request->getVars();
        
        $array = array(
            'action'	=> 'PaymentInvitation',
            'version'   => 1,
        );
        
        if (is_array($vars['services'][$this->_method])) {
            $vars['services'][$this->_method] = array_merge($vars['services'][$this->_method], $array);
        } else {
            $vars['services'][$this->_method] = $array;
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
        $additionalFields = Mage::getSingleton('checkout/session')->getData('additionalFields');
        
        if (is_array($additionalFields) 
            && array_key_exists('gender', $additionalFields)
            && array_key_exists('mail', $additionalFields)
            && array_key_exists('firstname', $additionalFields)
            && array_key_exists('lastname', $additionalFields)
        ) {
            $array = array(
                'customergender'        => $additionalFields['gender'],
                'CustomerEmail'         => $additionalFields['mail'],
                'CustomerFirstName'     => $additionalFields['firstname'],
                'CustomerLastName'      => $additionalFields['lastname'],
            );
        } else {
            $array = array();
        }
        
        if (is_array($vars['customVars'][$this->_method])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $array);
        } else {
            $vars['customVars'][$this->_method] = $array;
        }
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
     * While onlinegiro is the paymentmethod for this transaction, the transation is actually completed using another paymentmethod.
     * This observer stores that paymentmethod in the database. This is currently only used for online refunds.
     * 
     * @param Varien_Event_Observer $observer
     */
    public function buckaroo3extended_push_custom_processing(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $push = $observer->getPush();
        $order = $observer->getOrder();
        $postArray = $push->getPostArray();
        
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

        //if set to true, the push processing will be stopped here. Needs to be set to false, to make
        //sure the order is still updated.
        $push->setCustomResponseProcessing(false);
        
        return $this;
    }
    
    protected function _isChosenMethod($observer)
    {
        $ret = false;
        
        $chosenMethod = $observer->getOrder()->getPayment()->getMethod();
        
        if ($chosenMethod === $this->_code) {
            $ret = true;
        }
        return $ret;
    }
}