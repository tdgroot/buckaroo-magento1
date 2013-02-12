<?php
class TIG_Buckaroo3Extended_Block_PaymentFee_Order_Totals_Paymentfee extends Mage_Core_Block_Abstract
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $display = (int) Mage::getStoreConfig('tax/sales_display/subtotal', Mage::app()->getStore()->getId());
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
        $buckarooFee->setCode('buckarooFee');
        
        if ($display === 1) {
            $parent->addTotalBefore($buckarooFee, 'shipping');
        } elseif ($display === 2) {
            $buckarooFee->setValue($this->_order->getBuckarooFee() + $this->_order->getBuckarooFeeTax());
            $buckarooFee->setBaseValue($this->_order->getBaseBuckarooFee() + $this->_order->getBaseBuckarooFeeTax());
            
            $parent->addTotalBefore($buckarooFee, 'shipping');
        } else {
            $feeInclLabel = $feeLabel . Mage::helper('buckaroo3extended')->__(' (Incl. Tax)');
            $feeExclLabel = $feeLabel . Mage::helper('buckaroo3extended')->__(' (Excl. Tax)');
            
            $buckarooFee->setLabel($feeExclLabel);
            
            $buckarooFeeInclTax = new Varien_Object();
            $buckarooFeeInclTax->setLabel($feeInclLabel);
            $buckarooFeeInclTax->setValue($this->_order->getBuckarooFee() + $this->_order->getBuckarooFeeTax());
            $buckarooFeeInclTax->setBaseValue($this->_order->getBaseBuckarooFee() + $this->_order->getBaseBuckarooFeeTax());
            $buckarooFeeInclTax->setCode('buckaroo_fee_incl_tax');
            
            $parent->addTotalBefore($buckarooFee, 'shipping');
            $parent->addTotalBefore($buckarooFeeInclTax, 'shipping');
        }

        return $this;
    }
}