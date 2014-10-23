<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
$installer = $this;

$installer->startSetup();


/**
 * Add Buckaroo Payment fee columns to sales/order.
 */
$salesOrderTable = $installer->getTable('sales/order');

if (!$conn->tableColumnExists($salesOrderTable, 'base_buckaroo_fee')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_buckaroo_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee',
            'after'    => 'base_shipping_tax_refunded',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'base_buckaroo_fee_invoiced')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_buckaroo_fee_invoiced',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee Invoiced',
            'after'    => 'base_buckaroo_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'base_buckaroo_fee_refunded')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_buckaroo_fee_refunded',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee Refunded',
            'after'    => 'base_buckaroo_fee_invoiced',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'base_buckaroo_fee_tax')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_buckaroo_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee Tax',
            'after'    => 'base_buckaroo_fee_refunded',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'base_buckaroo_fee_tax_invoiced')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_buckaroo_fee_tax_invoiced',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee Tax Invoiced',
            'after'    => 'base_buckaroo_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'base_buckaroo_fee_tax_refunded')) {
    $conn->addColumn(
        $salesOrderTable,
        'base_buckaroo_fee_tax_refunded',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee Tax Refunded',
            'after'    => 'base_buckaroo_fee_tax_invoiced',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'buckaroo_fee')) {
    $conn->addColumn(
        $salesOrderTable,
        'buckaroo_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee',
            'after'    => 'shipping_tax_refunded',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'buckaroo_fee_invoiced')) {
    $conn->addColumn(
        $salesOrderTable,
        'buckaroo_fee_invoiced',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee Invoiced',
            'after'    => 'buckaroo_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'buckaroo_fee_refunded')) {
    $conn->addColumn(
        $salesOrderTable,
        'buckaroo_fee_refunded',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee Refunded',
            'after'    => 'buckaroo_fee_invoiced',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'buckaroo_fee_tax')) {
    $conn->addColumn(
        $salesOrderTable,
        'buckaroo_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee Tax',
            'after'    => 'buckaroo_fee_refunded',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'buckaroo_fee_tax_invoiced')) {
    $conn->addColumn(
        $salesOrderTable,
        'buckaroo_fee_tax_invoiced',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee Tax Invoiced',
            'after'    => 'buckaroo_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesOrderTable, 'buckaroo_fee_tax_refunded')) {
    $conn->addColumn(
        $salesOrderTable,
        'buckaroo_fee_tax_refunded',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee Tax Refunded',
            'after'    => 'buckaroo_fee_tax_invoiced',
        )
    );
}

/***********************************************************************************************************************
 * INVOICE
 **********************************************************************************************************************/

/**
 * Add Buckaroo Payment fee columns to sales/order_invoice.
 */
$salesInvoiceTable = $installer->getTable('sales/invoice');

if (!$conn->tableColumnExists($salesInvoiceTable, 'base_buckaroo_fee')) {
    $conn->addColumn(
        $salesInvoiceTable,
        'base_buckaroo_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee',
            'after'    => 'base_shipping_amount',
        )
    );
}

if (!$conn->tableColumnExists($salesInvoiceTable, 'base_buckaroo_fee_tax')) {
    $conn->addColumn(
        $salesInvoiceTable,
        'base_buckaroo_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee Tax',
            'after'    => 'base_buckaroo_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesInvoiceTable, 'buckaroo_fee')) {
    $conn->addColumn(
        $salesInvoiceTable,
        'buckaroo_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee',
            'after'    => 'base_buckaroo_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesInvoiceTable, 'buckaroo_fee_tax')) {
    $conn->addColumn(
        $salesInvoiceTable,
        'buckaroo_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee Tax',
            'after'    => 'buckaroo_fee',
        )
    );
}

/***********************************************************************************************************************
 * QUOTE
 **********************************************************************************************************************/

/**
 * Add Buckaroo Payment fee columns to sales/quote.
 */
$salesQuoteTable = $installer->getTable('sales/quote');

if (!$conn->tableColumnExists($salesQuoteTable, 'base_buckaroo_fee')) {
    $conn->addColumn(
        $salesQuoteTable,
        'base_buckaroo_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee',
            'after'    => 'customer_gender',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteTable, 'base_buckaroo_fee_tax')) {
    $conn->addColumn(
        $salesQuoteTable,
        'base_buckaroo_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee Tax',
            'after'    => 'base_buckaroo_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteTable, 'buckaroo_fee')) {
    $conn->addColumn(
        $salesQuoteTable,
        'buckaroo_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee',
            'after'    => 'base_buckaroo_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteTable, 'buckaroo_fee_tax')) {
    $conn->addColumn(
        $salesQuoteTable,
        'buckaroo_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee Tax',
            'after'    => 'buckaroo_fee',
        )
    );
}

/***********************************************************************************************************************
 * QUOTE ADDRESS
 **********************************************************************************************************************/

/**
 * Add Buckaroo Payment fee columns to sales/quote_address.
 */
$salesQuoteAddressTable = $installer->getTable('sales/quote_address');

if (!$conn->tableColumnExists($salesQuoteAddressTable, 'base_buckaroo_fee')) {
    $conn->addColumn(
        $salesQuoteAddressTable,
        'base_buckaroo_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee',
            'after'    => 'base_shipping_tax_amount',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteAddressTable, 'base_buckaroo_fee_tax')) {
    $conn->addColumn(
        $salesQuoteAddressTable,
        'base_buckaroo_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee Tax',
            'after'    => 'base_buckaroo_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteAddressTable, 'buckaroo_fee')) {
    $conn->addColumn(
        $salesQuoteAddressTable,
        'buckaroo_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee',
            'after'    => 'base_buckaroo_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesQuoteAddressTable, 'buckaroo_fee_tax')) {
    $conn->addColumn(
        $salesQuoteAddressTable,
        'buckaroo_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee Tax',
            'after'    => 'buckaroo_fee',
        )
    );
}

/***********************************************************************************************************************
 * CREDITMEMO
 **********************************************************************************************************************/

/**
 * Add Buckaroo Payment fee columns to sales/creditmemo.
 */
$salesCreditmemoTable = $installer->getTable('sales/creditmemo');

if (!$conn->tableColumnExists($salesCreditmemoTable, 'base_buckaroo_fee')) {
    $conn->addColumn(
        $salesCreditmemoTable,
        'base_buckaroo_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee',
            'after'    => 'shipping_tax_amount',
        )
    );
}

if (!$conn->tableColumnExists($salesCreditmemoTable, 'base_buckaroo_fee_tax')) {
    $conn->addColumn(
        $salesCreditmemoTable,
        'base_buckaroo_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Base Buckaroo Payment Fee Tax',
            'after'    => 'base_buckaroo_fee',
        )
    );
}

if (!$conn->tableColumnExists($salesCreditmemoTable, 'buckaroo_fee')) {
    $conn->addColumn(
        $salesCreditmemoTable,
        'buckaroo_fee',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee',
            'after'    => 'base_buckaroo_fee_tax',
        )
    );
}

if (!$conn->tableColumnExists($salesCreditmemoTable, 'buckaroo_fee_tax')) {
    $conn->addColumn(
        $salesCreditmemoTable,
        'buckaroo_fee_tax',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => true,
            'default'  => 0,
            'length'   => '12,4',
            'comment'  => 'Buckaroo Payment Fee Tax',
            'after'    => 'buckaroo_fee',
        )
    );
}

$installer->endSetup();
