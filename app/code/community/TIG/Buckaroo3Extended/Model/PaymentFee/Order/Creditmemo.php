<?php 
class TIG_Buckaroo3Extended_Model_PaymentFee_Order_Creditmemo extends Mage_Sales_Model_Order_Creditmemo
{
    public function refund()
    {
        Mage::dispatchEvent('buckaroofee_order_creditmemo_refund_before', array($this->_eventObject => $this));
        
        parent::refund();
    }
}