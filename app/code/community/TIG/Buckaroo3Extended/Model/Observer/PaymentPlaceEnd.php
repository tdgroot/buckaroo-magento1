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
class TIG_Buckaroo3Extended_Model_Observer_PaymentPlaceEnd extends Mage_Core_Model_Abstract
{
    public function updateOrderStatus(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getPayment();

        $methodInstance = $payment->getMethodInstance();
        $paymentAction = $methodInstance->getConfigPaymentAction();
        $paymentCode = substr($methodInstance->getCode(), 0, 18);

        if ($paymentCode != 'buckaroo3extended_' || !$paymentAction) {
            return $this;
        }

        $order = $payment->getOrder();

        $orderState = Mage_Sales_Model_Order::STATE_NEW;
        $orderStatus = $methodInstance->getConfigData('order_status');
        $states = $order->getConfig()->getStatusStates($orderStatus);

        if (!$orderStatus || count($states) == 0) {
            $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
        }

        $order->setData('state', $orderState);
        $order->setStatus($orderStatus);

        $history = $order->getAllStatusHistory();

        /** @var Mage_Sales_Model_Order_Status_History $latestHistory */
        $latestHistory = $history[0];
        $latestHistory->setStatus($orderStatus);
        $latestHistory->save();

        return $this;
    }
}
