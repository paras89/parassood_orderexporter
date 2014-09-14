<?php


class Parassood_Orderexporter_Helper_Config extends Mage_Core_Helper_Abstract
{

    protected $_exportLayout = null;

    protected $_sortedFields = null;

    /**
     * XML Configuration to entity map.
     * @var array
     */
    protected $_entities = array('sales_order' => 'sales/order',
        'product' => 'catalog/product',
        'sales_order_item' => 'sales/order_item',
        'shipping_address' => 'sales/order_address.shipping',
        'billing_address' => 'sales/order_address.billing',
        'sales_payment' => 'sales/order_payment');


    /**
     * Return Default Orderexporter configuration XML.
     * @return string
     */
    public function getDefaultXml()
    {
        $node = Mage::getConfig()->getNode('default_fields');
        $xmlNode = $node->asNiceXml();
        //Adding usage comments to the default configuration.

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

    /**
     * Validate XML of Orderexporter configuration XML file uploaded by user from admin panel.
     * @param $fileXML
     * @return Varien_Simplexml_Element
     */
    public function validateExportXML($fileXML)
    {
        $xmlObject = new Varien_Simplexml_Element($fileXML);
        $this->validateEntities($xmlObject);
        return $xmlObject;

    }

    /**
     * Validate XMLObject from user uploaded file to ensure it has only columns and entities
     * that actually exist set for export
     * @param $xmlObject
     */
    public function validateEntities($xmlObject)
    {
        $entities = $xmlObject->entities;
        $entities = $entities->asArray();
        foreach ($entities as $key => $entity) {

            if (!array_key_exists($key, $this->_entities)) {
                // Uknown entity configured by user. Throw Exception.
                $message = 'Entity: ' . $key . ' cannot be configured. Please remove it.';
                Mage::throwException($message);
            }
            $mageEntity = $this->_entities[$key];
            /*  From order address entity we need to remove shipping/billing identifier. */
            $mageEntity = explode('.',$mageEntity);
            $mageEntity = $mageEntity[0];
            if (!array_key_exists('fields', $entity) || !is_array($entity['fields'])) {
               // Empty child node added to entity node. Nothing to do here. Continue.
                continue;
            }
            foreach ($entity['fields'] as $key => $child) {
                //Validate that column/attribute $key exists in $mageEntity.
                $this->_validateAttribute($mageEntity, $key);

            }
        }

    }

    /**
     * Ensure column/attribute $attribute exists in $entity table/eav entity.
     * Throw invalid configuration exception, it if does not exist.
     * @param $entity
     * @param $attribute
     * @return bool
     */
    protected function _validateAttribute($entity, $attribute)
    {
        if ($entity != 'catalog/product') {
            // Only use this block for non EAV entities.
            $resource = Mage::getSingleton('core/resource');
            $resourceModel = Mage::getModel('orderexporter/exportorders')->getResource();
            $adapter = $resource->getConnection('core_read');
            $columns = $adapter->describeTable($resourceModel->getTable($entity));
            if (!array_key_exists($attribute, $columns)) {
               /* $column does not exist in description of table for entity $entity
               * Throw exception.
               */
                $message = 'Column Attribute :' . $attribute . ' of table: ' . $resourceModel->getTable($entity) .
                    ' does not exist. Please check the XML file again.';
                Mage::throwException($message);
            } else {
                return true;
            }
        } else {
            // catalog/product is a EAV entity. Validate it's attributes separately.
            $attr = Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_product', $attribute);
            if ($attr->getId() !== null) {
                return true;
            } else {
                /*
                 * Attribute $attribute does not exist in entity catalog/product. Throw exception.
                 */
                $message = 'Attribute :' . $attribute . ' of entity catalog/product' .
                    ' does not exist. Please check the XML file.';
                Mage::throwException($message);
            }
        }
        return true;

    }

    /**
     * Extract Varien_Simplexml_Element from either default configuration or
     * custom configuration XML uploaded by user from admin panel.
     * @return Mage_Core_Model_Config_Element|null|Varien_Simplexml_Element
     */
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

    /**
     * Return Sorted srray of headers for Orderexport CSV.
     * @return array
     */
    public function getCsvHeaders()
    {
        $sortedFields = $this->getSortedFields();
        $headers = array();
        foreach ($sortedFields as $order => $fieldInfo) {
            $headers[] = $fieldInfo['label'];
        }
        return $headers;
    }


    /**
     * Get array of fields and entities to be include in order export.
     * The fields are sorted for export based on sort_order field from XML configuration.
     * @return array|null
     */
    public function getSortedFields()
    {
        if (!isset($this->_sortedFields)) {

            $exportSettings = $this->getExportLayout();
            $entities = $exportSettings->entities;
            $exportCsvLayout = $entities->asArray();

            $sortedFields = array();
            foreach ($exportCsvLayout as $entity => $fields) {
                $fields = $fields['fields'];
                if (!is_array($fields)) {
                    continue;
                }
                foreach ($fields as $attributeCode => $fieldInfo) {
                    if (array_key_exists($fieldInfo['sort_order'], $sortedFields)) {
                        /* The sort_order field for this entity column is duplicate.
                           Find the next available slot for this field.
                        */
                        $sortOrder = $fieldInfo['sort_order'];
                        while (true) {
                            $sortOrder = $sortOrder + 0.01;
                            // Convert to string so it can be used as a key for associative array.
                            $sortOrder = (string)$sortOrder;
                            if (array_key_exists($sortOrder, $sortedFields)) {
                                $sortOrder++;
                            } else {
                                $sortedFields[$sortOrder] = array('attribute' => $attributeCode,
                                    'label' => $fieldInfo['label'],
                                     'entity' => $this->_entities[$entity]);
                                break;
                            }

                        }
                        // A slot was found for duplicate sort order. Entry made, continue with other fields.
                        continue;
                    }
                    $sortedFields[$fieldInfo['sort_order']] = array('attribute' => $attributeCode,
                        'label' => $fieldInfo['label'],
                        'entity' => $this->_entities[$entity]);
                }
            }

            // Sort $sortedFields based on keys. sorted_order from config XML was used as keys.
            ksort($sortedFields);
            $this->_sortedFields = $sortedFields;
        }
        return $this->_sortedFields;
    }


}