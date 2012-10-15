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
        
        $vars['services'][$this->_method] = false;
        
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
        
        $availableCards = Mage::getStoreConfig('buckaroo/buckaroo3extended_giftcards/cards_allowed', Mage::app()->getStore()->getId());
        
        $array = array(
        		'servicesSelectableByClient' => $availableCards,
        		'continueOnImcomplete'       => 'RedirectToHTML',
        );
        
        if (
        	array_key_exists('customVars', $vars)
        	&& array_key_exists($this->_method, $vars['customVars']) 
        	&& is_array($vars['customVars'][$this->_method])
        ) {
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