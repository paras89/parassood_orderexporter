<?php


class Parassood_Orderexporter_Model_Resource_Prescription_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('orderexporter/exportorders');
    }

}




