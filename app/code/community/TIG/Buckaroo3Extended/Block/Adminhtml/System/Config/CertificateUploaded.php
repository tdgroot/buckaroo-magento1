<?php 
class TIG_Buckaroo3Extended_Block_Adminhtml_System_Config_CertificateUploaded 
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'buckaroo3extended/system/config/certificateUploaded.phtml';
    
    public $message;
    public $icon;

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
    
    public function isUploaded()
    {
        $certificate = Mage::getBaseDir() 
                     . DS 
                     . 'app' 
                     . DS 
                     . 'code' 
                     . DS 
                     . 'community' 
                     . DS 
                     . 'TIG' 
                     . DS 
                     . 'Buckaroo3Extended' 
                     . DS 
                     . 'certificate' 
                     . DS
                     . '/BuckarooPrivateKey.pem';
        
        if (file_exists($certificate)) {
            $this->message = 'You have succesfully uploaded your Buckaroo private key certificate! You can use the form below to upload a replacement.';
            $this->icon = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) 
                        . 'adminhtml/default/default/images/fam_bullet_success.gif';
        } else {
            $this->message = 'You have not yet uploaded a Buckaroo private key certificate. Please use the form below to do so.';
            $this->icon = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) 
                        . 'adminhtml/default/default/images/error_msg_icon.gif';
        }
    }
}