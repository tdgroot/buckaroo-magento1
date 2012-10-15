<?php
class TIG_Buckaroo3Extended_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isAdmin()
	{
		if(Mage::app()->getStore()->isAdmin()) {
			return true;
		}
	
		if(Mage::getDesign()->getArea() == 'adminhtml') {
			return true;
		}
	
		return false;
	}
	
	public function log($message, $force = false)
	{
	    Mage::log($message, Zend_Log::DEBUG, 'TIG_B3E.log', $force);
	}

	public function logException($e)
	{
	    if (is_string($e)) {
	        Mage::log($e, Zend_Log::ERR, 'TIG_B3E_Exception.log', true);
	    } else {
	        Mage::log($e->getMessage(), Zend_Log::ERR, 'TIG_B3E_Exception.log', true);
	        Mage::log($e->getTraceAsString(), Zend_Log::ERR, 'TIG_B3E_Exception.log', true);
	    }
	}
}