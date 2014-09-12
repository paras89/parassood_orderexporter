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
}