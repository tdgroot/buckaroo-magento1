<?php 
class TIG_Buckaroo3Extended_Model_PaymentFee_Service_Order extends Mage_Sales_Model_Service_Order
{
	/**
     * Initialize creditmemo state based on requested parameters
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @param array $data
     */
    protected function _initCreditmemoData($creditmemo, $data)
    {
        if(Mage::helper('buckaroo3extended')->getIsKlarnaEnabled()) {
            return parent::_initCreditmemoData($creditmemo, $data);
        }
        
        if (isset($data['buckaroofee'])) {
            $creditmemo->setBuckarooFeeToRefund($data['buckaroofee']);
        }
        
        return parent::_initCreditmemoData($creditmemo, $data);
    }
}