<?php
class TIG_Buckaroo3Extended_Model_PaymentFee_Observer extends Mage_Core_Model_Abstract 
{    
	/**
     * Collects buckarooFee from quote/addresses to quote
     *
     * @param Varien_Event_Observer $observer
     */
    public function sales_quote_collect_totals_after(Varien_Event_Observer $observer) 
    {
        $quote = $observer->getEvent()->getQuote();
        
        $quote->setBuckarooFee(0);
        $quote->setBaseBuckarooFee(0);
        $quote->setBuckarooFeeTax(0);
        $quote->setBaseBuckarooFeeTax(0);
                
        foreach ($quote->getAllAddresses() as $address) 
        {
            if (!$quote->getBuckarooFee()) {
                $quote->setBuckarooFee((float) $address->getBuckarooFee());
            }
            if (!$quote->getBaseBuckarooFee()) {
                $quote->setBaseBuckarooFee((float) $address->getBaseBuckarooFee());
            }
            if (!$quote->getBuckarooFeeTax()) {
                $quote->setBuckarooFeeTax((float) $address->getBuckarooFeeTax());
            }
            if (!$quote->getBaseBuckarooFeeTax()) {
                $quote->setBaseBuckarooFeeTax((float) $address->getBaseBuckarooFeeTax());
            }
        }
        
        $quote->save();
    }

    /**
     * Adds BuckarooFee to order
     * 
     * @param Varien_Event_Observer $observer
     */
    public function sales_order_payment_place_end(Varien_Event_Observer $observer) 
    {
        $payment = $observer->getPayment();

        $order = $payment->getOrder();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        
        if (!$quote->getId()) {
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        
        $order->setBaseBuckarooFee($quote->getBaseBuckarooFee());
        $order->setBuckarooFee($quote->getBuckarooFee());
        
        $order->setBaseBuckarooFeeTax($quote->getBaseBuckarooFeeTax());
        $order->setBuckarooFeeTax($quote->getBuckarooFeeTax());
        
        $order->setBaseTaxAmount($order->getBaseTaxAmount());
        $order->setTaxAmount($order->getTaxAmount());
        
        $order->save();
        
        $info = $payment->getMethodInstance()->getInfoInstance();
        
        $info->setAdditionalInformation('buckaroo_fee', $quote->getBuckarooFee());
        $info->setAdditionalInformation('base_buckaroo_fee', $quote->getBaseBuckarooFee());
        
        $info->setAdditionalInformation('buckaroo_fee_tax', $quote->getBuckarooFeeTax());
        $info->setAdditionalInformation('base_buckaroo_fee_tax', $quote->getBaseBuckarooFeeTax());
        
        $info->save();
    }
    
    /**
     * Adds the payment fee to the creditmemo
     * 
     * @param Varien_Event_Observer $observer
     */
    public function buckaroofee_order_creditmemo_refund_before(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getCreditmemo();
        
        $buckarooFee = Mage::getModel('buckaroo3extended/paymentFee_refund', $creditmemo);
        $creditmemo = $buckarooFee->buckarooFeeRefund();
    }
    
    /**
     * Updates the order with the newly invoiced values
     * 
     * @param Varien_Event_Observer $observer
     */
    public function sales_order_invoice_register(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getInvoice();
        $order = $invoice->getOrder();
        
        $order->setBaseBuckarooFeeInvoiced($invoice->getBaseBuckarooFee());
        $order->setBuckarooFeeInvoiced($invoice->getBuckarooFee());
        
        $order->setBaseBuckarooFeeTaxInvoiced($invoice->getBaseBuckarooFeeTax());
        $order->setBuckarooFeeTaxInvoiced($invoice->getBuckarooFeeTax());
        $order->save();
    }
}