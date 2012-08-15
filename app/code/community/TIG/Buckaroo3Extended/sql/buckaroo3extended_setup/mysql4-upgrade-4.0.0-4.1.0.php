<?php 
$installer = $this;

$installer->startSetup();

$salesFlatCreditMemoTableName = $installer->getTable('sales_flat_creditmemo');
$salesFlatOrderTableName = $installer->getTable('sales_flat_order');

$sql = <<<SQL
ALTER TABLE `{$salesFlatCreditMemoTableName}`
ADD `transaction_key` varchar(50) NULL
SQL;

$sql2 = <<<SQL2
ALTER TABLE `{$salesFlatOrderTableName}`
ADD `payment_method_used_for_transaction` varchar(50) NULL
SQL2;

$sql3 = <<<SQL3
ALTER TABLE `{$salesFlatOrderTableName}`
ADD `currency_code_used_for_transaction` varchar(3) NULL
SQL3;

try {
    $installer->run($sql);
    $installer->run($sql2);
    $installer->run($sql3);
} catch (Exception $e) {
    
}

$installer->endSetup();