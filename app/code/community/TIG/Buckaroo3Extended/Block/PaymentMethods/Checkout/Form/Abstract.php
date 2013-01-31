<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Checkout_Form_Abstract extends Mage_Payment_Block_Form
{
    public function getMethodLabelAfterHtml()
    {
        $code = $this->getMethod()->getCode();
        
        $fee = Mage::getStoreConfig('buckaroo/' . $code . '/payment_fee', mage::app()->getStore()->getId());
        if (!$fee) {
            return '';
        }
        
        if (strpos($fee, '%') === false) {
            $fee = Mage::helper('core')->currency($fee, true, false);
        }
        
        $feeText = '<span class="buckaroo_fee '
                 . $code
                 . '">'
                 . Mage::helper('buckaroo3extended')->__('%s fee', $fee)
                 . '</span>';

        return $feeText;
    }
}
