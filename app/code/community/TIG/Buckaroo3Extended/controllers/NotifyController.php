<?php
class TIG_Buckaroo3Extended_NotifyController extends Mage_Core_Controller_Front_Action
{
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
		parent::preDispatch();
	}

	/**
	 *
	 * Handles 'pushes' sent by Buckaroo meant to update the current status of payments/orders
	 */
    public function pushAction()
    {
    	if (isset($_POST['brq_invoicenumber'])) {
    	    $postArray = $_POST;
    	    $orderId = $postArray['brq_invoicenumber'];
    	} else if (isset($_POST['bpe_invoice'])) {
    	    $postArray = $this->_restructurePostArray();
    	    $orderId = $postArray['brq_invoicenumber'];
    	} else {
    		return;
    	}

    	$debugEmail = 'Buckaroo push recieved at ' . date('Y-m-d H:i:s') . "\n";
    	$debugEmail = 'Order ID: ' . $orderId . "\n";

    	if (isset($_POST['brq_test']) && $_POST['brq_test'] == 'true') {
    	    $debugEmail .= "\n/////////// TEST /////////\n";
    	}
    	
    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

    	$paymentCode = $order->getPayment()->getMethod();

    	$debugEmail .= 'Payment code: ' . $paymentCode . "\n\n";
    	$debugEmail .= 'POST variables recieved: ' . var_export($postArray, true) . "\n\n";

    	$module = Mage::getModel(
    	    'TIG_Buckaroo3Extended_Model_Response_Push',
    	    array(
    	        'order'      => $order,
    	        'postArray'  => $postArray,
    	        'debugEmail' => $debugEmail,
    	        'method'     => $paymentCode,
    	    )
    	);

    	$processedPush = $module->processPush();

    	$debugEmail = $module->getDebugEmail();
    	if ($processedPush === false) {
    		$debugEmail .= 'Push was not fully processed!';
    	}

    	$debugEmail .= '\n sent from: ' . __FILE__ . '@' . __LINE__;
    	$module->setDebugEmail($debugEmail);
    	$module->sendDebugEmail();
    }

	public function returnAction()
	{
	    if (isset($_POST['brq_invoicenumber'])) {
    	    $orderId = $_POST['brq_invoicenumber'];
    	} else {
    		return;
    	}

    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

    	$paymentCode = $order->getPayment()->getMethod();

    	$debugEmail .= 'Payment code: ' . $paymentCode . "\n\n";
    	$debugEmail .= 'POST variables recieved: ' . var_export($_POST, true) . "\n\n";

    	$module = Mage::getModel(
    	    'TIG_Buckaroo3Extended_Model_Response_Return',
    	    array(
    	        'order'      => $order,
    	        'postArray'  => $_POST,
    	        'debugEmail' => $debugEmail,
    	        'method'     => $paymentCode,
    	    )
    	);

    	$module->processReturn();
	}

	/**
	 * Restructure the Push message sent by the BPE 2.0 to one resembling a push message sent by BPE 3.0.
	 * This way push messages sent to update a 2.0 transaction can still be processed.
	 */
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

	    return $postArray;
	}
}