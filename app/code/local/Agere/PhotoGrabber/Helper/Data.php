<?php
/**
 * @package Agere_PhotoGrabber.php
 * @author Vlad Kozak <vk@agere.com.ua>
 */
class Agere_PhotoGrabber_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * This function starts find SKU from Database
     */
    public function syncImages() {

        /** @var Mage_Catalog_Helper_Product_Flat $helper */
        $process = Mage::helper('catalog/product_flat')->getProcess();
        $status = $process->getStatus();
        $process->setStatus(Mage_Index_Model_Process::STATUS_RUNNING);

        // Fetch attribute set id by attribute set name
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $attributeSetId = Mage::getModel('eav/entity_attribute_set')
            ->load('Oodji', 'attribute_set_name')
            ->getAttributeSetId();

        $website = Mage::getResourceModel('core/website_collection')
            ->addFieldToFilter('code', 'oodji')
            ->getFirstItem();

        $storeId = Mage::app()->getStore(Mage::app()->getWebsite($website)->getDefaultGroup()->getDefaultStoreId())->getId();

        // Load product model collection filtered by attribute set id
        $products = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId($storeId)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToFilter('type_id', 'configurable')
            ->addFieldToFilter('attribute_set_id', $attributeSetId)
            ->addFieldToFilter('status', 2);

        $process->setStatus($status);

        // Process your product collection as per your bussiness logic
        /** @var Agere_PhotoGrabber_Helper_Grabber $grab */
        $grab = Mage::helper('photoGrabber/grabber');

        foreach($products as $product){
            $grab->searchProduct($product->getSku());
        }
    }

}