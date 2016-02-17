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

class AW_Ascurl_Model_Observer extends Mage_Sitemap_Model_Observer
{
    /**
     * @param object $observer
     */
    public function addFieldsToCmsMeta($observer)
    {
        $form = $observer->getForm();
        $fieldset = $form->getElement('meta_fieldset');

        $helper = Mage::helper('ascurl');

        $fieldset->addField('sitemap_include', 'select',
            array(
                'name'   => 'sitemap_include',
                'label'  => $helper->__('Include in Google Sitemap'),
                'values' => array(
                    array(
                        'value' => 0,
                        'label' => $helper->__('No'),
                    ),
                    array(
                        'value' => 1,
                        'label' => $helper->__('Yes'),
                    )
                ),
            )
        );

        $fieldset->addField('meta_robots', 'select',
            array(
                'name'   => 'meta_robots',
                'label'  => $helper->__('Meta Robots'),
                'values' => AW_Ascurl_Model_Source_Metarobots::toOptionArray()
            )
        );
        return $this;
    }

    /**
     * Save 3 params to aw_ascurl_cms table which interacts to cms_page table
     * @param <type> $observer 
     */
    public function cmsPageSaveAfter($observer)
    {
        $page = $observer->getObject();
        Mage::getModel('ascurl/cms')
            ->load($page->getPageId(), 'page_id')
            ->setPageId($page->getPageId())
            ->setSitemapInclude($page->getSitemapInclude())
            ->setMetaRobots($page->getMetaRobots())
            ->save()
        ;
        return $this;
    }

    /**
     * Add to page object 3 params from aw_ascurl_cms table
     * @param object $observer 
     */
    public function cmsPageLoadAfter($observer)
    {
        $page = $observer->getObject();
        $ascurlPage = Mage::getModel('ascurl/cms')->load($page->getPageId(), 'page_id');
        $page
            ->setSitemapInclude($ascurlPage->getSitemapInclude())
            ->setMetaRobots($ascurlPage->getMetaRobots())
        ;
        return $this;
    }

    /**
     * Add metarobots to the head of cms pages
     * @param object $observer
     */
    public function addCannonicalUrlToHead($observer)
    {
        try {
            $block = $observer->getBlock();
            $headBlock = $block->getLayout()->getBlock('head');
            $product = Mage::registry('product');
            if ($block instanceof Mage_Catalog_Block_Product_View && Mage::helper('ascurl')->useCanonicalUrl()
                && null !== $product
            ) {
                if (Mage::getEdition() == 'Enterprise' && version_compare(Mage::getVersion(), '1.13', '>=')) {
                    $headBlock->addLinkRel('canonical', Mage::helper('ascurl')->getProductCanonicalURL($product, false));
                    return $this;
                }

                $params = array('_ignore_category' => true);
                $headBlock->addLinkRel('canonical', $product->getUrlModel()->getUrl($product, $params));
                return $this;
            }

            $category = Mage::registry('current_category');
            if ($block instanceof Mage_Catalog_Block_Category_View && Mage::helper('ascurl')->useCanonicalUrl()
                && null !== $category
            ) {
                $headBlock->addLinkRel('canonical', $category->getUrl());
                return $this;
            }

            if ($block instanceof Mage_Cms_Block_Page && $block->getPage()->getMetaRobots()) {
                $headBlock
                    ->setRobots(AW_Ascurl_Model_Source_Metarobots::$metarobots[$block->getPage()->getMetaRobots()])
                ;
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
    
    public function pageLoadBefore($observer = null)
    {
        if (!Mage::helper('ascurl')->isModuleOutputEnabled()) {
            return $this;
        }

        $catalogRewriteNode = Mage::getConfig()->getNode('global/models/catalog/rewrite');
        $dCatalogRewriteNode = Mage::getConfig()->getNode('global/models/catalog/d_rewrite_product_url/product_url');

        $catalogRewriteNode->appendChild($dCatalogRewriteNode);

        $sitemapRewriteNode = Mage::getConfig()->getNode('global/models/sitemap/rewrite');
        $dSitemapRewriteNode = Mage::getConfig()->getNode('global/models/sitemap/d_rewrite_sitemap/sitemap');

        $sitemapRewriteNode->appendChild($dSitemapRewriteNode);

        $sitemapMysql4CpRewriteNode = Mage::getConfig()->getNode('global/models/sitemap_mysql4/rewrite');
        $dSitemapMysql4CpRewriteNode = Mage::getConfig()
            ->getNode('global/models/sitemap_mysql4/d_rewrite_sitemap_mysql4')
        ;

        foreach ($dSitemapMysql4CpRewriteNode->children() as $dnode) {
            $sitemapMysql4CpRewriteNode->appendChild($dnode);
        }
        return $this;
    }

    public function scheduledGenerateSitemaps($schedule)
    {
        $this->pageLoadBefore();
        return parent::scheduledGenerateSitemaps($schedule);
    }
}