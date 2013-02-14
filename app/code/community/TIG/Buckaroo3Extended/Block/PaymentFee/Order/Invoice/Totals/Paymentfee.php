<?php
class TIG_Buckaroo3Extended_Block_PaymentFee_Order_Invoice_Totals_Paymentfee extends Mage_Core_Block_Abstract
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $display = (int) Mage::getStoreConfig('tax/sales_display/subtotal', Mage::app()->getStore()->getId());
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
        
        if ($display === 1) {
            $parent->addTotalBefore($buckarooFee, 'shipping');
        } elseif ($display === 2) {
            $buckarooFee->setValue($this->_invoice->getBuckarooFee() + $this->_invoice->getBuckarooFeeTax());
            $buckarooFee->setBaseValue($this->_invoice->getBaseBuckarooFee() + $this->_invoice->getBaseBuckarooFeeTax());
            
            $parent->addTotalBefore($buckarooFee, 'shipping');
        } else {
            $feeInclLabel = $feeLabel . Mage::helper('buckaroo3extended')->__(' (Incl. Tax)');
            $feeExclLabel = $feeLabel . Mage::helper('buckaroo3extended')->__(' (Excl. Tax)');
            
            $buckarooFee->setLabel($feeExclLabel);
            
            $buckarooFeeInclTax = new Varien_Object();
            $buckarooFeeInclTax->setLabel($feeInclLabel);
            $buckarooFeeInclTax->setValue($this->_invoice->getBuckarooFee() + $this->_invoice->getBuckarooFeeTax());
            $buckarooFeeInclTax->setBaseValue($this->_invoice->getBaseBuckarooFee() + $this->_invoice->getBaseBuckarooFeeTax());
            $buckarooFeeInclTax->setCode('buckaroo_fee_incl_tax');
            
            $parent->addTotalBefore($buckarooFee, 'shipping');
            $parent->addTotalBefore($buckarooFeeInclTax, 'shipping');
        }

        return $this;
    }
}