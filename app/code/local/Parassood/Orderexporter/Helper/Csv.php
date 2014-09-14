<?php

class Parassood_Orderexporter_Helper_Csv extends Mage_Core_Helper_Abstract
{


    /*
     * We will make a new row in order export CSV file for every order item.
     */
    public function getItemLevelRows($order)
    {
        $rows = array();
        /** @var  $exportConfig Parassood_Orderexporter_Helper_Config */
        $exportConfig = Mage::helper('orderexporter/config');
        $sortedExportConfig = $exportConfig->getSortedFields();
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $orderItem) {
            $row = array();

            if($orderItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
                    && !is_null($orderItem->getParentItemId())){
                //Take data only from configurable/bundled order item unless parent item is not present.
                continue;
            }
            $childrenItems = $orderItem->getChildrenItems();
            if(count($childrenItems)){

                foreach($childrenItems as $childItem){
                    $row = array();
                    if($orderItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
                       /* For configurable/simple products we need to get the total
                       * of child item option price and configurable product price.
                       */
                        $childItem->setPrice($childItem->getPrice() + $orderItem->getPrice());
                    }
                    foreach ($sortedExportConfig as $sortOrder => $fieldInfo) {
                        $row[] = $this->_getCsvColumn($fieldInfo, $order,$childItem);
                    }
                    $rows[] = $row;
                }
                //For $item configurable/bundle product we have written the child items to the csv, so continue.
                continue;

            }

            foreach ($sortedExportConfig as $sortOrder => $fieldInfo) {
                $row[] = $this->_getCsvColumn($fieldInfo, $order,$orderItem);
            }
            $rows[] = $row;
        }

        return $rows;

    }


    /**
     * Get a CSV column's value with respect to an Order Item.
     * @param $fieldInfo
     * @param $order
     * @param $orderItem
     * @return mixed
     */
    protected function _getCsvColumn($fieldInfo, $order,$orderItem)
    {
        $entityModel = $this->_getEntityModel($fieldInfo, $order,$orderItem);
        if($entityModel instanceof Varien_Object )
            return $entityModel->getData($fieldInfo['attribute']);

        /* In case of sales/order_address entities order object returns false on
        *   absence of a shipping address for items like virtual items. We return
        * '' for such cases.
        */
        return '';
    }


    /**
     * Based on configuration set in config XML from admin panel, return the respective
     * model to fetch the data from.
     * @param $fieldInfo
     * @param $order
     * @param $orderItem
     * @return mixed
     */
    protected function _getEntityModel($fieldInfo, $order,$orderItem)
    {
        $entity = $fieldInfo['entity'];
        switch ($entity) {
            case 'sales/order':
                return $order;
                break;

            case 'sales/order_item':
                return $orderItem;
                break;

            case 'sales/order_address.shipping':
                return $order->getShippingAddress();
                break;

            case 'sales/order_address.billing':
                return $order->getBillingAddress();
                break;

            case 'sales/order_payment':
                return $order->getPayment();
                break;

            case 'catalog/product':
                 return $orderItem->getProduct();
                 break;
        }

        Mage::throwException(Mage::helper('orderexporter/config')->__(
            'Unknown entity configured for export.Please check the configuration XML file for export module.'));
    }

}