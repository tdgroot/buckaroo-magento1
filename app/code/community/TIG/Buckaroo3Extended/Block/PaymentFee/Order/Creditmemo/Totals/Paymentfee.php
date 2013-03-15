<?php
class TIG_Buckaroo3Extended_Block_PaymentFee_Order_Creditmemo_Totals_Paymentfee extends Mage_Core_Block_Abstract
{
    public function initTotals()
    {
        if(Mage::helper('buckaroo3extended')->getIsKlarnaEnabled()) {
            return $this;
        }
        
        $this->_creditmemo  = $this->getParentBlock()->getCreditmemo();
        
        $paymentmethodCode = $this->_creditmemo->getOrder()->getPayment()->getMethod();
        $this->_feeLabel = Mage::helper('buckaroo3extended')->getfeeLabel($paymentmethodCode);
        
        if ($this->_creditmemo->getId()) {
            $this->_addRefundedFee();
        } else {
            $this->_addAvailableFee();
        }
        
        return $this;
    }

    protected function _addAvailableFee()
    {
        $parent = $this->getParentBlock();
        $display = (int) Mage::getStoreConfig('tax/sales_display/subtotal', Mage::app()->getStore()->getId());
        
        if ($this->_creditmemo->getinvoice()) {
            $fee = $this->_creditmemo->getInvoice()->getBuckarooFee() - $this->_creditmemo->getOrder()->getBuckarooFeeRefunded();
            $baseFee = $this->_creditmemo->getInvoice()->getBaseBuckarooFee() - $this->_creditmemo->getOrder()->getBaseBuckarooFeeRefunded();
            
            $feeTax = $this->_creditmemo->getInvoice()->getBuckarooFeeTax() - $this->_creditmemo->getOrder()->getBuckarooFeeTaxRefunded();
            $baseFeeTax = $this->_creditmemo->getInvoice()->getBaseBuckarooFeeTax() - $this->_creditmemo->getOrder()->getBaseBuckarooFeeTaxRefunded();
        	
            $buckarooFee = new Varien_Object();
            $buckarooFee->setLabel($this->_feeLabel);
            $buckarooFee->setValue($fee);
            $buckarooFee->setBaseValue($baseFee);
            $buckarooFee->setCode('buckaroo_fee');
        } else {
            $fee = $this->_creditmemo->getOrder()->getBuckarooFee() - $this->_creditmemo->getOrder()->getBuckarooFeeRefunded();
            $baseFee = $this->_creditmemo->getOrder()->getBaseBuckarooFee() - $this->_creditmemo->getOrder()->getBaseBuckarooFeeRefunded();
            
            $feeTax = $this->_creditmemo->getOrder()->getBuckarooFeeTax() - $this->_creditmemo->getOrder()->getBuckarooFeeTaxRefunded();
            $baseFeeTax = $this->_creditmemo->getOrder()->getBaseBuckarooFeeTax() - $this->_creditmemo->getOrder()->getBaseBuckarooFeeTaxRefunded();
            
            $buckarooFee = new Varien_Object();
            $buckarooFee->setLabel($this->_feeLabel);
            $buckarooFee->setValue($fee);
            $buckarooFee->setBaseValue($baseFee);
            $buckarooFee->setCode('buckaroo_fee');
        }
        
        if ($display === 1) {
            $parent->addTotalBefore($buckarooFee, 'shipping');
        } elseif ($display === 2) {
            $buckarooFee->setValue($fee + $feeTax);
            $buckarooFee->setBaseValue($baseFee + $baseFeeTax);
            
            $parent->addTotalBefore($buckarooFee, 'shipping');
        } else {
            $feeInclLabel = $this->_feeLabel . Mage::helper('buckaroo3extended')->__(' (Incl. Tax)');
            $feeExclLabel = $this->_feeLabel . Mage::helper('buckaroo3extended')->__(' (Excl. Tax)');
            
            $buckarooFee->setLabel($feeExclLabel);
            
            $buckarooFeeInclTax = new Varien_Object();
            $buckarooFeeInclTax->setLabel($feeInclLabel);
            $buckarooFeeInclTax->setValue($fee + $feeTax);
            $buckarooFeeInclTax->setBaseValue($baseFee + $baseFeeTax);
            $buckarooFeeInclTax->setCode('buckaroo_fee_incl_tax');
            
            $parent->addTotalBefore($buckarooFee, 'shipping');
            $parent->addTotalBefore($buckarooFeeInclTax, 'shipping');
        }
    }
    
    protected function _addRefundedFee()
    {
        $parent = $this->getParentBlock();
        $display = (int) Mage::getStoreConfig('tax/sales_display/subtotal', Mage::app()->getStore()->getId());
        
        $refundedFee = $this->_creditmemo->getOrder()->getBuckarooFeeRefunded();
        $baseRefundedFee = $this->_creditmemo->getOrder()->getBaseBuckarooFeeRefunded();
        
        $buckarooFeeRefunded = new Varien_Object();
        $buckarooFeeRefunded->setLabel($this->_feeLabel);
        $buckarooFeeRefunded->setValue($refundedFee);
        $buckarooFeeRefunded->setBaseValue($baseRefundedFee);
        $buckarooFeeRefunded->setCode('buckaroo_fee_refunded');
        
        if ($display === 1) {
            $parent->addTotalBefore($buckarooFeeRefunded, 'shipping');
        } elseif ($display === 2) {
            $buckarooFeeRefunded->setValue($refundedFee + $this->_creditmemo->getBuckarooFeeTax());
            $buckarooFeeRefunded->setBaseValue($baseRefundedFee + $this->_creditmemo->getBaseBuckarooFeeTax());
            
            $parent->addTotalBefore($buckarooFeeRefunded, 'shipping');
        } else {
            $feeInclLabel = $this->_feeLabel . Mage::helper('buckaroo3extended')->__(' (Incl. Tax)');
            $feeExclLabel = $this->_feeLabel . Mage::helper('buckaroo3extended')->__(' (Excl. Tax)');
            
            $buckarooFeeRefunded->setLabel($feeExclLabel);
            
            $buckarooFeeInclTax = new Varien_Object();
            $buckarooFeeInclTax->setLabel($feeInclLabel);
            $buckarooFeeInclTax->setValue($refundedFee + $this->_creditmemo->getBuckarooFeeTax());
            $buckarooFeeInclTax->setBaseValue($baseRefundedFee + $this->_creditmemo->getBaseBuckarooFeeTax());
            $buckarooFeeInclTax->setCode('buckaroo_fee_refunded_incl_tax');
            
            $parent->addTotalBefore($buckarooFeeRefunded, 'shipping');
            $parent->addTotalBefore($buckarooFeeInclTax, 'shipping');
        }
    }
}