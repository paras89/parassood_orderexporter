<?php


class Parassood_Orderexporter_Block_Adminhtml_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Return button HTML for view sample configuration XML button in sytem configuration.
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = Mage::helper("adminhtml")->getUrl("adminhtml/orderexport/viewxml");

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('View Sample Configuration XML')
            ->setOnClick("window.open('$url');")
            ->toHtml();

        return $html;
    }
}