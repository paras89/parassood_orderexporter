<?php


class Parassood_Orderexporter_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('main_table.created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'orderexporter/order_export_collection';
    }

    protected function _prepareCollection()
    {
        Mage::helper('orderexporter/config')->getExportLayout();
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        if(!Mage::helper('orderexporter')->isGridFilterEnabled()){

            /** Only apply hourly and already exported order filter if
             *  system config for this module has turned off grid filters.
             * Otherwise use default grid filters for the export.
             **/
            $collection->addHourlyFilter();
            $collection->addAlreadyExportedFilter();
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }




    /**
     * Write item data to csv export file
     *
     * @param Varien_Object $item
     * @param Varien_Io_File $adapter
     */
    protected function _exportCsvItem(Varien_Object $item, Varien_Io_File $adapter)
    {
       //$item is Mage_Sales_Model_Order Object.
       $rows = Mage::helper('orderexporter/csv')->getItemLevelRows($item);
       foreach($rows as $row){
           $adapter->streamWriteCsv($row);
       }

       if(!Mage::helper('orderexporter')->isGridFilterEnabled()){
           /**
            * Mark Order as exported only if the setting to use default
            * grid filters for the export is set to NO.
            */
           Mage::helper('orderexporter')->markOrderAsExported($item);
       }

    }

    /**
     * Retrieve Headers row array for Export
     *
     * @return array
     */
    protected function _getExportHeaders()
    {
        $row = parent::_getExportHeaders();
        $row = Mage::helper('orderexporter/config')->getCsvHeaders();
        return $row;
    }


    /**
     * Override parent method to avoid setting/getting filter param values from session/request so we export entire list
     * of orders
     * @param string $paramName
     * @param null $default
     * @return mixed|void
     */
    public function getParam($paramName, $default=null)
    {
        // If the setting to use grid filters is set in admin system configuration we will use the default filters for the grid.
        if(Mage::helper('orderexporter')->isGridFilterEnabled()){
            return parent::getParam($paramName,$default);
        }
        return $default;
    }

}