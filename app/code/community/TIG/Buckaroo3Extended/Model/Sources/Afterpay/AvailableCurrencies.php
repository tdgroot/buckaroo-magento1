<?php
class TIG_Buckaroo3Extended_Model_Sources_Afterpay_AvailableCurrencies
{
    public function toOptionArray()
    {
        $paymentModel = Mage::getModel('buckaroo3extended/paymentMethods_afterpay_paymentMethod');
        $allowedCurrencies = $paymentModel->getAllowedCurrencies();
        
        $array = array();
        foreach ($allowedCurrencies as $allowedCurrency) {
            $array[] = array('value' => $allowedCurrency, 'label' => $allowedCurrency);
        }
        
    	return $array;
    }
}