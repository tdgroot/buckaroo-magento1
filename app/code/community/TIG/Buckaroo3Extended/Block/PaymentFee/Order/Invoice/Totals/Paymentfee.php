<?php
class TIG_Buckaroo3Extended_Block_PaymentFee_Order_Invoice_Totals_Paymentfee extends Mage_Core_Block_Abstract
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_invoice  = $parent->getInvoice();
        
        if (
            ($this->_invoice->getBuckarooFee() < 0.01 || $this->_invoice->getBuckarooFee() < 0.01)
            && ($this->_invoice->getOrder()->getBaseBuckarooFee() - $this->_invoice->getOrder()->getBaseBuckarooFeeInvoiced()) < 0.01
           ) 
        {
            return $this;
        }
        
        $paymentmethodCode = $this->_invoice->getOrder()->getPayment()->getMethod();
        $feeLabel = Mage::helper('buckaroo3extended')->getfeeLabel($paymentmethodCode);
        
        $buckarooFee = new Varien_Object();
        $buckarooFee->setLabel($feeLabel);
        $buckarooFee->setValue($this->_invoice->getBuckarooFee());
        $buckarooFee->setBaseValue($this->_invoice->getBaseBuckarooFee());
        $buckarooFee->setCode('buckaroo_fee');
        
        $parent->addTotalBefore($buckarooFee, 'tax');

        return $this;
    }
}