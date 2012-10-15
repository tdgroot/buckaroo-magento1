<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Giftcards_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract 
{    
    protected $_code = 'buckaroo3extended_giftcards';
    protected $_method = 'giftcards';
    
    public function buckaroo3extended_request_addservices(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }
        
        $request = $observer->getRequest();
        
        $vars = $request->getVars();
        
        $vars['services'][$this->_method] = false;
        
        $request->setVars($vars);
        
        return $this;
    }
    
    public function buckaroo3extended_request_addcustomvars(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request = $observer->getRequest();
        $vars = $request->getVars();
        
        $availableCards = Mage::getStoreConfig('buckaroo/buckaroo3extended_giftcards/cards_allowed', Mage::app()->getStore()->getId());
        
        $array = array(
        		'servicesSelectableByClient' => $availableCards,
        		'continueOnImcomplete'       => 'RedirectToHTML',
        );
        
        if (
        	array_key_exists('customVars', $vars)
        	&& array_key_exists($this->_method, $vars['customVars']) 
        	&& is_array($vars['customVars'][$this->_method])
        ) {
        	$vars['customVars'] = array_merge($vars['customVars'], $array);
        } else {
        	$vars['customVars'] = $array;
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
     * Custom push processing for Paymentguarantee. Because paymentguarantee orders should have been invoiced as 
     * soon as Buckaroo said that the guarantor had approved the transaction only a note should be added to the
     * order.
     * 
     * @param Varien_Event_Observer $observer
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