<?php
class TIG_Buckaroo3Extended_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    protected $_termsAndConditions = <<<TERMS_AND_CONDITIONS
    test content
TERMS_AND_CONDITIONS;

    protected $_informationRequirement = <<<INFORMATION_REQUIREMENT
    test content
INFORMATION_REQUIREMENT;

    public function getTermsAndConditions()
    {
        return $this->_termsAndConditions;
    }
    
    public function setTermsAndConditions($termsAndConditions)
    {
        $this->_termsAndConditions = $termsAndConditions;
        return $this;
    }

    public function getInformationRequirement()
    {
        return $this->_informationRequirement;
    }
    
    public function setInformationRequirement($informationRequirement)
    {
        $this->_informationRequirement = $informationRequirement;
        return $this;
    }
    
    public function installTermsAndConditions()
    {
        $currentStore = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        
        $staticBlock = Mage::getModel('cms/block');
        $intrumTermsAndConditions = $staticBlock->load('buckaroo_intrum_terms_and_conditions');
        if ($intrumTermsAndConditions->getId()) {
            return $this;
        }
        
        $parameters = array(
            'title'      => 'Buckaroo Algemene Voorwaarden',
            'identifier' => 'buckaroo_intrum_terms_and_conditions',
            'content'    => $this->getTermsAndConditions(),
            'is_active'  => 1,
            'stores'     => array(0),
        );
        
        $intrumTermsAndConditions->setData($parameters)->save();
        Mage::app()->setCurrentStore($currentStore);
        return $this;
    }
    
    public function installInformationRequirement()
    {
        $currentStore = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        
        $staticBlock = Mage::getModel('cms/block');
        $informationRequirement = $staticBlock->load('buckaroo_information_requirement');
        if ($informationRequirement->getId()) {
            return $this;
        }
        
        $parameters = array(
            'title'      => 'Buckaroo Informatieplicht',
            'identifier' => 'buckaroo_information_requirement',
            'content'    => $this->getInformationRequirement(),
            'is_active'  => 1,
            'stores'     => array(0),
        );
        
        $informationRequirement->setData($parameters)->save();
        Mage::app()->setCurrentStore($currentStore);
        return $this;
    }
}