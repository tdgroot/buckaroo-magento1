<?php
class TIG_Buckaroo3Extended_NotifyController extends Mage_Core_Controller_Front_Action
{
    protected $_order;
    protected $_postArray;
    protected $_debugEmail;
    protected $_paymentMethodCode;
        
	public function setCurrentOrder($order)
    {
    	$this->_order = $order;
    }
    
    public function getCurrentOrder()
    {
    	return $this->_order;
    }
    
    public function setPostArray($array)
    {
    	$this->_postArray = $array;
    }
    
    public function getPostArray()
    {
    	return $this->_postArray;
    }
    
    public function setMethod($paymentMethod)
    {
    	$this->_paymentMethodCode = $paymentMethod;
    }
    
    public function getMethod()
    {
    	return $this->_paymentMethodCode;
    }
    
    public function setDebugEmail($debugEmail)
    {
    	$this->_debugEmail = $debugEmail;
    }
    
    public function getDebugEmail()
    {
    	return $this->_debugEmail;
    }
    
	/**
	 *
	 * Prevents the page from being displayed using GET
	 */
	public function preDispatch()
	{
		if (empty($_POST)) {
			echo 'Only Buckaroo can call this page properly.';
			exit;
		}
		return parent::preDispatch();
	}

	/**
	 *
	 * Handles 'pushes' sent by Buckaroo meant to update the current status of payments/orders
	 */
    public function pushAction()
    {
	    $this->_debugEmail = '';
    	if (isset($_POST['brq_invoicenumber'])) {
    	    $this->_postArray = $_POST;
    	    $orderId = $this->_postArray['brq_invoicenumber'];
    	} else if (isset($_POST['bpe_invoice'])) {
    	    $this->_restructurePostArray();
    	    $orderId = $this->_postArray['brq_invoicenumber'];
    	} else {
    		return;
    	}

    	$this->_debugEmail = 'Buckaroo push recieved at ' . date('Y-m-d H:i:s') . "\n";
    	$this->_debugEmail = 'Order ID: ' . $this->_orderId . "\n";

    	if (isset($_POST['brq_test']) && $_POST['brq_test'] == 'true') {
    	    $this->_debugEmail .= "\n/////////// TEST /////////\n";
    	}
    	
    	$this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

    	$this->_paymentCode = $this->_order->getPayment()->getMethod();

    	$this->_debugEmail .= 'Payment code: ' . $this->_paymentCode . "\n\n";
    	$this->_debugEmail .= 'POST variables recieved: ' . var_export($this->_postArray, true) . "\n\n";
	    
	    list($module, $processedPush) = $this->_processPushAccordingToType();


    	$this->_debugEmail = $module->getDebugEmail();
    	if ($processedPush === false) {
    		$this->_debugEmail .= 'Push was not fully processed!';
    	}

    	$this->_debugEmail .= '\n sent from: ' . __FILE__ . '@' . __LINE__;
    	$module->setDebugEmail($this->_debugEmail);
    	$module->sendDebugEmail();
    }

	public function returnAction()
	{
	    if (isset($_POST['brq_invoicenumber'])) {
    	    $orderId = $_POST['brq_invoicenumber'];
    	} else {
    		return;
    	}

    	$this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

    	$this->_paymentCode = $this->_order->getPayment()->getMethod();

    	$this->_debugEmail .= 'Payment code: ' . $this->_paymentCode . "\n\n";
    	$this->_debugEmail .= 'POST variables recieved: ' . var_export($_POST, true) . "\n\n";

    	$module = Mage::getModel(
    	    'TIG_Buckaroo3Extended_Model_Response_Return',
    	    array(
    	        'order'      => $this->_order,
    	        'postArray'  => $_POST,
    	        'debugEmail' => $this->_debugEmail,
    	        'method'     => $this->_paymentCode,
    	    )
    	);

    	$module->processReturn();
	}
	
