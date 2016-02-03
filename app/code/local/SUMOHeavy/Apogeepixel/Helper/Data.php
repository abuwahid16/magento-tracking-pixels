<?php
/**
 * SUMOHeavy_Apogeepixel
 *
 * @category   SUMOHeavy
 * @package    SUMOHeavy_Apogeepixel
 * @copyright  Copyright (c) 2015 Blades (http://www.blades.com)
 * @author     Abu Wahid <support@sumoheavy.com>
 */
?>

<?php

class SUMOHeavy_Apogeepixel_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CART_QTY = '{{qty}}';
    const ORDERED_QTY = '{{qty}}';
    const REVENUE = '{{revenue}}';
    const BRAND = '{{brand}}';
    const PRODUCT_NAME = '{{prodName}}';
    const ORDER_ID = '{{orderId}}';
    const PRODUCT_CAT = '{{prodCat}}';
    const PRODUCT_SUB_CAT = '{{prodSubCat}}';

    const HOMEPAGE_CONFIG_KEY = 'homepage';
    const LOGIN_CONFIG_KEY = 'login';
    const CART_CONFIG_KEY = 'cart';
    const SALES_CONFIG_KEY = 'sales';
    const PRODUCT_CONFIG_KEY = 'product';

    const APOGEE_MODULE_NAME = 'sumoheavy_apogeepixel';
    const APOGEE_CONFIG_SECTION = 'pixels';



    /**
     * @Returns config value
     * @param $field
     * @param string $section
     * @param string $module
     * @return mixed
     */
    public function getConfigValue($field, $section = self::APOGEE_CONFIG_SECTION, $module = self::APOGEE_MODULE_NAME)
    {
        return Mage::getStoreConfig($module . '/' . $section . '/' . $field);
    }

    /**
     * @Check if the module is enabled
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfigValue('enabled');
    }



    /**
     * @Returns product brand
     * @param $productId
     * @return mixed
     */
    public function getProductBrand($productId)
    {
        $_product = $this->getProduct($productId);
        return $_product->getResource()->getAttribute('brand')->getFrontend()->getValue($_product);
    }

    /**
     * @Load product object by product id
     * @param $productId
     * @return product object
     */
    public function getProduct($productId)
    {
        return Mage::getModel('catalog/product')->load($productId);
    }


    /**
     * @Retrieve Quote cart grand total
     * @return mixed
     */
    public function getQuoteGrandTotal()
    {
        $_totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
        return $_totals["grand_total"]->getValue();
    }

    /**
     * @Returns Current placed order
     * @return mixed
     */
    public function getCheckoutOrder()
    {
        return Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
    }



    /**
     * @Format adPixel for each type
     * @param $type
     * @param null $additionalInfo
     * @return mixed|string|void
     */
    public function getFormattedPixel($type, $_item, $additionalInfo = null)
    {
        $pixelData = '';

        switch ($type) {
            case self::SALES_CONFIG_KEY:
                $pixelData = $this->getSalesPixelData($_item, $additionalInfo);
                break;

            case self::CART_CONFIG_KEY:
                $pixelData = $this->getCartPixelData($_item);
                break;

            case self::PRODUCT_CONFIG_KEY:
                $pixelData = $this->getProductPixelData($_item);
                break;
            default:
                break;
        }

        return $this->replaceVars($pixelData, $this->getConfigValue($type));
    }

    /**
     * @Returns sales pixel array
     * @param $_item
     * @param $_additionalInfo
     * @return array
     */
    protected function getSalesPixelData($_item, $_additionalInfo)
    {
        return array(
            self::ORDERED_QTY => $_item->getQtyOrdered(),
            self::REVENUE => $this->getCheckoutOrder()->getGrandTotal(),
            self::BRAND => $this->getProductBrand($_item->getId()),
            self::PRODUCT_NAME => $_item->getName(),
            self::ORDER_ID => $_additionalInfo['orderId']
        );
    }


    /**
     * @Returns Cart pixel array
     * @param $_item
     * @return array
     */
    protected function getCartPixelData($_item)
    {
        return array(
            self::CART_QTY => $_item->getQty(),
            self::REVENUE => $this->getQuoteGrandTotal(),
            self::BRAND => $this->getProductBrand($_item->getId()),
            self::PRODUCT_NAME => $_item->getName()
        );
    }


    /**
     * @Returns Product pixel array
     * @param $_item
     * @return array
     */
    protected function getProductPixelData($_item)
    {
        return array(
            self::PRODUCT_CAT => $_item->getCategory()->getParentCategory()->getName(),
            self::PRODUCT_SUB_CAT =>  $_item->getCategory()->getName()
        );
    }


    /**
     * @Replace pixel variable with dynamic value
     * @param $pixelData
     * @param $_configPixel
     * @return string
     */
    private function replaceVars($pixelData, $_configPixel)
    {
        foreach($pixelData as $key => $pixelDataValue) {
            $_configPixel = str_replace($key, $pixelDataValue, $_configPixel);
        }
        return $_configPixel;
    }



}