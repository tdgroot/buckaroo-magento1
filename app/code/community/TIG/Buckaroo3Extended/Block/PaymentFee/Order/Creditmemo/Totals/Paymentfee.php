<?php
class TIG_Buckaroo3Extended_Block_PaymentFee_Order_Creditmemo_Totals_Paymentfee extends Mage_Core_Block_Abstract
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_creditmemo  = $parent->getCreditmemo();
        
        $paymentmethodCode = $this->_creditmemo->getOrder()->getPayment()->getMethod();
        $feeLabel = Mage::helper('buckaroo3extended')->getfeeLabel($paymentmethodCode);
        
        if ($this->_creditmemo->getinvoice()) {
            $buckarooFee = new Varien_Object();
            $buckarooFee->setLabel($feeLabel . ' available for refund');
            $buckarooFee->setValue($this->_creditmemo->getInvoice()->getBuckarooFee() - $this->_creditmemo->getOrder()->getBuckarooFeeRefunded() + ($this->_creditmemo->getInvoice()->getBuckarooFeeTax() - $this->_creditmemo->getOrder()->getBuckarooFeeTaxefunded()));
            $buckarooFee->setBaseValue($this->_creditmemo->getInvoice()->getBaseBuckarooFee() - $this->_creditmemo->getOrder()->getBaseBuckarooFeeRefunded() + ($this->_creditmemo->getInvoice()->getBaseBuckarooFeeTax() - $this->_creditmemo->getOrder()->getBaseBuckarooFeeTaxRefunded()));
            $buckarooFee->setCode('buckaroo_fee');
        } else {
            $buckarooFee = new Varien_Object();
            $buckarooFee->setLabel($feeLabel . ' available for refund');
            $buckarooFee->setValue($this->_creditmemo->getOrder()->getBuckarooFee() - $this->_creditmemo->getOrder()->getBuckarooFeeRefunded() + ($this->_creditmemo->getOrder()->getBuckarooFeeTax() - $this->_creditmemo->getOrder()->getBuckarooFeeTaxRefunded()));
            $buckarooFee->setBaseValue($this->_creditmemo->getOrder()->getBaseBuckarooFee() - $this->_creditmemo->getOrder()->getBaseBuckarooFeeRefunded() + ($this->_creditmemo->getOrder()->getBaseBuckarooFeeTax() - $this->_creditmemo->getOrder()->getBaseBuckarooFeeTaxRefunded()));
            $buckarooFee->setCode('buckaroo_fee');
        }
        
        $buckarooFeeRefunded = new Varien_Object();
        $buckarooFeeRefunded->setLabel($feeLabel . ' refunded');
        $buckarooFeeRefunded->setValue($this->_creditmemo->getOrder()->getBuckarooFeeRefunded() + $this->_creditmemo->getOrder()->getBuckarooFeeTaxRefunded());
        $buckarooFeeRefunded->setBaseValue($this->_creditmemo->getOrder()->getBaseBuckarooFeeRefunded() + $this->_creditmemo->getOrder()->getBaseBuckarooFeeTaxRefunded());
        $buckarooFeeRefunded->setCode('buckaroot_fee_refunded');
        
        $parent->addTotalBefore($buckarooFee, 'tax');
        $parent->addTotalBefore($buckarooFeeRefunded, 'buckaroo_fee');

        return $this;
    }
}