<?php

class TIG_Buckaroo3Extended_Model_Certificate_Certificate extends Mage_Core_Model_Abstract
{
    /**
     * Uploads the certificate file.
     * 
     * @param Varien_Object $object
     */
    public function uploadAndImport(Varien_Object $object)
    {   
        if (
            isset($_FILES['groups']['name']['buckaroo3extended']['fields']['certificate_upload']['value'])
            && !empty($_FILES['groups']['name']['buckaroo3extended']['fields']['certificate_upload']['value'])
        ) {
            try {
                //ugly hack to allow varien_file_uploader to work
                $_FILES['certificate']['name']     = $_FILES['groups']['name']['buckaroo3extended']['fields']['certificate_upload']['value'];
                $_FILES['certificate']['type']     = $_FILES['groups']['type']['buckaroo3extended']['fields']['certificate_upload']['value'];
                $_FILES['certificate']['tmp_name'] = $_FILES['groups']['tmp_name']['buckaroo3extended']['fields']['certificate_upload']['value'];
                $_FILES['certificate']['error']    = $_FILES['groups']['error']['buckaroo3extended']['fields']['certificate_upload']['value'];
                $_FILES['certificate']['size']     = $_FILES['groups']['size']['buckaroo3extended']['fields']['certificate_upload']['value'];

                $uploader = new Varien_File_Uploader('certificate');

                $path = str_replace(
                	DS 
                	. 'Model' 
                	. DS 
                	. 'Certificate', 
                	DS 
                	. 'certificate', 
                	dirname(__FILE__)
                );
                if (strpos(dirname(__FILE__), DS . 'Model' . DS . 'Certificate') !== false) {
        	        $path = str_replace(DS . 'Model' . DS . 'Certificate', DS . 'certificate', dirname(__FILE__));
        	    } else {
        	        $path = str_replace(
        	        	DS 
        	        	. 'includes' 
        	        	. DS 
        	        	. 'src', 
        	        	DS 
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
        	        	. 'certificate', 
        	        	dirname(__FILE__)
        	        );
        	    }

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