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
        
        $vars['services'][$this->_method] = array(
            'action'	=> 'Pay',
            'version'   => 1,
        );
        
        $vars['services']['creditmanagement'] = array(
            'action'	=> 'Invoice',
            'version'   => 1,
        );
        
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
        
        $this->_addCustomerVariables(&$vars, 'creditmanagement');
        $this->_addCreditManagement(&$vars);
        
        $vars['customVars']['transfer'] = array(
            'SendMail'          => Mage::getStoreConfig('buckaroo/buckaroo3extended_transfer/send_mail', Mage::app()->getStore()->getStoreId()) ? 'true' : 'false',
            'customeremail'     => $this->_billingInfo['email'],
            'customercountry'   => $this->_billingInfo['countryCode'],
            'customergender'    => '0',
            'customerFirstName' => $this->_billingInfo['firstname'],
            'customerLastName'  => $this->_billingInfo['lastname'],
        );
        
        $VAT = 0;
        foreach($this->_order->getFullTaxInfo() as $taxRecord)
        {
            $VAT += $taxRecord['amount'];
        }
        $VAT = round($VAT * 100,0);
        
        $dueDays = Mage::getStoreConfig('buckaroo/buckaroo3extended_transfer/due_date', Mage::app()->getStore()->getStoreId());
        $dueDate = date('Y-m-d', mktime(0, 0, 0, date("m")  , (date("d") + $dueDays), date("Y")));
        
        $vars['customVars']['transfer']['DateDue']                  = $dueDate;
        
        $vars['customVars']['creditmanagement']['AmountVat']        = $VAT;
        $vars['customVars']['creditmanagement']['CustomerType']     = 1;
        $vars['customVars']['creditmanagement']['MaxReminderLevel'] = 4;
        
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