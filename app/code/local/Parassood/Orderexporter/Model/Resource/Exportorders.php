<?php


class  Parassood_Orderexporter_Model_Resource_Exportorders extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('orderexporter/exportorders', 'id');
    }

}