<?php
class Parassood_Orderexporter_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Return the setting for grid filter enabled/disabled.
     * @return bool
     */
    public function isGridFilterEnabled()
    {
        return Mage::getStoreConfig('orderexporter/orderexport_settings/grid_filter');
    }


    /**
     * Return the number of hours for report export
     * @return int
     */
    public function getExportHours()
    {

        $hours = Mage::getStoreConfig('orderexporter/orderexport_settings/hour_export');
        if(is_numeric($hours)){
            return $hours;
        }

        return 1;
    }

    /**
     * Mark an order as exported so that it is not pulled out in next export.
     * @param $order
     * @return $this
     */
    public function markOrderAsExported($order)
    {
        $exportOrder = Mage::getModel('orderexporter/exportorders')->load($order->getId(),'order_id');
        $exportOrder->setIsExported(1)
                    ->setOrderId($order->getId())
                    ->setExportedAt(now())
                    ->save();
        return $this;
    }
}