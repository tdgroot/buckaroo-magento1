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
        if (method_exists('Mage', 'getEdition')) {
            $edition = Mage::getEdition();
            if ($edition != 'Enterprise') {
                return false;
            }
        } else {
            return (bool) Mage::getConfig()->getModuleConfig("Enterprise_Enterprise")->version;
        }
    }

    public function getIsKlarnaEnabled()
    {
        return Mage::helper('core')->isModuleEnabled('Klarna_KlarnaPaymentModule');
    }


    public function checkRegionRequired()
    {
        if (Mage::getStoreConfig('buckaroo/' . $this->_code . '/allowspecific', Mage::app()->getStore()->getStoreId()) == 1) {
            $allowedCountries = explode(',',Mage::getStoreConfig('buckaroo/' . $this->_code . '/specificcountry', Mage::app()->getStore()->getStoreId()));
        }
        $requiredCountries = Mage::helper('directory')->getCountriesWithStatesRequired();

        return $requiredCountries;
    }
    
    public function checkSellersProtection()
    {
        if (Mage::getStoreConfig('buckaroo/buckaroo3extended_' .  $this->_method . '/sellers_protection', Mage::app()->getStore()->getStoreId())) {
            $returnArray = $array['sellersprotection'] = array(
                    'name' => 'paypal',
                    'action' => 'extraInfo',
                    'version' => 1,
           );
        }
        return $returnArray;
    }
    
    public function check()
    {
        $array = array(
            'Name'              =>  $this->_billingInfo['lastname'],
            'Street1'           =>  $address['street'],
            'CityName'          =>  $this->_billingInfo['city'],
            'StateOrProvince'   =>  'test value',//$this->_billingInfo['state'],
            'PostalCode'        =>  $this->_billingInfo['zip'],
            'Country'           =>  $this->_billingInfo['countryCode'],
            'AddressOverride'   =>  'TRUE'
         );

        if (array_key_exists('customVars', $vars) && array_key_exists('sellersprotection', $vars['customVars']) && is_array($vars['customVars']['sellersprotection'])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $array);
        } else {
            $vars['customVars'][$this->_method] = $array;
        }
        
    }
}