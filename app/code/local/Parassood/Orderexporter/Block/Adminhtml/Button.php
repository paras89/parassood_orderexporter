<?php


class Parassood_Orderexporter_Block_Adminhtml_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        //
        $url = Mage::helper("adminhtml")->getUrl("adminhtml/orderexport/viewxml");

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('View Sample XML')
            ->setOnClick("window.open('$url');")
            ->toHtml();

        return $html;
    }
}