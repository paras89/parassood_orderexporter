<?php


/**
 * Orderexporter observer model
 *
 * @category    Parassood
 * @package     Parassood_Orderexporter
 * @author      Paras Sood
 */
class Parassood_Orderexporter_Model_Observer
{


    /**
     *
     * Hook for Event adminhtml_block_html_before
     * @param $observer
     * @return $this
     */
    public function addExportTypeToGrid($observer)
    {
        if(!Mage::getStoreConfig('orderexporter/orderexport_settings/active')){
            // The hourly export module is disabled from system configuration.
            return $this;
        }
        $block = $observer->getBlock();
        if(!$block instanceof Mage_Adminhtml_Block_Sales_Order_Grid){
            return $this;
        }
        // Add our custom export type to Admin Sales Order Grid.
        $block->addExportType('*/orderexport/exportCustomCsv', Mage::helper('sales')->__('Hourly Order CSV Export'));
        return $this;
    }

    /**
     * Hook for event-sales_order_place_after
     * We register a purchase so we can mark it in the hourly order export to keep it from being exported again.
     * @param $observer
     */
    public function registerNewOrder($observer)
    {
        $order = $observer->getOrder();
        $exportOrder = Mage::getModel('orderexporter/exportorders');
        $exportOrder->setOrderId($order->getId());
        $exportOrder->save();
        return $this;

    }

}