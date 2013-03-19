<?php
class TIG_Buckaroo3Extended_Block_PaymentFee_Order_Totals_Tax extends Mage_Adminhtml_Block_Sales_Order_Totals_Tax
{
    /**
     * Get full information about taxes applied to order
     *
     * @return array
     */
    public function getFullTaxInfo()
    {
        if(Mage::helper('buckaroo3extended')->getIsKlarnaEnabled()) {
            return parent::getFullTaxInfo();
        }
        
        /** @var $source Mage_Sales_Model_Order */
        $source = $this->getOrder();

        if ($source instanceof Mage_Sales_Model_Order) {
            if (method_exists(Mage::helper('tax'), 'getCalculatedTaxes')) {
                return $this->_addBuckarooTaxInfo($source);
            } else {
                return $this->_addAlternativeBuckarooTaxInfo($source);
            }
        }
    }

    protected function _addBuckarooTaxInfo($source)
    {
        $taxClassAmount = array();
        $taxClassAmount = Mage::helper('tax')->getCalculatedTaxes($source);
        if (empty($taxClassAmount)) {
            $rates = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($source)->toArray();
            $taxClassAmount =  Mage::getSingleton('tax/calculation')->reproduceProcess($rates['items']);
        } else {
            $shippingTax    = Mage::helper('tax')->getShippingTax($source);
            if ($source->getBaseBuckarooFeeTax()) {
                $buckarooFeeTax = array(
                    array(
                        'tax_amount'      => $source->getBuckarooFeeTax(),
                        'base_tax_amount' => $source->getBaseBuckarooFeeTax(),
                        'title'           => Mage::helper('buckaroo3extended')->__('Fee'),
                        'percent'         => NULL,
                    ),
                );
                $taxClassAmount = array_merge($shippingTax, $buckarooFeeTax, $taxClassAmount);
            } else {
                $taxClassAmount = array_merge($shippingTax, $taxClassAmount);
            }
        }

        return $taxClassAmount;
    }
    
    protected function _addAlternativeBuckarootaxInfo($source)
    {
        $rates = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($source)->toArray();
        $info  = Mage::getSingleton('tax/calculation')->reproduceProcess($rates['items']);

        /**
         * Set right tax amount from invoice
         * (In $info tax invalid when invoice is partial)
         */
        /** @var $blockInvoice Mage_Adminhtml_Block_Sales_Order_Invoice_Totals */
        $blockInvoice = $this->getLayout()->getBlock('tax');
        /** @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $blockInvoice->getSource();
        $items = $invoice->getItemsCollection();
        
        $i = 0;
        /** @var $item Mage_Sales_Model_Order_Invoice_Item */
        foreach ($items as $item) {
            $info[$i]['hidden']           = $item->getHiddenTaxAmount();
            $info[$i]['amount']           = $item->getTaxAmount();
            $info[$i]['base_amount']      = $item->getBaseTaxAmount();
            $info[$i]['base_real_amount'] = $item->getBaseTaxAmount();
            $i++;
        }
        
        if ($invoice->getBuckarooFeeTax()) {
            $info[] = array(
                'hidden' => 0,
                'amount' => $invoice->getBuckarooFeeTax(),
                'base_amount' => $invoice->getBaseBuckarooFeeTax(),
                'base_real_amount' => $invoice->getBaseBuckarooFeeTax()
            );
        }
        
        return $info;
    }
}
