<?php
$installer = $this;

$installer->startSetup();
$conn = $installer->getConnection();
    
$conn->addColumn($installer->getTable('sales/order'), 'buckaroo_service_version_used', array(
        'TYPE'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'DEFAULT'   => NULL,
        'NULLABLE'  => true,
        'COMMENT'   => 'Buckaroo Service Version Used',
    ));
    
$installer->endSetup();
