<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ascurl
 * @version    1.3.7
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Ascurl_Model_Catalog_Product_Url extends Mage_Catalog_Model_Product_Url
{
    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $params
     * @return string
     */
    public function getUrl(Mage_Catalog_Model_Product $product, $params = array())
    {
        if (!Mage::helper('ascurl')->useCanonicalUrl()
            || (AW_All_Helper_Versions::getPlatform() == AW_All_Helper_Versions::CE_PLATFORM
                && version_compare(Mage::getVersion(), '1.8', '>=')
            )
        ) {
            return parent::getUrl($product, $params);
        }

        $routePath      = '';
        $routeParams    = $params;

        $storeId    = $product->getStoreId();
        $categoryId = null;
        if (!isset($params['_ignore_category']) && $product->getCategoryId()
            && !$product->getDoNotUseCategoryId()
        ) {
            $categoryId = $product->getCategoryId();
        }

        if (isset($params['_ignore_category'])) {
            unset($params['_ignore_category']);
        }

        if (!$categoryId) {
            $product = Mage::getModel('catalog/product')->load($product->getId());
            $categoryId = Mage::helper('ascurl')->getCategoryIdAsc($product);
        }

        if ($product->hasUrlDataObject()) {
            $requestPath = $product->getUrlDataObject()->getUrlRewrite();
            $routeParams['_store'] = $product->getUrlDataObject()->getStoreId();
        }

        if (!$product->hasUrlDataObject()) {
            $requestPath = $product->getRequestPath();
            if (empty($requestPath)) {
                $idPath = sprintf('product/%d', $product->getEntityId());
                if ($categoryId) {
                    $idPath = sprintf('%s/%d', $idPath, $categoryId);
                }
                $rewrite = $this->getUrlRewrite();
                $rewrite
                    ->setStoreId($storeId)
                    ->loadByIdPath($idPath)
                ;
                if ($rewrite->getId()) {
                    $requestPath = $rewrite->getRequestPath();
                    $product->setRequestPath($requestPath);
                }
            }
        }

        if (isset($routeParams['_store'])) {
            $storeId = Mage::app()->getStore($routeParams['_store'])->getId();
        }

        if ($storeId != Mage::app()->getStore()->getId()) {
            $routeParams['_store_to_url'] = true;
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'catalog/product/view';
            $routeParams['id']  = $product->getId();
            $routeParams['s']   = $product->getUrlKey();
            if ($categoryId) {
                $routeParams['category'] = $categoryId;
            }
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = array();
        }

        return $this
            ->getUrlInstance()
            ->setStore($storeId)
            ->getUrl($routePath, $routeParams)
        ;
    }

    protected function _getCategoryIdForUrl($product, $params)
    {
        $categoryId = parent::_getCategoryIdForUrl($product, $params);
        if (Mage::helper('ascurl')->useCanonicalUrl() && null === $categoryId) {
            $product = Mage::getModel('catalog/product')->load($product->getId());
            $categoryId = Mage::helper('ascurl')->getCategoryIdAsc($product);
        }
        return $categoryId;
    }
}