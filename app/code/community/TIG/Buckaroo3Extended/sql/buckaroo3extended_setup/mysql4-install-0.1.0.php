<?php 
$installer = $this;

$installer->startSetup();

$sql = <<<SQL
DROP TABLE IF EXISTS `buckaroo_log_expert`;
CREATE TABLE `buckaroo_log_expert`
(
`Id` int NOT NULL AUTO_INCREMENT,
`Timestamp` int NOT NULL,
`Date` varchar(50),
`Previous_setting` varchar(50),
`New_setting` varchar(50) NOT NULL,
`Setting_name` varchar(50) NOT NULL,
`User` varchar(50) NOT NULL,
PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
$installer->run($sql);

$version15 = '1.5.0.0';
$isVersion15 = version_compare(Mage::getVersion(), $version15, '<') ? false : true;

if (!$isVersion15) {
	return;
}

//define statusses to be added
$statusArray = array(
	'buckaroo_pending_payment' => array(
		'status' => 'buckaroo_pending_payment',
		'label' => 'Buckaroo (waiting for payment)', 
		'is_new' => 1, 
		'form_key' => '', 
		'store_labels' => array(), 
		'state' => 'new'
	),
	'buckaroo_incorrect_amount' => array(
		'status' => 'buckaroo_incorrect_payment',
		'label' => 'Buckaroo On Hold (incorrect amount transfered)', 
		'is_new' => 1, 
		'form_key' => '', 
		'store_labels' => array(), 
		'state' => 'holded'
	)
);

//add the statusses and link them to their defined states
foreach ($statusArray as $status) {
	$_stat = Mage::getModel('sales/order_status')->load($status['status']);
		
	/* Add Status */
	if ($status['is_new'] && $_stat->getStatus()) {
	    return;
	}
	
	$_stat->setData($status)->setStatus($status['status']);
	
	try {
	    $_stat->save();
	} catch (Mage_Core_Exception $e) {  } 
	
	/* Assign Status to State */
	if ($_stat && $_stat->getStatus()) {
	    try {
	        $_stat->assignState($status['state'], false);
	    } catch (Mage_Core_Exception $e) {  } 
	    catch (Exception $e) {  }
	}
}
$installer->endSetup();
