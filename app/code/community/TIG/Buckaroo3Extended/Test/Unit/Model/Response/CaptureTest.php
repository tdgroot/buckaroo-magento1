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
class TIG_Buckaroo3Extended_Test_Unit_Model_Response_CaptureTest extends TIG_Buckaroo3Extended_Test_Framework_TIG_Test_TestCase
{
    /** @var null|TIG_Buckaroo3Extended_Model_Response_Capture */
    protected $_instance = null;

    protected function _getInstance()
    {
        if ($this->_instance === null) {
            $params = array(
                'payment' => $this->_getMockPayment(),
                'debugEmail' => '',
                'response' => $this->_getResponseData(),
                'XML' => false
            );

            $this->_instance = $this->getMock(
                'TIG_Buckaroo3Extended_Model_Response_Capture',
                array('_verifySignature', '_verifyDigest', '_verifyResponse'),
                array($params)
            );

            $this->_instance->expects($this->any())
                ->method('_verifySignature')
                ->will($this->returnValue(true));
            $this->_instance->expects($this->any())
                ->method('_verifyDigest')
                ->will($this->returnValue(true));
        }

        return $this->_instance;
    }

    protected function _getResponseData()
    {
        $responseData = [
            'Status' => [
                'Code' => [
                    'Code' => '190'
                ]
            ]
        ];

        $responseDataObject = json_decode(json_encode($responseData));
        return $responseDataObject;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMockPayment()
    {
        $mockOrderAddress = $this->getMock('Mage_Sales_Model_Order_Address');

        $mockOrder = $this->getMock(
            'Mage_Sales_Model_Order',
            array('getBillingAddress', 'getPayment')
        );
        $mockOrder->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue($mockOrderAddress));

        $mockPayment = $this->getMock(
            'Mage_Sales_Model_Order_Payment',
            array('getOrder', 'getMethod')
        );
        $mockPayment->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($mockOrder));
        $mockPayment->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('buckaroo3extended_afterpay'));

        $mockOrder->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($mockPayment));

        return $mockPayment;
    }

    public function testProcessResponse()
    {
        $this->markTestIncomplete('Figure out how to test void methods');
        $instance = $this->_getInstance();

        $instance->expects($this->once())->method('_verifyResponse');

        $instance->processResponse();
    }

    public function testGetPayment()
    {
        $payment = $this->_getMockPayment();

        $instance = $this->_getInstance();
        $result = $instance->getPayment();

        $this->assertEquals($payment, $result);
    }
}
