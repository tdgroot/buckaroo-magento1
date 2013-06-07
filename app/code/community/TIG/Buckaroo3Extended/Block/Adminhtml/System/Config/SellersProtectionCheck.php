<?php
class TIG_Buckaroo3Extended_Block_Adminhtml_System_Config_SellersProtectionCheck
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'buckaroo3extended/system/config/paypalRegionCheck.phtml';


    
    public function getIsRegionRequired()
    {
        return Mage::helper('buckaroo3extended')->checkRegionRequired();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
}
