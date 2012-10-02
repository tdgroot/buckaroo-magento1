<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_ByjunoAccount_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract
{
    protected $_code   = 'buckaroo3extended_byjunoaccount';
    protected $_method = 'empayment';

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

        $this->_addEmpaymentVars($vars);
        $this->_addPersonVars($vars);
        $this->_addBankAccountVars($vars);
        $this->_addBillingAddressVars($vars);

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

    protected function _addEmpaymentVars(&$vars)
    {        
        $storeId = Mage::app()->getStore()->getId();
        
        $array = array(
            'beneficiaryAccountNumber' => Mage::getStoreConfig('buckaroo/buckaroo3extended_byjunoaccount/account_number', $storeId),
            'reference'                => $this->_order->getIncrementId(),
            'emailAddress'             => Mage::getStoreConfig('buckaroo/buckaroo3extended_byjunoaccount/email_address', $storeId),
        );
        
        if (array_key_exists('customVars', $vars) && is_array($vars['customVars'][$this->_method])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $array);
        } else {
            $vars['customVars'][$this->_method] = $array;
        }
    }

    protected function _addBankAccountVars(&$vars)
    {
        $additionalFields = Mage::getSingleton('checkout/session')->getData('additionalFields');
        
        $array = array(
            'Type'                             => array(
                                                      'value' => 'DOM',
                                                      'group' => 'bankaccount',
                                                  ),
            'DomesticAccountHolderName'        => array(
                                                      'value' => $additionalFields['DOM']['accountHolder'],
                                                      'group' => 'bankaccount',
                                                  ),
            'DomesticCountry'                  => array(
                                                      'value' => 528,
                                                      'group' => 'bankaccount',
                                                  ),
            'DomesticBankIdentifier'           => array(
                                                      'value' => $additionalFields['DOM']['bankId'],
                                                      'group' => 'bankaccount',
                                                  ),
            'DomesticAccountNumber'            => array(
                                                      'value' => $additionalFields['DOM']['accountNumber'],
                                                      'group' => 'bankaccount',
                                                  ),
//            'InternationalAccountHolderName'   => $additionalFields['INT']['accountHolder'],
//            'InternationalBankAddress'         => $additionalFields['INT']['bankAddress'],
//            'InternationalBankName'            => $additionalFields['INT']['bankName'],
//            'InternationalBankSwiftCode'       => $additionalFields['INT']['swiftCode'],
//            'InternationalAccountNumber'       => $additionalFields['INT']['accountNumber'],
//            'InternationalAccountHolderAddres' => $additionalFields['INT']['accountHolderAddress'],
//            'SepaBIC'                          => $additionalFields['SEPA']['BIC'],
//            'SepaIBAN'                         => $additionalFields['SEPA']['IBAN'],
            'Collect'                          => array(
                                                      'value' => 1,
                                                      'group' => 'bankaccount',
                                                  ),
        );
        
        if (array_key_exists('customVars', $vars) && is_array($vars['customVars'][$this->_method])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $array);
        } else {
            $vars['customVars'][$this->_method] = $array;
        }
    }

    protected function _addPersonVars(&$vars)
    {
        $array = array(
            'FirstName'  => array(
                                'value' => $this->_billingInfo['firstname'],
                                'group' => 'person',
                            ),
            'Initials'   => array(
                                'value' => $this->_getInitialsCM(),
                                'group' => 'person',
                            ),
            'LastName'   => array(
                                'value' => $this->_billingInfo['lastname'],
                                'group' => 'person',
                            ),
        );
        
        if (array_key_exists('customVars', $vars) && is_array($vars['customVars'][$this->_method])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $array);
        } else {
            $vars['customVars'][$this->_method] = $array;
        }
    }

    protected function _addBillingAddressVars(&$vars)
    {
        $address = $this->_processAddressCM();
        
        $array = array(
            'Street'  => array(
                                'value' => $address['street'],
                                'group' => 'address',
                            ),
            'AddressType'   => array(
                                'value' => 'HOM',
                                'group' => 'address',
                            ),
            'Country'   => array(
                                'value' => 538,
                                'group' => 'address',
                            ),
            'NumberExtension'  => array(
                                'value' => $address['number_addition'],
                                'group' => 'address',
                            ),
            'City'   => array(
                                'value' => $this->_billingInfo['city'],
                                'group' => 'address',
                            ),
            'Number'   => array(
                                'value' => $address['house_number'],
                                'group' => 'address',
                            ),
            'ZipCode'  => array(
                                'value' => $this->_billingInfo['firstname'],
                                'group' => 'address',
                            ),
        );
        
        if (array_key_exists('customVars', $vars) && is_array($vars['customVars'][$this->_method])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $array);
        } else {
            $vars['customVars'][$this->_method] = $array;
        }
    }
}