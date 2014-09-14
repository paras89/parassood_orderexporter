<?php

class Parassood_Orderexporter_Model_System_Config_Backend_File extends
    Mage_Adminhtml_Model_System_Config_Backend_File
{

    /**
     * Override core before save for upload. Validate the export order configuration XML file
     * before saving the value for core_config_data. In case validation fails unset value.
     *
     * @return Mage_Adminhtml_Model_System_Config_Backend_File
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $file = @file_get_contents(Mage::getBaseDir() . DS . 'var' . DS . 'uploads' . DS . $this->getValue());
        if (isset($file) && $file != '' && $file) {
            try {
                Mage::helper('orderexporter/config')->validateExportXML($file);
            } catch (Exception $e) {
                $this->setValue(null);
                Mage::throwException($e->getMessage());
            }

            Mage::getSingleton('adminhtml/session')
                ->addSuccess(
                    Mage::helper('adminhtml')->__('Order Export Configuration XML was loaded and validated successfully.'));
        }
        return $this;
    }


    protected function _getAllowedExtensions()
    {
        return array('xml');
    }


}