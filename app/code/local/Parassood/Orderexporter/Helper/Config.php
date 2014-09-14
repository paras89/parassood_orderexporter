<?php


class Parassood_Orderexporter_Helper_Config extends Mage_Core_Helper_Abstract
{

    protected $_exportLayout = null;

    protected $_sortedFields = null;

    protected $_entities = array('sales_order' => 'sales/order',
        'product' => 'catalog/product',
        'sales_order_item' => 'sales/order_item',
        'shipping_address' => 'sales/order_address',
        'billing_address' => 'sales/order_address',
        'sales_payment' => 'sales/order_payment');


    public function getDefaultXml()
    {
        $node = Mage::getConfig()->getNode('default_fields');
        $xmlNode = $node->asNiceXml();
        $comments = '<!-- Entities represent:
	   1. sales_order : sales_flat_order table
	   2. product" catalog/product
	   3. sales_order_item : sales_flat_order_item
	   4. shipping_address: sales_flat_order_address with address type shipping.
	   5. billing_address: sales_flat_order_address with address type billing.
	   6. sales_payment: sales_flat_order_payment

	   Each child node under an entity node represents a column name in the respective entity tables listed above.
	   Label is the header Label in the Order Export CSV.
	   Columns will be ordered in the Export CSV as per the sort_order field value for each entity field.
       You can add any column that is part of one of the above entities in your Magento Set up, including custom fields you might have added. -->';

        return $comments . $xmlNode;
    }


    public function validateExportXML($fileXML)
    {
        $xmlObject = new Varien_Simplexml_Element($fileXML);
        $this->validateEntities($xmlObject);
        return $xmlObject;

    }


    public function validateEntities($xmlObject)
    {
        $entities = $xmlObject->entities;
        $entities = $entities->asArray();
        foreach ($entities as $key => $entity) {

            if (!array_key_exists($key, $this->_entities)) {
                $message = 'Entity: ' . $key . ' cannot be configured. Please remove it.';
                Mage::throwException($message);
            }
            $mageEntity = $this->_entities[$key];
            if (!array_key_exists('fields', $entity) || !is_array($entity['fields'])) {
                continue;
            }
            foreach ($entity['fields'] as $key => $child) {
                $this->_validateAttribute($mageEntity, $key);

            }
        }

    }


    protected function _validateAttribute($entity, $attribute)
    {
        if ($entity != 'catalog/product') {
            $resource = Mage::getSingleton('core/resource');
            $resourceModel = Mage::getModel('orderexporter/exportorders')->getResource();
            $adapter = $resource->getConnection('core_read');
            $columns = $adapter->describeTable($resourceModel->getTable($entity));
            if (!array_key_exists($attribute, $columns)) {

                $message = 'Column Attribute :' . $attribute . ' of table: ' . $resourceModel->getTable($entity) .
                    ' does not exist. Please check the XML file again.';
                Mage::throwException($message);
            } else {
                return true;
            }
        } else {
            $attr = Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_product', $attribute);
            if ($attr->getId() !== null) {
                return true;
            } else {
                $message = 'Attribute :' . $attribute . ' of entity catalog/product' .
                    ' does not exist. Please check the XML file.';
                Mage::throwException($message);
            }
        }
        return true;

    }

    public function getExportLayout()
    {
        if (!isset($this->_exportLayout)) {


            $exportSettingFile = Mage::getStoreConfig('orderexporter/orderexport_settings/file');
            if (isset($exportSettingFile) && $exportSettingFile != '') {
                $settingXML = @file_get_contents(Mage::getBaseDir() . DS . 'var' . DS . 'uploads'
                    . DS . $exportSettingFile);
                $settingObj = $this->validateExportXML($settingXML);
            } else {
                $settingObj = Mage::getConfig()->getNode('default_fields');
            }
            $this->_exportLayout = $settingObj;
        }

        return $this->_exportLayout;
    }

    public function getCsvHeaders()
    {
        $sortedFields = $this->getSortedFields();
        $headers = array();
        foreach($sortedFields as $order => $fieldInfo){
            $headers[] = $fieldInfo['label'];
        }
        return $headers;
    }


    public function getSortedFields()
    {
        if (!isset($this->_sortedFields)) {

            $exportSettings = $this->getExportLayout();
            $entities = $exportSettings->entities;
            $exportCsvLayout = $entities->asArray();

            $sortedFields = array();
            foreach ($exportCsvLayout as $entity => $fields) {
                foreach ($fields as $attributeCode => $fieldInfo) {
                    $sortedFields[$fieldInfo['sort_order']] = array('attribute' => $attributeCode,
                        'label' => $fieldInfo['label']);
                }
            }

            $this->_sortedFields = ksort($sortedFields);
        }
        return $this->_sortedFields;
    }


}