	protected function _processPushAccordingToType()
	{
    	if ($this->_order->getTransactionKey() == $this->_postArray['brq_transactions']) {
    	    list($processedPush, $module) = $this->_updateOrderWithKey();
    	} elseif ($this->_pushIsCreditmemo($this->_postArray)) {
        	list($processedPush, $module) = $this->_updateCreditmemo();
    	} elseif (isset($this->_postArray['brq_amount_credit'])) {
    	    list($processedPush, $module) = $this->_newRefund();
    	} elseif (!$this->_order->getTransactionKey()) {
    	    list($processedPush, $module) = $this->_updateOrderWithoutKey();
    	}
    	
    	return array($module, $processedPush);
	}
	
	protected function _updateOrderWithKey()
	{
	    $this->_debugEmail .= "Transaction key matches the order. \n";
    	    
    	$module = Mage::getModel(
    	    'TIG_Buckaroo3Extended_Model_Response_Push',
    	    array(
    	        'order'      => $this->_order,
    	        'postArray'  => $this->_postArray,
    	        'debugEmail' => $this->_debugEmail,
    	        'method'     => $this->_paymentCode,
    	    )
    	);
    	
    	$processedPush = $module->processPush();
    	
	    return array($processedPush, $module);
	}
	
    protected function _updateOrderWithoutKey()
	{
	    $this->_debugEmail .= "Order does not yet have a transaction key and the PUSH does not constitute a refund. \n";
	    
	    $this->_order->setTransactionKey($this->_postArray['brq_transactions'])
	          ->save();
	          
	    $this->_debugEmail .= "Transaction key saved: {$this->_postArray['brq_transactions']}";
	    
    	$module = Mage::getModel(
    	    'TIG_Buckaroo3Extended_Model_Response_Push',
    	    array(
    	        'order'      => $this->_order,
    	        'postArray'  => $this->_postArray,
    	        'debugEmail' => $this->_debugEmail,
    	        'method'     => $this->_paymentCode,
    	    )
    	);
    	
	    $processedPush = $module->processPush();
	    
	    return array($processedPush, $module);
	}    
	
	protected function _updateCreditmemo()
	{
	    $this->_debugEmail .= "Transaction key matches a creditmemo. \n";
	    
	    $module = Mage::getModel(
    	    'TIG_Buckaroo3Extended_Model_Refund_Response_Push',
    	    array(
    	        'order'      => $this->_order,
    	        'postArray'  => $this->_postArray,
    	        'debugEmail' => $this->_debugEmail,
    	    )
    	);
    	
	    $processedPush = /*$module->processPush()*/false; //TODO: create code to update creditmemo
	    
	    return array($processedPush, $module);
	}
	
	protected function _newRefund()
	{
	    $this->_debugEmail .= "The PUSH constitutes a new refund. \n";
	    
	    $module = Mage::getModel(
    	    'TIG_Buckaroo3Extended_Model_Refund_Creditmemo',
    	    array(
    	        'order'      => $this->_order,
    	        'postArray'  => $this->_postArray,
    	        'debugEmail' => $this->_debugEmail,
    	    )
    	);
    	
	    $processedPush = $module->processBuckarooRefundPush();
	    
	    return array($processedPush, $module);
	}
	
	protected function _pushIsCreditmemo()
	{
	    foreach ($this->_order->getCreditmemoCollection() as $creditmemo)
	    {
	        if ($creditmemo->getTransactionKey == $this->_postArray['brq_transactions']) {
	            return true;
	        }
	    }
	    return false;
	}

	protected function _restructurePostArray()
	{
	    $postArray = array(
	        'brq_amount'             => round($_POST['bpe_amount'] / 100, 2),
            'brq_currency'           => $_POST['bpe_currency'],
            'brq_invoicenumber'      => $_POST['bpe_invoice'],
            'brq_statuscode'         => $_POST['bpe_result'],
            'brq_statusmessage'      => null,
            'brq_test'               => $_POST['bpe_mode'] ? 'true' : 'false',
            'brq_timestamp'          => $_POST['bpe_timestamp'],
            'brq_transaction_method' => null,
            'brq_transaction_type'   => null,
            'brq_transactions'       => $_POST['bpe_trx'],
            'brq_signature'          => $_POST['bpe_signature2'],
	        'isOldPost'              => true,
	        'oldPost'                => $_POST,
	    );

	    $this->setPostArray($postArray);
	}
}