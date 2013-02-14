<?php
class TIG_Buckaroo3Extended_Model_PaymentFee_Order_Invoice_Total extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Retrieves Payment Fee values, calculates the amount that needs to be invoiced
     * 
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        
        //retrieve all base fee-related values from order
        $baseBuckarooFee             = $order->getBaseBuckarooFee();
        $baseBuckarooFeeInvoiced     = $order->getBaseBuckarooFeeInvoiced();
        $baseBuckarooFeeTax          = $order->getBaseBuckarooFeeTax();
        $baseBuckarooFeeTaxInvoiced  = $order->getBaseBuckarooFeeTaxInvoiced();
        
        //retrieve all fee-related values from order
        $buckarooFee                 = $order->getBuckarooFee();
        $buckarooFeeInvoiced         = $order->getBuckarooFeeInvoiced();
        $buckarooFeeTax              = $order->getBuckarooFeeTax();
        $buckarooFeeTaxInvoiced      = $order->getBuckarooFeeTaxInvoiced();
        
        //get current invoice totals
        $baseInvoiceTotal            = $invoice->getBaseGrandTotal();
        $invoiceTotal                = $invoice->getGrandTotal();
        
        $baseTaxAmountTotal          = $invoice->getBaseTaxAmount();
        $taxAmountTotal              = $invoice->getTaxAmount();

        //calculate how much needs to be invoiced
        $baseBuckarooFeeToInvoice    = $baseBuckarooFee - $baseBuckarooFeeInvoiced;
        $buckarooFeeToInvoice        = $buckarooFee - $buckarooFeeInvoiced;
        
        $baseBuckarooFeeTaxToInvoice = $baseBuckarooFeeTax - $baseBuckarooFeeTaxInvoiced;
        $buckarooFeeTaxToInvoice     = $buckarooFeeTax - $buckarooFeeTaxInvoiced;
        
        $baseInvoiceTotal           += $baseBuckarooFeeToInvoice;
        $invoiceTotal               += $buckarooFeeToInvoice;
        
        $invoice->setBaseGrandTotal($baseInvoiceTotal);
        $invoice->setGrandTotal($invoiceTotal);
        
        $invoice->setBaseTaxAmount($baseTaxAmountTotal);
        $invoice->setTaxAmount($taxAmountTotal);

        //fix for issue where invoice totals is sometimes missing paymentfee tax
        //underlying cause currently unknown
        if ($invoice->getBaseGrandTotal() < $order->getBaseGrandTotal() && $baseBuckarooFeeToInvoice) {
        	$invoice->setBaseGrandTotal($baseInvoiceTotal + $baseBuckarooFeeTaxToInvoice);
        	$invoice->setGrandTotal($invoiceTotal + $buckarooFeeTaxToInvoice * 2); //@TODO figure out why this needs to be doubled
        	
	        $invoice->setBaseTaxAmount($baseTaxAmountTotal + $baseBuckarooFeeTaxToInvoice);
	        $invoice->setTaxAmount($taxAmountTotal + $baseBuckarooFeeTaxToInvoice);
        }
		
		//@TODO figure out why this is needed
		if ($invoice->getBaseGrandTotal() == $order->getBaseGrandTotal()) {
			$invoice->setSubtotalInclTax($invoice->getSubtotalInclTax() - $buckarooFeeTaxToInvoice - $buckarooFeeInvoiced);
			$invoice->setBaseSubtotalInclTax($invoice->getBaseSubtotalInclTax() - $baseBuckarooFeeTaxToInvoice - $baseBuckarooFeeInvoiced);
		}
        
        $invoice->setBaseBuckarooFee($baseBuckarooFeeToInvoice);
        $invoice->setBuckarooFee($buckarooFeeToInvoice);
        
        $invoice->setBaseBuckarooFeeTax($baseBuckarooFeeTaxToInvoice);
        $invoice->setBuckarooFeeTax($buckarooFeeTaxToInvoice);
        return $this;
    }
}