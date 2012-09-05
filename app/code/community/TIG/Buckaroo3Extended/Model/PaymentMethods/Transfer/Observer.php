<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Transfer_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract
{
    protected $_code = 'buckaroo3extended_transfer';
    protected $_method = 'transfer';

    public function buckaroo3extended_request_addservices(Varien_Event_Observer $observer)
    {
        if($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request = $observer->getRequest();

        $vars = $request->getVars();
        
        $array = array(
            $this->_method     => array(
                'action'	=> 'Pay',
                'version'   => 1,
            ),
        );
        
        if (Mage::getStoreConfig('buckaroo/buckaroo3extended_transfer/use_creditmanagement', Mage::app()->getStore()->getStoreId())) {
            $array['creditmanagement'] = array(
                'action'	=> 'Invoice',
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

        $request            = $observer->getRequest();
        $this->_billingInfo = $request->getBillingInfo();
        $this->_order       = $request->getOrder();

        $vars = $request->getVars();

        $this->_addCustomerVariables($vars, 'creditmanagement');
        $this->_addTransfer($vars);
        
        if (Mage::getStoreConfig('buckaroo/buckaroo3extended_transfer/use_creditmanagement', Mage::app()->getStore()->getStoreId())) {
            $this->_addCreditManagement($vars);
            $this->_addTransferCreditmanagement($vars);
        }
        
        $request->setVars($vars);

        return $this;
    }
    
    protected function _addTransfer(&$vars)
    {
        $array = array(
            'SendMail'          => Mage::getStoreConfig('buckaroo/buckaroo3extended_transfer/send_mail', Mage::app()->getStore()->getStoreId()) ? 'true' : 'false',
            'customeremail'     => $this->_billingInfo['email'],
            'customercountry'   => $this->_billingInfo['countryCode'],
            'customergender'    => '0',
            'customerFirstName' => $this->_billingInfo['firstname'],
            'customerLastName'  => $this->_billingInfo['lastname'],
        );
        if (array_key_exists('customVars', $vars) && is_array($vars['customVars']['transfer'])) {
            $vars['customVars']['transfer'] = array_merge($vars['customVars']['transfer'], $array);
        } else {
            $vars['customVars']['transfer'] = $array;
        }
    }
    
    protected function _addTransferCreditmanagement(&$vars)
    {
        $VAT = 0;
        foreach($this->_order->getFullTaxInfo() as $taxRecord)
        {
            $VAT += $taxRecord['amount'];
        }

        $creditmanagementArray = array(
            'AmountVat'        => $VAT,
            'CustomerType'     => 1,
            'MaxReminderLevel' => 4,
        );

        if (array_key_exists('customVars', $vars) && is_array($vars['customVars']['creditmanagement'])) {
            $vars['customVars']['creditmanagement'] = array_merge($vars['customVars']['creditmanagement'], $creditmanagementArray);
        } else {
            $vars['customVars']['creditmanagement'] = $creditmanagementArray;
        }
        
        if (empty($vars['customVars']['creditmanagement']['PhoneNumber'])) {
            $vars['customVars']['creditmanagement']['PhoneNumber'] = $vars['customVars']['creditmanagement']['MobilePhoneNumber'];
        }
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