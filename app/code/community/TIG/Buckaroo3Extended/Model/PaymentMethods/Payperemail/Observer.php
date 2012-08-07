<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Payperemail_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract 
{    
    protected $_code = 'buckaroo3extended_payperemail';
    protected $_method = 'payperemail';
    
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
    
    public function buckaroo3extended_refund_request_addservices(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }
        
        $request = $observer->getRequest();
        $transactionMethod = $request->getOrder()->getPaymentMethodUsedForTransaction();
        $transactionMethodModel = Mage::getModel("buckaroo3extended/paymentMethods_{$transactionMethod}_paymentMethod");
        
        if ($transactionMethodModel->canRefund() && $transactionMethodModel->canRefundPartialPerInvoice()) {
            $vars = $request->getVars();
            
            $vars['services'][$transactionMethod] = array(
                'action'	=> 'Refund',
                'version'   => 1,
            );
            
            $request->setVars($vars);
        } else {
            Mage::throwException("This transaction has been paid using '{$transactionMethod}', however refunds are not available for this paymentMethod.");
        }
        
        return $this;
    }
    
    public function buckaroo3extended_refund_request_setmethod(Varien_Event_Observer $observer)
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
    
    public function buckaroo3extended_request_addcustomvars(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }
        
        $request            = $observer->getRequest();
        $this->_billingInfo = $request->getBillingInfo();
        $this->_order       = $request->getOrder();
        
        $vars = $request->getVars();
        
        $vars['customVars'][$this->_method] = array(
            'MerchantSendsEmail'    => Mage::getStoreConfig('buckaroo/buckaroo3extended_payperemail/send_mail', Mage::app()->getStore()->getStoreId()) ? 'false' : 'true',
            'customergender'        => '0',
            'PaymentMethodsAllowed' => $this->_getPaymentMethodsAllowed(),
            'CustomerEmail'         => $this->_billingInfo['email'],
            'CustomerFirstName'     => $this->_billingInfo['firstname'],
            'CustomerLastName'      => $this->_billingInfo['lastname'],
        );
        $request->setVars($vars);
        
        return $this;
    }

    /**
     * While PayperEmail is the paymentmethod for this transaction, the transation is actually completed using another paymentmethod.
     * This observer stores that paymentmethod in tyhe database. This is currently only used for online refunds.
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