<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_Buckaroo3Extended_Test_Unit_Model_PaymentMethods_Afterpay_ObserverTest extends TIG_Buckaroo3Extended_Test_Framework_TIG_Test_TestCase
{
    /** @var null|TIG_Buckaroo3Extended_Model_PaymentMethods_Afterpay_Observer */
    protected $_instance = null;

    protected function _getInstance()
    {
        if ($this->_instance === null) {
            $this->_instance = $this->getMock(
                'TIG_Buckaroo3Extended_Model_PaymentMethods_Afterpay_Observer',
                array('_loadLastOrder')
            );

            $this->_instance->expects($this->any())
                ->method('_loadLastOrder')
                ->will($this->returnSelf());
        }

        return $this->_instance;
    }

    public function testBuckaroo3extended_request_addservices()
    {
        $this->registerMockSessions(array('core', 'checkout'));

        $mockPayment = $this->getMock('Mage_Sales_Model_Order_Payment', array('getMethod'));
        $mockPayment->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('buckaroo3extended_afterpay'));

        $mockOrder = $this->getMock(
            'Mage_Sales_Model_Order',
            array('getPayment', 'getPaymentMethodUsedForTransaction')
        );
        $mockOrder->expects($this->once())
            ->method('getPayment')
            ->will($this->returnValue($mockPayment));
        $mockOrder->expects($this->any())
            ->method('getPaymentMethodUsedForTransaction')
            ->will($this->returnValue(false));

        $mockRequest = $this->getMock('TIG_Buckaroo3Extended_Model_Request_Abstract', array('getVars'));
        $mockRequest->expects($this->once())
            ->method('getVars')
            ->will($this->returnValue(array()));

        $mockObserver = $this->getMock('Varien_Event_Observer', array('getOrder', 'getRequest'));
        $mockObserver->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($mockOrder));

        $mockObserver->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));


        $instance = $this->_getInstance();
        $result = $instance->buckaroo3extended_request_addservices($mockObserver);

        $this->assertInstanceOf('TIG_Buckaroo3Extended_Model_PaymentMethods_Afterpay_Observer', $result);
    }
}
