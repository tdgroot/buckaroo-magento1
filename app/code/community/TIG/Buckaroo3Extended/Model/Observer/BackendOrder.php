<?php
class TIG_Buckaroo3Extended_Model_Observer_BackendOrder extends Mage_Core_Model_Abstract
{
	public function checkout_submit_all_after(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $method = $order->getPayment()->getMethod();

        $newStates = Mage::helper('buckaroo3extended')->getNewStates('BUCKAROO_PENDING_PAYMENT', $order, $method);

        var_dump($newStates);
        die();

        $newStates = Mage::helper('buckaroo3extended')->getNewStates('BUCKAROO_PENDING_PAYMENT', $order, $method);

        if (strpos($method, 'buckaroo3extended') === false) {
            return $this;
        }

        try {
            $request = Mage::getModel('buckaroo3extended/request_abstract');
            $request->setOrder($order)
                    ->setOrderBillingInfo();

            $request->sendRequest();
	    } catch (Exception $e) {
            Mage::throwException($e->getMessage());
	    }

        return $this;
    }
}