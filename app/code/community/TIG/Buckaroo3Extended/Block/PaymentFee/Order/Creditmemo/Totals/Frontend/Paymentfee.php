<?php
class TIG_Buckaroo3Extended_Block_PaymentFee_Order_Creditmemo_Totals_Frontend_Paymentfee extends Mage_Core_Block_Abstract
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_creditmemo  = $parent->getCreditmemo();
        
        $paymentmethodCode = $this->_creditmemo->getOrder()->getPayment()->getMethod();
        $feeLabel = Mage::helper('buckaroo3extended')->getfeeLabel($paymentmethodCode);
        
        $buckarooFeeRefunded = new Varien_Object();
        $buckarooFeeRefunded->setLabel($feeLabel);
        $buckarooFeeRefunded->setValue($this->_creditmemo->getOrder()->getBuckarooFeeRefunded() + $this->_creditmemo->getOrder()->getBuckarooFeeTaxRefunded());
        $buckarooFeeRefunded->setBaseValue($this->_creditmemo->getOrder()->getBaseBuckarooFeeRefunded() + $this->_creditmemo->getOrder()->getBaseBuckarooFeeTaxRefunded());
        $buckarooFeeRefunded->setCode('buckaroo_fee_refunded');
        
        $parent->addTotalBefore($buckarooFeeRefunded, 'tax');

        return $this;
    }
}