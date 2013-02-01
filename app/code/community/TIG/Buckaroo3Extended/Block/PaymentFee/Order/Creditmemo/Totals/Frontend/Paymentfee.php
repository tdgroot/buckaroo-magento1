<?php
class TIG_Buckaroo3Extended_Block_PaymentFee_Order_Creditmemo_Totals_Frontend_Paymentfee extends Mage_Core_Block_Abstract
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $display = (int) Mage::getStoreConfig('tax/sales_display/subtotal', Mage::app()->getStore()->getId());
        $this->_creditmemo  = $parent->getCreditmemo();
        
        $paymentmethodCode = $this->_creditmemo->getOrder()->getPayment()->getMethod();
        $feeLabel = Mage::helper('buckaroo3extended')->getfeeLabel($paymentmethodCode);
        
        $buckarooFeeRefunded = new Varien_Object();
        $buckarooFeeRefunded->setLabel($feeLabel);
        $buckarooFeeRefunded->setValue($this->_creditmemo->getOrder()->getBuckarooFeeRefunded() + $this->_creditmemo->getOrder()->getBuckarooFeeTaxRefunded());
        $buckarooFeeRefunded->setBaseValue($this->_creditmemo->getOrder()->getBaseBuckarooFeeRefunded() + $this->_creditmemo->getOrder()->getBaseBuckarooFeeTaxRefunded());
        $buckarooFeeRefunded->setCode('buckaroo_fee_refunded');
        
        if ($display === 1) {
            $parent->addTotalBefore($buckarooFeeRefunded, 'shipping');
        } elseif ($display === 2) {
            $buckarooFeeRefunded->setValue($this->_creditmemo->getBuckarooFee() + $this->_creditmemo->getBuckarooFeeTax());
            $buckarooFeeRefunded->setBaseValue($this->_creditmemo->getBuckarooBaseFee() + $this->_creditmemo->getBaseBuckarooFeeTax());
            
            $parent->addTotalBefore($buckarooFeeRefunded, 'shipping');
        } else {
            $feeInclLabel = $feeLabel . Mage::helper('buckaroo3extended')->__(' (Incl. Tax)');
            $feeExclLabel = $feeLabel . Mage::helper('buckaroo3extended')->__(' (Excl. Tax)');
            
            $buckarooFeeRefunded->setLabel($feeExclLabel);
            
            $buckarooFeeInclTax = new Varien_Object();
            $buckarooFeeInclTax->setLabel($feeInclLabel);
            $buckarooFeeInclTax->setValue($this->_creditmemo->getBuckarooFee() + $this->_creditmemo->getBuckarooFeeTax());
            $buckarooFeeInclTax->setBaseValue($this->_creditmemo->getBuckarooBaseFee() + $this->_creditmemo->getBaseBuckarooFeeTax());
            $buckarooFeeInclTax->setCode('buckaroo_fee_refunded_incl_tax');
            
            $parent->addTotalBefore($buckarooFeeRefunded, 'shipping');
            $parent->addTotalBefore($buckarooFeeInclTax, 'shipping');
        }

        return $this;
    }
}