<?php 
class TIG_Buckaroo3Extended_Model_Observer_BackendOrder extends Mage_Core_Model_Abstract
{
	public function checkout_submit_all_after(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $method = $order->getPayment()->getMethod();
        
        if (strpos($method, 'buckaroo3extended') === false) {
            return $this;
        }
        
        try {
            $request = Mage::getModel('buckaroo3extended/request_abstract');
            $request->setOrder($order)
                    ->setOrderBillingInfo();
            
            $request->sendRequest();
	    } catch (Exception $e) {
	        Mage::getSingleton('core/session')->addError(
                Mage::helper('buckaroo3extended')->__($e->getMessage())
            );
            Mage::throwException($e->getMessage());
	    }
	    
        return $this;
    }
}