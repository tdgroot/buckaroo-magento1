<?php 
class TIG_Buckaroo3Extended_Model_Observer_BackendOrder extends Mage_Core_Model_Abstract
{
	public function checkout_submit_all_after(Varien_Event_Observer $observer)
    {
        try {
            $order = $observer->getOrder();
            $method = $order->getPayment()->getMethod();
            
            $request = Mage::getModel('buckaroo3extended/request_abstract');
            $request->setOrder($order);
            
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