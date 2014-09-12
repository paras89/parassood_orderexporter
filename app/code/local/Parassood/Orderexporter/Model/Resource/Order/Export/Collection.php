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







}