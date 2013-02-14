<?php
class TIG_Buckaroo3Extended_Model_PaymentFee_Refund extends Mage_Core_Model_Abstract
{
    private $_creditmemo;
    
    protected $_order;
    protected $_invoice;
    
    public function setCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $this->_creditmemo = $creditmemo;
    }
    
    public function getCreditmemo()
    {
        return $this->_creditmemo;
    }
    
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
    }
    
    public function getOrder()
    {
        return $this->_order;
    }
    
    public function setInvoice(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $this->_invoice = $invoice;
    }
    
    public function getInvoice()
    {
        return $this->_invoice;
    }
    
    protected function _setOrderFromCreditmemo()
    {
        $order = $this->_creditmemo->getOrder();
        $this->setOrder($order);
    }
    
    protected function _setInvoiceFromCreditmemo()
    {
        $invoice = $this->_creditmemo->getInvoice();
        if ($invoice) {
            $this->setInvoice($invoice);
        }
    }
    
    public function __construct(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $this->setCreditmemo($creditmemo);
        $this->_setOrderFromCreditmemo();
        $this->_setInvoiceFromCreditmemo();
    }
    
    public function buckarooFeeRefund()
    {
        $orderConvertRate                        = $this->_order->getBaseToOrderRate();
        
        if (empty($orderConvertRate)) {
            $orderConvertRate = 1;
        }
        
        //get amounts that are to be refunded
        $baseBuckarooFeeToRefund                  = (float) $this->_creditmemo->getBuckarooFeeToRefund();
        
        //in order to prevent rounding errors from causing errors
        if (
            $baseBuckarooFeeToRefund > $this->_order->getBaseBuckarooFee() 
            && $baseBuckarooFeeToRefund == round($this->_order->getBaseBuckarooFee(), 2)
        ) {
            $baseBuckarooFeeToRefund = $this->_order->getBaseBuckarooFee();
        }
		
        $buckarooFeeToRefund                      = (float) $baseBuckarooFeeToRefund * $orderConvertRate;
        
        $baseBuckarooFeeTaxToRefund               = (float) $this->_calculateBuckarooFeeTaxToRefund($baseBuckarooFeeToRefund, true);
        $buckarooFeeTaxToRefund                   = (float) $this->_calculateBuckarooFeeTaxToRefund($buckarooFeeToRefund);
        
        $buckarooFeeToRefund                     -= $buckarooFeeTaxToRefund;
        $baseBuckarooFeeToRefund                 -= $baseBuckarooFeeTaxToRefund;
        
        if ($this->_invoice) {
            //get the amounts that are available to refund (cant refund more than is available)
            $buckarooFeeAvailableForRefund        = $this->_invoice->getBuckarooFee() - $this->_order->getBuckarooFeeRefunded() + ($this->_invoice->getBuckarooFeeTax() - $this->_order->getBuckarooFeeTaxRefunded());
            $baseBuckarooFeeAvailableForRefund    = $this->_invoice->getBaseBuckarooFee() - $this->_order->getBaseBuckarooFeeRefunded() + ($this->_invoice->getBaseBuckarooFeeTax() - $this->_order->getBaseBuckarooFeeTaxRefunded());
            
            $buckarooFeeTaxAvailableForRefund     = $this->_invoice->getBuckarooFeeTax() - $this->_order->getBuckarooFeeTaxRefunded();
            $baseBuckarooFeeTaxAvailableForRefund = $this->_invoice->getBaseBuckarooFeeTax() - $this->_order->getBaseBuckarooFeeTaxRefunded();
        } else {
            //get the amounts that are available to refund (cant refund more than is available)
            $buckarooFeeAvailableForRefund        = $this->_order->getBuckarooFee() - $this->_order->getBuckarooFeeRefunded() + ($this->_order->getBuckarooFeeTax() - $this->_order->getBuckarooFeeTaxRefunded());
            $baseBuckarooFeeAvailableForRefund    = $this->_order->getBaseBuckarooFee() - $this->_order->getBaseBuckarooFeeRefunded() + ($this->_order->getBaseBuckarooFeeTax() - $this->_order->getBaseBuckarooFeeTaxRefunded());
            
            $buckarooFeeTaxAvailableForRefund     = $this->_order->getBuckarooFeeTax() - $this->_order->getBuckarooFeeTaxRefunded();
            $baseBuckarooFeeTaxAvailableForRefund = $this->_order->getBaseBuckarooFeeTax() - $this->_order->getBaseBuckarooFeeTaxRefunded();
        }
        
        //check if the amount that is to be invoiced exceeds the available amount
        if (
            $buckarooFeeAvailableForRefund - $buckarooFeeTaxAvailableForRefund             < $buckarooFeeToRefund
            || $baseBuckarooFeeAvailableForRefund - $baseBuckarooFeeTaxAvailableForRefund  < $baseBuckarooFeeToRefund
            || $buckarooFeeTaxAvailableForRefund                                           < $buckarooFeeTaxToRefund
            || $baseBuckarooFeeTaxAvailableForRefund                                       < $baseBuckarooFeeTaxToRefund
        ) {
       		$error = Mage::helper('buckaroo3extended')->__(
                	    'You cannot refund a larger amount than is available. Maximum Payment Fee available for refund: %s',
                	    number_format($baseBuckarooFeeAvailableForRefund, 2)
               		 );
            Mage::throwException($error);
        }
        
        $this->_order->setBuckarooFeeRefunded($this->_order->getBuckarooFeeRefunded() + $buckarooFeeToRefund);
        $this->_order->setBaseBuckarooFeeRefunded($this->_order->getBaseBuckarooFeeRefunded() + $baseBuckarooFeeToRefund);
        
        $this->_order->setBuckarooFeeTaxRefunded($this->_order->getBuckarooFeeTaxRefunded() + $buckarooFeeTaxToRefund);
        $this->_order->setBaseBuckarooFeeTaxRefunded($this->_order->getBaseBuckarooFeeTaxRefunded() + $baseBuckarooFeeTaxToRefund);
		
        $this->_creditmemo->setGrandTotal($this->_creditmemo->getGrandTotal() - ($this->_creditmemo->getBuckarooFee() + $this->_creditmemo->getBuckarooFeeTax()) + ($buckarooFeeToRefund + $buckarooFeeTaxToRefund));
        $this->_creditmemo->setBaseGrandTotal($this->_creditmemo->getBaseGrandTotal() - ($this->_creditmemo->getBaseBuckarooFee() + $this->_creditmemo->getBaseBuckarooFeeTax()) + ($baseBuckarooFeeToRefund + $baseBuckarooFeeTaxToRefund));
        
        $this->_creditmemo->setBaseBuckarooFee($baseBuckarooFeeToRefund);
        $this->_creditmemo->setBuckarooFee($buckarooFeeToRefund);
        
        $this->_creditmemo->setBaseBuckarooFeeTax($baseBuckarooFeeTaxToRefund);
        $this->_creditmemo->setBuckarooFeeTax($buckarooFeeTaxToRefund);
        
        $this->_creditmemo->setTaxAmount($this->_creditmemo->getTaxAmount() + $buckarooFeeTaxToRefund);
        $this->_creditmemo->setBaseTaxAmount($this->_creditmemo->getBaseTaxAmount() + $baseBuckarooFeeTaxToRefund);
		
        return $this->_creditmemo;
    }
    
    protected function _calculateBuckarooFeeTaxToRefund($feeToRefund, $base = false)
    {
        if ($base === true) {
            $fee = $this->_order->getBaseBuckarooFeeInvoiced() - $this->_order->getBaseBuckarooFeeRefunded() + ($this->_order->getBaseBuckarooFeeTaxInvoiced() - $this->_order->getBaseBuckarooFeeTaxRefunded());
            $tax = $this->_order->getBaseBuckarooFeeTaxInvoiced() - $this->_order->getBaseBuckarooFeeTaxRefunded();
        } else {
            $fee = $this->_order->getBuckarooFeeInvoiced() - $this->_order->getBuckarooFeeRefunded() + ($this->_order->getBuckarooFeeTaxInvoiced() - $this->_order->getBuckarooFeeTaxRefunded());
            $tax = $this->_order->getBuckarooFeeTaxInvoiced() - $this->_order->getBuckarooFeeTaxRefunded();
        }
        
        if ($fee == 0) {
            return 0;
        }
        
        $ratio = $feeToRefund / $fee;
        
        $taxToRefund = $tax * $ratio;
        
        return $taxToRefund;
    }
}