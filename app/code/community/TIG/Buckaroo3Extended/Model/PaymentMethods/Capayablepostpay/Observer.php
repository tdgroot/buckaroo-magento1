<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_Buckaroo3Extended_Model_PaymentMethods_Capayablepostpay_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract
{
    protected $_code = 'buckaroo3extended_capayablepostpay';
    protected $_method = 'Capayable';

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function buckaroo3extended_request_addservices(Varien_Event_Observer $observer)
    {
        if ($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request = $observer->getRequest();
        $vars = $request->getVars();
        $serviceVersion = $this->_getServiceVersion();

        $array = array($this->_method => array('action'  => 'Pay', 'version' => $serviceVersion));

        if (array_key_exists('services', $vars) && is_array($vars['services'])) {
            $vars['services'] = array_merge($vars['services'], $array);
        } else {
            $vars['services'] = $array;
        }

        $request->setVars($vars);

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function buckaroo3extended_request_addcustomvars(Varien_Event_Observer $observer)
    {
        if ($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request            = $observer->getRequest();
        $this->_billingInfo = $request->getBillingInfo();
        $this->_order       = $request->getOrder();

        $vars = $request->getVars();

        $this->addCustomerData($vars);

        $request->setVars($vars);

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function buckaroo3extended_request_setmethod(Varien_Event_Observer $observer)
    {
        if ($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        $request = $observer->getRequest();

        $codeBits = explode('_', $this->_code);
        $code = end($codeBits);
        $request->setMethod($code);

        return $this;
    }

    /**
     * @param $vars
     */
    private function addCustomerData(&$vars)
    {
        $country = $this->_billingInfo['countryCode'];
        $phoneNumber = ($country == 'BE' ? $this->_processPhoneNumberCMBe() : $this->_processPhoneNumberCM());

        $array = array(
            'CustomerType' => $this->getCustomerType(),
            'InvoiceDate' => date('d-m-Y'),
            'Phone' => array(
                'value' => $phoneNumber['clean'],
                'group' => 'Phone'
            ),
            'Email' => array(
                'value' => $this->_billingInfo['email'],
                'group' => 'Email'
            )
        );

        $array = array_merge($array, $this->getPersonGroupData());
        $array = array_merge($array, $this->getAddressGroupData());

        if (array_key_exists('customVars', $vars)
            && array_key_exists($this->_method, $vars['customVars'])
            && is_array($vars['customVars'][$this->_method])
        ) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $array);
        } else {
            $vars['customVars'][$this->_method] = $array;
        }
    }

    /**
     * @return array
     */
    private function getPersonGroupData()
    {
        $additionalFields = Mage::getSingleton('checkout/session')->getData('additionalFields');
        $gender = 0;
        $birthdate = '';

        if (Mage::helper('buckaroo3extended')->isAdmin()) {
            $additionalFields = Mage::getSingleton('core/session')->getData('additionalFields');
        }

        if (isset($additionalFields['BPE_Customergender'])) {
            $gender = $additionalFields['BPE_Customergender'];
        }

        if (isset($additionalFields['BPE_Customerbirthdate'])) {
            $birthdate = $additionalFields['BPE_Customerbirthdate'];
        }

        $array = array(
            'Initials' => array(
                'value' => $this->_getInitialsCM(),
                'group' => 'Person'
            ),
            'LastName' => array(
                'value' => $this->_billingInfo['lastname'],
                'group' => 'Person'
            ),
            'Culture' => array(
                'value' => 'nl-NL',
                'group' => 'Person'
            ),
            'Gender' => array(
                'value' => $gender,
                'group' => 'Person'
            ),
            'BirthDate' => array(
                'value' => $birthdate,
                'group' => 'Person'
            )
        );

        return $array;
    }

    /**
     * @return array
     */
    private function getAddressGroupData()
    {
        $address = $this->_processAddressCM();
        $country = $this->_billingInfo['countryCode'];

        $array = array(
            'Street' => array(
                'value' => $address['street'],
                'group' => 'Address'
            ),
            'HouseNumber' => array(
                'value' => $address['house_number'],
                'group' => 'Address'
            ),
            'HouseNumberSuffix' => array(
                'value' => $address['number_addition'],
                'group' => 'Address'
            ),
            'ZipCode' => array(
                'value' => $this->_billingInfo['zip'],
                'group' => 'Address'
            ),
            'City' => array(
                'value' => $this->_billingInfo['city'],
                'group' => 'Address'
            ),
            'Country' => array(
                'value' => $country,
                'group' => 'Address'
            )
        );

        return $array;
    }

    /**
     * @return string
     */
    private function getCustomerType()
    {
        $customerType = '';
        $additionalFields = Mage::getSingleton('checkout/session')->getData('additionalFields');

        if (Mage::helper('buckaroo3extended')->isAdmin()) {
            $additionalFields = Mage::getSingleton('core/session')->getData('additionalFields');
        }

        if (!isset($additionalFields['BPE_OrderAs']) || empty($additionalFields['BPE_OrderAs'])) {
            return $customerType;
        }

        switch ($additionalFields['BPE_OrderAs']) {
            case 1:
                $customerType = 'Debtor';
                break;
            case 2:
                $customerType = 'Company';
                break;
            case 3:
                $customerType = 'SoleProprietor';
                break;
        }

        return $customerType;
    }
}
