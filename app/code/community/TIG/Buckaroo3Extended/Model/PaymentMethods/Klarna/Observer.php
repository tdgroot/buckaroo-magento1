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
        $this->addPartialArticlesVariables($vars);

        $request->setVars($vars);

        return $this;
    }

    /**
     * @param $vars
     */
    private function addCaptureAditionalInfo(&$vars)
    {
        $array = array(
            'ReservationNumber' => $this->_order->getBuckarooReservationNumber(),
            'PreserveReservation' => 'false',
            'SendByMail' => 'false',
            'SendByEmail' => 'false',
        );

        $sendInvoiceBy = Mage::getStoreConfig(
            'buckaroo/buckaroo3extended_' . $this->_method . '/send_invoice_by',
            Mage::app()->getStore()->getStoreId()
        );
        $sendInvoiceBy = ucfirst($sendInvoiceBy);
        $array['SendBy' . $sendInvoiceBy] = 'true';

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

        // Pay Webservice doesn't support OriginalTransactionKey
        unset($vars['OriginalTransactionKey']);
    }

    /**
     * @param $vars
     */
    private function addPartialArticlesVariables(&$vars)
    {
        /** @var Mage_Sales_Model_Resource_Order_Invoice_Collection $invoiceCollection */
        $invoiceCollection = $this->_order->getInvoiceCollection();

        $products = $invoiceCollection->getLastItem()->getAllItems();
        $max      = 99;
        $i        = 1;
        $group    = array();

        /** @var Mage_Sales_Model_Order_Invoice_Item $item */
        foreach ($products as $item) {
            if (empty($item) || ($item->getOrderItem() && $item->getOrderItem()->getParentItem())) {
                continue;
            }

            $article['ArticleNumber']['value']   = $item->getOrderItemId();
            $article['ArticleQuantity']['value'] = round($item->getQty(), 0);

            $group[$i] = $article;
            $i++;

            if ($i > $max) {
                break;
            }
        }

        if (Mage::helper('buckaroo3extended')->isEnterprise() && count($invoiceCollection) == 1) {
            $gwId = 1;

            if ($this->_order->getGwBasePrice() > 0) {
                $gwOrder = array();
                $gwOrder['ArticleNumber']['value']   = 'gwo_' . $this->_order->getGwId();
                $gwOrder['ArticleQuantity']['value'] = 1;

                $group[] = $gwOrder;

                $gwId += $this->_order->getGwId();
            }

            if ($this->_order->getGwItemsBasePrice() > 0) {
                $gwiOrder = array();
                $gwiOrder['ArticleNumber']['value']   = 'gwi_' . $gwId;
                $gwiOrder['ArticleQuantity']['value'] = 1;

                $group[] = $gwiOrder;
            }
        }

        end($group);
        $key             = (int)key($group);
        $feeGroupId      = $key + 1;
        $paymentFeeArray = $this->getPaymentFeeLine();

        if (false !== $paymentFeeArray && is_array($paymentFeeArray) && count($invoiceCollection) == 1) {
            unset($paymentFeeArray['ArticlePrice']);
            unset($paymentFeeArray['ArticleTitle']);
            unset($paymentFeeArray['ArticleVat']);
            $group[$feeGroupId] = $paymentFeeArray;
        }

        $shipmentCostsGroupId = $feeGroupId + 1;
        $shipmentCostsArray = $this->getShipmentCostsLine();

        if (false !== $shipmentCostsArray && is_array($shipmentCostsArray) && count($invoiceCollection) == 1) {
            unset($shipmentCostsArray['ArticlePrice']);
            unset($shipmentCostsArray['ArticleTitle']);
            unset($shipmentCostsArray['ArticleVat']);
            $group[$shipmentCostsGroupId] = $shipmentCostsArray;
        }

        $requestArray = array('Articles' => $group);

        if (array_key_exists('customVars', $vars) && is_array($vars['customVars'][$this->_method])) {
            $vars['customVars'][$this->_method] = array_merge($vars['customVars'][$this->_method], $requestArray);
        } else {
            $vars['customVars'][$this->_method] = $requestArray;
        }
    }

    /**
     * @return bool|array
     */
    private function getPaymentFeeLine()
    {
        $fee    = (double) $this->_order->getBuckarooFee();
        $feeTax = (double) $this->_order->getBuckarooFeeTax();
        if ($fee > 0) {
            $article['ArticleNumber']['value']   = 1;
            $article['ArticlePrice']['value']    = round($fee + $feeTax, 2);
            $article['ArticleQuantity']['value'] = 1;
            $article['ArticleTitle']['value']    = 'Servicekosten';
            $article['ArticleVat']['value']      = 0.00;
            return $article;
        }
        return false;
    }
    /**
     * @return bool
     */
    private function getShipmentCostsLine()
    {
        $shippingCosts = round($this->_order->getBaseShippingInclTax(), 2);
        if ($shippingCosts > 0) {
            $article['ArticleNumber']['value']   = 2;
            $article['ArticlePrice']['value']    = $shippingCosts;
            $article['ArticleQuantity']['value'] = 1;
            $article['ArticleTitle']['value']    = 'Verzendkosten';
            $article['ArticleVat']['value']      = 0.00;
            return $article;
        }
        return false;
    }
}
