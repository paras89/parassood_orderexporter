<?php

class  Parassood_Orderexporter_Model_Resource_Order_Export_Collection extends Mage_Sales_Model_Resource_Order_Collection
{

    /**
     * Add hourly filter to order collection for export.
     * If the setting to use grid filter is set in admin system configuration don't set the filter.
     * @return $this
     */
    public function addHourlyFilter()
    {
        //If grid filter is enabled we don't apply the hourly filter on the order collection.

        /** @var  $helper Parassood_Orderexporter_Helper_Data */
        $helper = Mage::helper('orderexporter');
         if($helper->isGridFilterEnabled()){
             return $this;
         }

         $hourReport = $helper->getExportHours();
         $time = time();
         $to = date('Y-m-d H:i:s', $time);
         $lastTime = $time - 3600 * $hourReport; // 1 Hour filter.
         $from = date('Y-m-d H:i:s', $lastTime);
         $this->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to));

    }

    /*
     * Add Filter to order collection to exclude already exported orders.
     */
    public function addAlreadyExportedFilter()
    {

        /** @var $helper Parassood_Orderexporter_Helper_Data */
        $helper = Mage::helper('orderexporter');
        if($helper->isGridFilterEnabled()){
            return $this;
        }

        // Left Join sales_flat_order table with orderexport_marked_orders so we filter out already exported orders.
        $this->getSelect()->joinLeft(array('export_order' => $this->getTable('orderexporter/exportorders')),
                                     'main_table.entity_id = export_order.order_id',array());

        //Export orders will be marked exported in export_order.is_exported = 1. So filter them out.
        $this->getSelect()->where('export_order.is_exported IS NULL  OR export_order.is_exported = 0');
        return $this;
    }







}