<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Ideal_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract
{
    protected $_code = 'buckaroo3extended_ideal';
    protected $_method = 'ideal';

    public function buckaroo3extended_request_addservices(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request = $observer->getRequest();

        $vars = $request->getVars();
        
        $array = array(
            'action'	=> 'Pay',
            'version'   => 1,
        );
        
        if (is_array($vars['services'][$this->_method])) {
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

        $issuer = $this->_getIssuer();
        $array = array(
            'issuer' => $issuer,
        );
        if (is_array($vars['customVars'][$this->_method])) {
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

    protected function _getIssuer()
    {
        $additionalFields = Mage::getSingleton('checkout/session')->getData('additionalFields');
        $issuer = $additionalFields['Issuer'];

        switch ($issuer) {
            case 'ABNAMRO':     $issuerCode = '0031';
                                break;
            case 'ASNBANK':     $issuerCode = '0761';
                                break;
            case 'FRIESLAND':   $issuerCode = '0091';
                                break;
            case 'INGBANK':     $issuerCode = '0721';
                                break;
            case 'RABOBANK':    $issuerCode = '0021';
                                break;
            case 'SNSBANK':     $issuerCode = '0751';
                                break;
            case 'SNSREGIO':    $issuerCode = '0771';
                                break;
            case 'TRIODOS':     $issuerCode = '0511';
                                break;
            case 'LANSCHOT':    $issuerCode = '0161';
                                break;
        }

        return $issuerCode;
    }
}