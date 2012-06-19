<?php
class TIG_Buckaroo3Extended_Model_Observer extends Mage_Core_Model_Abstract 
{    
    /**
     * When config settings are saved in the backend, retrieve the title of all buckaroo payment methods that have been activated.
     * These titles are using the following path: 'buckaroo/buckaroo2012PAYMENTCODE/title'.
     * Retrieve this and enter it in the DB using the following path: 'payment/buckaroo2012PAYMENTMETHOD/title'.
     * This way, magento will know what label to give the payment methods in the frontend.
     * The same goes for the sort_order
     * 
     * Secondly, calls the 'logExpertSettings' method which will log any changes made to expert settings in a seperate table.
     * 
     * @param Varien_Event_Observer $observer
     */
    public function controller_action_postdispatch_adminhtml_system_config_save(Varien_Event_Observer $observer) 
    {
    	//check if the section being saved is 'buckaroo'
    	$buckarooRequest = strpos(Mage::getSingleton('core/app')->getRequest()->getRequestUri(), 'section/buckaroo/');
    	if ($buckarooRequest === false) {
    		return false;
    	}
    	
    	//get all activated payment methods
    	$payments = Mage::getSingleton('payment/config')->getActiveMethods();
        foreach ($payments as $payment) {
            
    		//get the code and check if its a buckaroo2012 payment method
    		$code = $payment->getCode();
    		$isBuckaroo = strpos($code, 'buckaroo3extended');
    		if ($isBuckaroo !== false) {
    		    foreach(Mage::app()->getStores() as $eachStore => $storeVal)
    		    {
        		    //retrieve the title as set in the backend
        			$title = mage::getStoreConfig('buckaroo/' . $code . '/title', Mage::app()->getStore($eachStore)->getId());
        			$sort_order = mage::getStoreConfig('buckaroo/' . $code . '/sort_order', Mage::app()->getStore($eachStore)->getId());
        			
        			if ($title) {
        				//set the title as the new path
        				Mage::getModel('core/config')->saveConfig('payment/' . $code . '/title', $title, 'stores', Mage::app()->getStore($eachStore)->getId());
        			}
        			if ($sort_order) {
        				//set the sort_order as the new path
        				Mage::getModel('core/config')->saveConfig('payment/' . $code . '/sort_order', $sort_order, 'stores', Mage::app()->getStore($eachStore)->getId());
        			}
    		    }
    			
    		}
    	}
    	
    	//log any changes that may have been made to expert settings, for debugging purposes only
    	$this->_logExpertSettings();
    }
    
    /**
     * 
     * Enters a notification in the buckaroo_log_expert table of any changes made to expert settings. This is
     * for debugging purposes only.
     */
    private function _logExpertSettings()
    {
    	//array of default expert settings
    	$expertSettings = array(
    		'order_status'				 => 'pending',
    		'gateway'                    => 'https://payment.buckaroo.nl/gateway/payment.asp',
    	    'soap_gateway'                => 'https://payment.buckaroo.nl/soap/soap.asmx',
	    	'auto_invoice'               => '1',
	    	'order_state_success'        => 'processing',
	    	'order_state_failed'         => 'canceled',
    		'order_state_pendingpayment' => 'new',
	    	'success_redirect'           => 'checkout/onepage/success',
	    	'failure_redirect'           => 'checkout/onepage',
	    	'debug_email'                => NULL,
	    	'debug_order_updates'        => '0',
	    	'cancel_on_failed'           => '1',
    	);
    	
    	$db = Mage::getSingleton('core/resource')->getConnection('core/write');

    	foreach ($expertSettings as $settingName => $setting) {
    		//unset $prevSetting if it has been set by a previous iteration of the script
    		if (isset($prevSetting)) {
    			unset($prevSetting);
    		}
    		
    		//check the current setting
    		$newSetting = Mage::getStoreConfig('buckaroo/buckaroo3extended_advanced/' . $settingName);
    		
    		//query the DB to see if this setting has been changed before
    		$prevSettingSql = "SELECT `New_setting` FROM `buckaroo_log_expert` WHERE `Setting_name` = '{$settingName}' ORDER BY `Timestamp` DESC LIMIT 0, 1 ";
    		$result = $db->query($prevSettingSql);
			while ($prevSettingArray = $result->fetch() ) {
				if ($prevSettingArray) {
					$prevSetting = $prevSettingArray['New_setting'];
				}
			}
			
			//if this is the first time a setting has been changed, use the default setting as the previous setting
			if (!isset($prevSetting)) {
				$prevSetting = $setting;
			}
			
			//if this change had already been made before, continue with the next setting
			if ($newSetting == $prevSetting) {
				continue;
			}
			
			//escape output
    		$date = mysql_escape_string(date('d-m-y G:i:s'));
    		$user = mysql_escape_string(Mage::getSingleton('admin/session')->getUser()->getUsername());
    		$newSetting = mysql_escape_string($newSetting);
    		$prevSetting = mysql_escape_string($prevSetting);
    		$settingName = mysql_escape_string($settingName);
    		$time = time();
    		
    		$sql = "INSERT INTO `buckaroo_log_expert` 
    					(`Timestamp`, `Date`,`Setting_name` ,`Previous_setting`, `New_setting`, `User`) 
    				VALUES 
    					('{$time}', '{$date}', '{$settingName}', '{$prevSetting}', '{$newSetting}', '{$user}')"; 
		    try {
    			$result = $db->query($sql);
			   	if (!$result) {
			   		throw new Exception();
			   	}
		    } catch (Exception $e) { }
    	}
    }
}