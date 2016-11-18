<?php
class TIG_Buckaroo3Extended_Model_Refund_CreditmemoTest extends TIG_Buckaroo3Extended_Test_Framework_TIG_Test_TestCase
{
    protected function _getInstance($data = array()){
        $creditmemo = new TIG_Buckaroo3Extended_Model_Refund_Creditmemo();

        if(!array_key_exists('order',$data)){

        }else{
            $this->invokeMethod($creditmemo,'setCurrentOrder',$data['order']);
        }

        if(!array_key_exists('storeId',$data)){
            $order =  $this->invokeMethod($creditmemo,'getOrder');
            $this->invokeMethod($creditmemo,'setStoreId',array($order->getStoreId()));
        }else{
            $this->invokeMethod($creditmemo,'setStoreId',array($data['storeId']));
        }

        if(!array_key_exists('payment',$data)){

        }else{
            $this->invokeMethod($creditmemo,'',array($data['payment']));
        }

        if(!array_key_exists('postArray',$data)){

        }else{
            $this->invokeMethod($creditmemo,'setPostArray',array($data['postArray']));
        }

        if(!array_key_exists('XML',$data)){

        }else{
            $this->invokeMethod($creditmemo,'setResponseXML',array($data['XML']));
        }

        if(!array_key_exists('response',$data)){

        }else{
            $this->invokeMethod($creditmemo,'setResponse',array($data['response']));
        }

        if(!array_key_exists('debugEmail',$data)){

        }else{
            $this->setDebugEmail($data['debugEmail']);

        }

        return $creditmemo;
    }

    public function test1Provider()
    {
        return array(
            array('a', 'b'),
            array('c', 'd'),
        );
    }

    /**
     * @test
     * @dataProvider test1Provider
     */
    public function test1($a, $b){
        $creditmemo = $this->_getInstance();
        $result = $this->invokeMethod($creditmemo,'_getCreditmemoDataItems');

        $this->assertArrayHasKey(1,$result);
    }


}