<?php
class TIG_Buckaroo3Extended_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isAdmin()
	{
		if(Mage::app()->getStore()->isAdmin()) {
			return true;
		}
	
		if(Mage::getDesign()->getArea() == 'adminhtml') {
			return true;
		}
	
		return false;
	}
	
	public function log($message, $force = false)
	{
	    Mage::log($message, Zend_Log::DEBUG, 'TIG_B3E.log', $force);
	}

	public function logException($e)
	{
	    if (is_string($e)) {
	        Mage::log($e, Zend_Log::ERR, 'TIG_B3E_Exception.log', true);
	    } else {
	        Mage::log($e->getMessage(), Zend_Log::ERR, 'TIG_B3E_Exception.log', true);
	        Mage::log($e->getTraceAsString(), Zend_Log::ERR, 'TIG_B3E_Exception.log', true);
	    }
	}
    
    public function getFeeLabel($paymentMethodCode = false)
    {
        if ($paymentMethodCode) {
            $feeLabel = Mage::getStoreConfig('buckaroo3extended/buckaroo3extended_' . $paymentMethodCode . '/portfolio_payment_fee_label', Mage::app()->getStore()->getId());
            if (empty($feeLabel)) {
                $feeLabel = 'Buckaroo servicekosten';
            }
        } else {
            $feeLabel = 'Buckaroo servicekosten';
        }
        
        $feeLabel = $this->__($feeLabel);
        
        return $feeLabel;
    }
    
    public function resetPaymentFeeInvoicedValues($order, $invoice)
    {
        $basePaymentFee    = $invoice->getBasePaymentFee();
        $paymentFee        = $invoice->getPaymentFee();
        $basePaymentFeeTax = $invoice->getBasePaymentFeeTax();
        $paymentFeeTax     = $invoice->getPaymentFeeTax();
         
        $basePaymentFeeInvoiced    = $order->getBasePaymentFeeInvoiced();
        $paymentFeeInvoiced        = $order->getPaymentFeeInvoiced();
        $basePaymentFeeTaxInvoiced = $order->getBasePaymentFeeTaxInvoiced();
        $paymentFeeTaxInvoiced     = $order->getPaymentFeeTaxInvoiced();
         
        if ($basePaymentFeeInvoiced && $basePaymentFee && $basePaymentFeeInvoiced >= $basePaymentFee) {
            $order->setBasePaymentFeeInvoiced($basePaymentFeeInvoiced - $basePaymentFee)
                  ->setPaymentFeeInvoiced($paymentFeeInvoiced - $paymentFee)
                  ->setBasePaymentFeeTaxInvoiced($basePaymentFeeTaxInvoiced - $basePaymentFeeTax)
                  ->setBasePaymentFeeInvoiced($paymentFeeTaxInvoiced - $paymentFeeTax);
            $order->save();
        }
    }
    
    public function isEnterprise()
    {
        if (method_exists('Mage', 'getEdition')) {
            $edition = Mage::getEdition();
            if ($edition != 'Enterprise') {
                return false;
            }
        } else {
            return (bool) Mage::getConfig()->getModuleConfig("Enterprise_Enterprise")->version;
        }
    }
}