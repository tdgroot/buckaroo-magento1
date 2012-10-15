<?php 
class TIG_Buckaroo3Extended_Block_Adminhtml_System_Config_CertificateUploaded 
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'buckaroo3extended/system/config/certificateUploaded.phtml';
    
    public $message;
    public $bannerClass;

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
            $this->bannerClass = 'buckaroo_certificate_is_uploaded_banner';
        } else {
            $this->message = 'You have not yet uploaded a Buckaroo private key certificate. Please use the form below to do so.';
            $this->bannerClass = 'buckaroo_certificate_is_not_uploaded_banner';
        }
    }
}