<?php 

class TIG_Buckaroo3Extended_Model_Certificate_Certificate extends TIG_Buckaroo3Extended_Model_Abstract
{
    public function __construct()
    {
        
    }
    
    public function uploadAndImport(Varien_Object $object)
    {
        if (
            isset($_FILES['groups']['name']['buckaroo3extended']['fields']['certififcate_upload_button']['value']) 
            && !empty($_FILES['groups']['name']['buckaroo3extended']['fields']['certififcate_upload_button']['value'])
        ) {
            try {
                //ugly hack to allow varien_file_uploader to work
                $_FILES['certifiate']['name']     = $_FILES['groups']['name']['buckaroo3extended']['fields']['certififcate_upload_button']['value'];
                $_FILES['certifiate']['type']     = $_FILES['groups']['type']['buckaroo3extended']['fields']['certififcate_upload_button']['value'];
                $_FILES['certifiate']['tmp_name'] = $_FILES['groups']['tmp_name']['buckaroo3extended']['fields']['certififcate_upload_button']['value'];
                $_FILES['certifiate']['error']    = $_FILES['groups']['error']['buckaroo3extended']['fields']['certififcate_upload_button']['value'];
                $_FILES['certifiate']['size']     = $_FILES['groups']['size']['buckaroo3extended']['fields']['certififcate_upload_button']['value'];
                
                $uploader = new Varien_File_Uploader('certifiate');
              
                $path = str_replace('/Model/Certificate', '/certificate', __DIR__);
                
                $certName = 'BuckarooPrivateKey.pem';
                
                $uploader->setAllowedExtensions(
                    array('pem')
                );
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $uploader->save($path, $certName);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                
                return $object;
            }  
        }
        
        return $object;
    }
}