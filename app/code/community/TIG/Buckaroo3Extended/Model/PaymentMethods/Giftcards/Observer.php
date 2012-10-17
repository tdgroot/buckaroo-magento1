<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Giftcards_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract 
{    
    protected $_code = 'buckaroo3extended_giftcards';
    protected $_method = 'giftcards';
    
    public function buckaroo3extended_request_addservices(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }
        
        $request = $observer->getRequest();
        
        $vars = $request->getVars();
        
        $array = array();
        if (Mage::getStoreConfig('buckaroo/buckaroo3extended_' .  $this->_method . '/use_creditmanagement', Mage::app()->getStore()->getStoreId())) {
            $array['creditmanagement'] = array(
                    'action'    => 'Invoice',
                    'version'   => 1,
            );
        }
        
        if (array_key_exists('services', $vars) && is_array($vars['services'])) {
            $vars['services'] = array_merge($vars['services'], $array);
        } else {
            $vars['services'] = $array;
        }

        $request->setVars($vars);
        
        return $this;
    }
    
    public function buckaroo3extended_request_addcustomvars(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request = $observer->getRequest();
        $vars = $request->getVars();

        if (Mage::getStoreConfig('buckaroo/buckaroo3extended_' . $this->_method . '/use_creditmanagement', Mage::app()->getStore()->getStoreId())) {
            $this->_addCustomerVariables($vars);
            $this->_addCreditManagement($vars);
            $this->_addAdditionalCreditManagementVariables($vars);
        }
        
        $availableCards = Mage::getStoreConfig('buckaroo/buckaroo3extended_giftcards/cards_allowed', Mage::app()->getStore()->getId());
        if (empty($availableCards)) {
            Mage::throwException('no giftcards available');
        }
        $availableCards .= ',ideal';
        
        $array = array(
        		'servicesSelectableByClient' => $availableCards,
        		'continueOnImcomplete'       => 'RedirectToHTML',
        );
        
        if (array_key_exists('customVars', $vars)) {
            $vars['customVars'] = array_merge($vars['customVars'], $array);
        } else {
            $vars['customVars'] = $array;
        }
        
        $request->setVars($vars);
        
        return $this;
    }
    
    public function buckaroo3extended_request_setmethod(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }
        
        $request = $observer->getRequest();
        
        $codeBits = explode('_', $this->_code);
        $code = end($codeBits);
        $request->setMethod($code);
        
        return $this;
    }
    
    protected function _isChosenMethod($observer)
    {
        $ret = false;
        
        $chosenMethod = $observer->getOrder()->getPayment()->getMethod();
        
        if ($chosenMethod === $this->_code) {
            $ret = true;
        }
        return $ret;
    }
}