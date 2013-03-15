<?php 
class TIG_Buckaroo3Extended_Model_PaymentFee_Order_Creditmemo extends Mage_Sales_Model_Order_Creditmemo
{
    public function refund()
    {
        if(Mage::helper('buckaroo3extended')->getIsKlarnaEnabled()) {
            return parent::refund();
        }
        
        Mage::dispatchEvent('buckaroofee_order_creditmemo_refund_before', array($this->_eventObject => $this));
        
        parent::refund();
    }
}