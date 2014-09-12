<?php


class Parassood_Orderexporter_OrderexportController extends Mage_Adminhtml_Controller_Action
{

   public function exportCustomCsvAction()
   {
       $fileName   = 'hourly_orders.csv';
       $grid       = $this->getLayout()->createBlock('orderexporter/adminhtml_sales_order_grid');
       $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());

   }

}
