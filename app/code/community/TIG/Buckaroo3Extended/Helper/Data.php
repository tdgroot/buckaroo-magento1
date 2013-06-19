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

    public function isOneStepCheckout()
    {
        $moduleName = Mage::app()->getRequest()->getModuleName();

        if ($moduleName == 'onestepcheckout') {
            return true;
        }
        return false;
    }

    public function getFeeLabel($paymentMethodCode = false)
    {
        if ($paymentMethodCode) {
            $feeLabel = Mage::helper('buckaroo3extended')->__(
                Mage::getStoreConfig('buckaroo/' . $paymentMethodCode . '/payment_fee_label', Mage::app()->getStore()->getId())
            )
            ;
            if (empty($feeLabel)) {
                $feeLabel = Mage::helper('buckaroo3extended')->__('Fee');
            }
        } else {
            $feeLabel = Mage::helper('buckaroo3extended')->__('Fee');
        }

        return $feeLabel;
    }

    public function resetBuckarooFeeInvoicedValues($order, $invoice)
    {
        $baseBuckarooFee    = $invoice->getBaseBuckarooFee();
        $paymentFee        = $invoice->getBuckarooFee();
        $baseBuckarooFeeTax = $invoice->getBaseBuckarooFeeTax();
        $paymentFeeTax     = $invoice->getBuckarooFeeTax();

        $baseBuckarooFeeInvoiced    = $order->getBaseBuckarooFeeInvoiced();
        $paymentFeeInvoiced        = $order->getBuckarooFeeInvoiced();
        $baseBuckarooFeeTaxInvoiced = $order->getBaseBuckarooFeeTaxInvoiced();
        $paymentFeeTaxInvoiced     = $order->getBuckarooFeeTaxInvoiced();

        if ($baseBuckarooFeeInvoiced && $baseBuckarooFee && $baseBuckarooFeeInvoiced >= $baseBuckarooFee) {
            $order->setBaseBuckarooFeeInvoiced($baseBuckarooFeeInvoiced - $baseBuckarooFee)
                  ->setBuckarooFeeInvoiced($paymentFeeInvoiced - $paymentFee)
                  ->setBaseBuckarooFeeTaxInvoiced($baseBuckarooFeeTaxInvoiced - $baseBuckarooFeeTax)
                  ->setBaseBuckarooFeeInvoiced($paymentFeeTaxInvoiced - $paymentFeeTax);
            $order->save();
        }
    }

    public function isEnterprise()
    {
        //there are two easy ways of checking if the current Magento edition is enterprise:
        //1. use the getEdition() method (added in 1.12)
        //2. use isModuleEnabled() method
        if (method_exists('Mage', 'getEdition')) {
            $edition = Mage::getEdition();
            if ($edition == 'Enterprise') {
                return true;
            }
        } else {
            return (bool) Mage::helper('core')->isModuleEnabled('Enterprise_Enterprise');
        }

        return false;
    }

    public function getIsKlarnaEnabled()
    {
        return Mage::helper('core')->isModuleEnabled('Klarna_KlarnaPaymentModule');
    }

    public function checkRegionRequired()
    {
        $storeId = Mage::app()->getRequest()->getParam('store');
        $allowSpecific = Mage::getStoreConfig('buckaroo/buckaroo3extended_paypal/allowspecific', $storeId);
        if ($allowSpecific) {
            $allowedCountries = explode(',', Mage::getStoreConfig('buckaroo/buckaroo3extended_paypal/specificcountry', $storeId));
        } else {
            $allowedCountries = Mage::getModel('directory/country')->getResourceCollection()
                                                                   ->loadByStore($storeId)
                                                                   ->toOptionArray(true);
        }

        foreach ($allowedCountries as $country) {
            if (!Mage::helper('directory')->isregionRequired($country)) {
                return false;
            }
        }
        return true;
    }

    public function checkSellersProtection($order)
    {
        if (!Mage::getStoreConfig('buckaroo/buckaroo3extended_paypal/active', $order->getStoreId())) {
            return false;
        }

        if (!Mage::getStoreConfig('buckaroo/buckaroo3extended_paypal/sellers_protection', $order->getStoreId())) {
            return false;
        }

        if ($order->getIsVirtual()) {
            return false;
        }
        return true;
    }
}