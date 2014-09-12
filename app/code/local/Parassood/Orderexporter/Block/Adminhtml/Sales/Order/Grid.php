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
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection->addHourlyFilter();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id',
                            'data-column' => 'action',
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                ));
        }

        return parent::_prepareColumns();
    }


    /**
     * Write item data to csv export file
     *
     * @param Varien_Object $item
     * @param Varien_Io_File $adapter
     */
    protected function _exportCsvItem(Varien_Object $order, Varien_Io_File $adapter)
    {
        $orderItems = $order->getAllItems() ;
        $row = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($order);
            }
        }
        $row = $this->_exportCsvOrderWithItem($row,$orderItems,$adapter);

    }

    /**
     * Retrieve Headers row array for Export
     *
     * @return array
     */
    protected function _getExportHeaders()
    {
        $row = parent::_getExportHeaders();
        //add more export headers
        return $row;

    }

    /**
     * Export CSV order item and child order items.
     * @param $row
     * @param $orderItems
     * @param Varien_Io_File $adapter
     * @return $this
     */
    protected function _exportCsvOrderWithItem($row,$orderItems, Varien_Io_File $adapter)
    {

        $originalRow = $row;
        foreach($orderItems as $item){
            $row = $originalRow;
            if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE && !is_null($item->getParentItemId())){
                //Take data only from configurable/bundled order item unless parent item is not present.
                continue;
            }
            $childrenItems = $item->getChildrenItems();
            if(count($childrenItems)){

                foreach($childrenItems as $child){
                    $row[] = $child->getSku(); //$this->getItemSku($child);
                    if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
                      // In case of configurable-simple product relation take price from configurable product
                      // Simple product has it's own price set.
                        $row[] = $item->getPrice() + $child->getPrice();
                    }else{
                        $row[] = $child->getPrice();
                    }
                    $row[] = $child->getDiscountAmount();
                    $row[] =  $child->getQtyOrdered();
                    $adapter->streamWriteCsv($row);
                    $row = $originalRow;
                }
              //For $item configurable/bundle product we have written the child items to the csv, so continue.
                continue;

            }
            $row[] = $item->getSku();
            $row[] = $item->getPrice();
            $row[] = $item->getDiscountAmount();
            $row[] =  $item->getQtyOrdered();
            $adapter->streamWriteCsv($row);
        }

        return $this;
    }

    /**
     * Returns the sku of the given item dependant on the product type.
     *
     * @param Mage_Sales_Model_Order_Item $item The item to return info from
     * @return String The sku
     */
    protected function getItemSku($item)
    {
        if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            return $item->getProductOptionByCode('simple_sku');
        }
        return $item->getSku();
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