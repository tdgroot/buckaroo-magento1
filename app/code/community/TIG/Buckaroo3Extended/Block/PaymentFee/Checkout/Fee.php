<?php
class TIG_Buckaroo3Extended_Block_PaymentFee_Checkout_Fee extends Mage_Checkout_Block_Total_Default
{
    protected $_template = 'buckaroo3extended/paymentFee/checkout/fee.phtml';

    /**
     * Get COD fee exclude tax
     *
     * @return float
     */
    public function getBuckarooFee()
    {
        $buckarooFee = 0;
        foreach ($this->getTotal()->getAddress()->getQuote()->getAllShippingAddresses() as $address){
            $buckarooFee += $address->getBuckarooFee();
        }
        return $buckarooFee;
    }
    
    public function getBuckarooFeeTax()
    {
        $buckarooFeeTax = 0;
        foreach ($this->getTotal()->getAddress()->getQuote()->getAllShippingAddresses() as $address){
            $buckarooFeeTax += $address->getBuckarooFeeTax();
        }
        return $buckarooFeeTax;
    }
    
    public function getFeeDisplaySetting()
    {
        $display = (int) Mage::getStoreConfig('tax/cart_display/subtotal', Mage::app()->getStore()->getId());
        return $display;
    }
}
