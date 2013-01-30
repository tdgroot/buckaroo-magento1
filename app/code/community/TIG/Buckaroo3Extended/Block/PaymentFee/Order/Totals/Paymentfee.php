<?php
class TIG_Buckaroo3Extended_Block_PaymentFee_Order_Totals_Paymentfee extends Mage_Core_Block_Abstract
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order  = $parent->getOrder();
     
        if ($this->_order->getBuckarooFee() < 0.01 || $this->_order->getBuckarooFee() < 0.01) {
            return $this;
        }
        
        $paymentmethodCode = $this->_order->getPayment()->getMethod();
        $feeLabel = Mage::helper('buckaroo3extended')->getfeeLabel($this->_order->getPayment()->getMethod());
        
        $buckarooFee = new Varien_Object();
        $buckarooFee->setLabel($feeLabel);
        $buckarooFee->setValue($this->_order->getBuckarooFee());
        $buckarooFee->setBaseValue($this->_order->getBaseBuckarooFee());
        $buckarooFee->setCode('buckaroo_fee');
  
        $parent->addTotalBefore($buckarooFee, 'tax');

        return $this;
    }
}