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
class TIG_Buckaroo3Extended_Model_PaymentMethods_Klarna_Observer extends TIG_Buckaroo3Extended_Model_Observer_Abstract
{
    /** @var string $_code */
    protected $_code = 'buckaroo3extended_klarna';

    /** @var string $_method */
    protected $_method = 'klarna';

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function buckaroo3extended_capture_request_addservices(Varien_Event_Observer $observer)
    {
        if ($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        /** @var TIG_Buckaroo3Extended_Model_Request_Abstract $request */
        $request = $observer->getRequest();
        $vars = $request->getVars();

        $array = array(
            $this->_method => array(
                'action'   => 'Pay', //Capture
                'version'  => '1',
            ),
        );

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
    public function buckaroo3extended_capture_request_addcustomvars(Varien_Event_Observer $observer)
    {
        if ($this->_isChosenMethod($observer) === false) {
            return $this;
        }

        /** @var TIG_Buckaroo3Extended_Model_Request_Abstract $request */
        $request            = $observer->getRequest();
        $this->_billingInfo = $request->getBillingInfo();
        $this->_order       = $request->getOrder();

        $vars = $request->getVars();

        $this->addCaptureAditionalInfo($vars);

        $request->setVars($vars);

        return $this;
    }

    /**
     * @param $vars
     */
    private function addCaptureAditionalInfo(&$vars)
    {
        $sendInvoiceBy = Mage::getStoreConfig(
            'buckaroo/buckaroo3extended_' . $this->_method . '/send_invoice_by',
            Mage::app()->getStore()->getStoreId()
        );

        $array = array(
            'ReservationNumber' => $this->_order->getBuckarooReservationNumber(),
            'PreserveReservation' => 'false',
            'SendByMail' => (TIG_Buckaroo3Extended_Model_Sources_Klarna_SendInvoiceBy::ACTION_MAIL == $sendInvoiceBy),
            'SendByEmail' => (TIG_Buckaroo3Extended_Model_Sources_Klarna_SendInvoiceBy::ACTION_EMAIL == $sendInvoiceBy),
        );

        /** @var Mage_Sales_Model_Resource_Order_Invoice_Collection $invoiceCollection */
        $invoiceCollection = $this->_order->getInvoiceCollection();

        /** @var Mage_Sales_Model_Order_Invoice $lastInvoice */
        $lastInvoice = $invoiceCollection->getLastItem();

        if ($this->_order->getPayment()->canCapturePartial()
            && count($invoiceCollection) > 0
            && $lastInvoice->getBaseGrandTotal() < $this->_order->getBaseGrandTotal()) {
            $array['PreserveReservation'] = 'true';
        }

        if (array_key_exists('customVars', $vars) && is_array($vars['customVars'][$this->_method])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $array);
        } else {
            $vars['customVars'][$this->_method] = $array;
        }
    }
}
