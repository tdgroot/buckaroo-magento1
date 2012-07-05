<?php
class TIG_Buckaroo3Extended_CheckoutController extends Mage_Core_Controller_Front_Action
{
	/**
	 * process checkout and then echo a form with all required fields and auto-submit javascript
	 */
	public function checkoutAction()
	{
        $request = Mage::getModel('buckaroo3extended/request_abstract');
        $request->sendRequest();
	}
}