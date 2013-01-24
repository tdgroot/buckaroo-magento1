<?php 
$installer = $this;

$installer->startSetup();
$conn = $installer->getConnection();

/**
 * Add PaymentFee columns to sales/order
 */
$conn->addColumn(
    $installer->getTable('sales/order'),
    'payment_fee',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'base_payment_fee',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'payment_fee_invoiced',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'base_payment_fee_invoiced',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'payment_fee_tax',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'base_payment_fee_tax',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'payment_fee_tax_invoiced',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'base_payment_fee_tax_invoiced',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'payment_fee_refunded',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'base_payment_fee_refunded',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'payment_fee_tax_refunded',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/order'),
    'base_payment_fee_tax_refunded',
    "decimal(12,4) null"
);

/**
 * Add PaymentFee columns to sales/order_invoice
 */
$conn->addColumn(
    $installer->getTable('sales/invoice'),
    'payment_fee',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/invoice'),
    'base_payment_fee',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/invoice'),
    'payment_fee_tax',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/invoice'),
    'base_payment_fee_tax',
    "decimal(12,4) null"
);

/**
 * Add PaymentFee columns to sales/quote
 */
$conn->addColumn(
    $installer->getTable('sales/quote'),
    'payment_fee',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/quote'),
    'base_payment_fee',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/quote'),
    'payment_fee_tax',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/quote'),
    'base_payment_fee_tax',
    "decimal(12,4) null"
);

/**
 * Add PaymentFee columns to sales/quote_address
 */
$conn->addColumn(
    $installer->getTable('sales/quote_address'),
    'payment_fee',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/quote_address'),
    'base_payment_fee',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/quote_address'),
    'payment_fee_tax',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/quote_address'),
    'base_payment_fee_tax',
    "decimal(12,4) null"
);

/**
 * Add PaymentFee columns to sales/order_creditmemo
 */
$conn->addColumn(
    $installer->getTable('sales/creditmemo'),
    'payment_fee',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/creditmemo'),
    'base_payment_fee',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/creditmemo'),
    'payment_fee_tax',
    "decimal(12,4) null"
);
$conn->addColumn(
    $installer->getTable('sales/creditmemo'),
    'base_payment_fee_tax',
    "decimal(12,4) null"
);

$installer->endSetup();
