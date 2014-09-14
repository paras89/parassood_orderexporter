<?php


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$conn = $installer->getConnection();
$table = $installer->getTable('orderexporter/exportorders');
$conn->addColumn($table, 'exported_at', 'datetime');

$installer->endSetup();