<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Payperemail_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract 
{    
    protected $_code = 'buckaroo3extended_payperemail';
    protected $_method = 'payperemail';
    
    public function buckaroo3extended_request_addservices(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }
        
        $request = $observer->getRequest();
        
        $vars = $request->getVars();
        
        $array = array(
            'action'	=> 'PaymentInvitation',
            'version'   => 1,
        );
        
        if (array_key_exists('services', $vars) && is_array($vars['services'][$this->_method])) {
            $vars['services'][$this->_method] = array_merge($vars['services'][$this->_method], $array);
        } else {
            $vars['services'][$this->_method] = $array;
        }
        
        $request->setVars($vars);
        
        return $this;
    }
    
    public function buckaroo3extended_request_addcustomvars(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }
        
        $request            = $observer->getRequest();
        $this->_billingInfo = $request->getBillingInfo();
        $this->_order       = $request->getOrder();
        
        $vars = $request->getVars();
        $additionalFields = Mage::getSingleton('checkout/session')->getData('additionalFields');
        
        $array = array(
            'MerchantSendsEmail'    => Mage::getStoreConfig('buckaroo/buckaroo3extended_payperemail/send_mail', Mage::app()->getStore()->getStoreId()) ? 'false' : 'true',
            'customergender'        => $additionalFields['gender'],
            'PaymentMethodsAllowed' => $this->_getPaymentMethodsAllowed(),
            'CustomerEmail'         => $additionalFields['mail'],
            'CustomerFirstName'     => $additionalFields['firstname'],
            'CustomerLastName'      => $additionalFields['lastname'],
        );
        
        if (array_key_exists('customVars', $vars) && is_array($vars['customVars'][$this->_method])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $array);
        } else {
            $vars['customVars'][$this->_method] = $array;
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