<?php
class TIG_Buckaroo3Extended_CheckoutController extends Mage_Core_Controller_Front_Action
{
	public function checkoutAction()
	{
        $request = Mage::getModel('buckaroo3extended/request_abstract');
        $request->sendRequest();
	}
}