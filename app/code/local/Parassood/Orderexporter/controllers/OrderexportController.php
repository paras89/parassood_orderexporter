<?php


class Parassood_Orderexporter_OrderexportController extends Mage_Adminhtml_Controller_Action
{

   public function exportCustomCsvAction()
   {
       $fileName   = 'hourly_orders.csv';
       $grid = $this->getLayout()->createBlock('orderexporter/adminhtml_sales_order_grid');
       $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());

   }

   public function viewxmlAction()
   {
       $this->getResponse()->setHeader('Content-Type','text/xml');
       $xmlResponse = Mage::helper('orderexporter/config')->getDefaultXml();
       $this->getResponse()->setBody($xmlResponse);
       return $this;
   }

}
