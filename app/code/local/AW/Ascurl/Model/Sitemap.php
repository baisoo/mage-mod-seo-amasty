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

class AW_Ascurl_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{
    const MAX_URL_COUNT = 'sitemap/advancedseo/max_url_quantity_per_file';
    const MAX_FILE_SIZE = 'sitemap/advancedseo/max_file_size';
    const USE_IMAGES = 'sitemap/advancedseo/use_images';

    const URLSET = 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';

    protected $_fileNumber = 0;
    protected $_currentUrlCount = 0;
    protected $_currentFileSize = 0;
    protected $_maxUrlCount;
    protected $_maxFileSize;
    protected $_filenamesForIndexSitemap = array();
    protected $_io;

    /**
     * Create new xml file with several begin tags
     * @return Varien_Io_File 
     */
    public function createFile()
    {
        $fileToCreate = $this->getSitemapFilename();

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));

        if ($this->_fileNumber) {
            $fileToCreate = Mage::helper('ascurl')
                ->insertStringToFilename($this->getSitemapFilename(), '_'.$this->_fileNumber)
            ;
        }

        if ($io->fileExists($fileToCreate) && !$io->isWriteable($fileToCreate)) {
            Mage::throwException(
                Mage::helper('sitemap')->__(
                    'File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.',
                    $fileToCreate,
                    $this->getPath()
                )
            );
        }
        $io->streamOpen($fileToCreate);
        $this->_filenamesForIndexSitemap[] = $fileToCreate;
        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset '.self::URLSET.'>');
        $this->_io = $io;
        return $this;
    }

    /**
     * Create additional xml index file with links to other xml files (if number of them more than 1)
     */
    public function createIndexSitemapFile()
    {
        if (sizeof($this->_filenamesForIndexSitemap) > 1) {
            $io = new Varien_Io_File();
            $io->setAllowCreateFolders(true);
            $io->open(array('path' => $this->getPath()));
            $fileToCreate = Mage::helper('ascurl')->insertStringToFilename($this->getSitemapFilename(), '_index');

            if ($io->fileExists($fileToCreate) && !$io->isWriteable($fileToCreate)) {
                Mage::throwException(
                    Mage::helper('sitemap')->__(
                        'File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.',
                        $fileToCreate,
                        $this->getPath()
                    )
                );
            }
            $io->streamOpen($fileToCreate);
            $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
            $io->streamWrite('<sitemapindex '.self::URLSET.'>');

            $storeId  = $this->getStoreId();
            $baseUrl  = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $date     = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
            $path     = $this->getSitemapPath();
            $fullPath = preg_replace('/(?<=[^:])\/{2,}/', '/', $baseUrl.$path);

            foreach ($this->_filenamesForIndexSitemap as $item) {
                $xml = sprintf(
                    '<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>',
                    htmlspecialchars($fullPath . $item),
                    $date
                );
                $io->streamWrite($xml);
            }
            $io->streamWrite('</sitemapindex>');
            $io->streamClose();
        }
        return $this;
    }

    /**
     * Close xml file. Set to zero current url and current file size counters. Increments fileNumber.
     * @param Varien_Io_File $io
     */
    public function closeFile()
    {
        $this->_io->streamWrite('</urlset>');
        $this->_io->streamClose();
        $this->_fileNumber++;
        $this->_currentUrlCount = $this->_currentFileSize = 0;
        return $this;
    }

    /**
     * Returns true if count of urls in xml more than count of urls in ext configuration
     * @return bool
     */
    public function checkUrlCount()
    {
        $_result = false;
        if (is_null($this->_maxUrlCount)) {
            $this->_maxUrlCount = Mage::getStoreConfig(self::MAX_URL_COUNT, $this->getStoreId());
        }

        if ($this->_maxUrlCount > 0 && $this->_currentUrlCount > $this->_maxUrlCount) {
            return true;
        }
        return $_result;
    }

    /**
     * Returns true if xml file size more than max size at ext configuration
     * @param int $sizeAdd
     * @return bool
     */
    public function checkFileSize($sizeAdd)
    {
        $_result = false;
        if (is_null($this->_maxFileSize)) {
            $this->_maxFileSize = Mage::getStoreConfig(self::MAX_FILE_SIZE, $this->getStoreId());
        }

        if ($this->maxFileSize > 0 && $this->_currentFileSize > $this->_maxFileSize*1024-450) {
            return true;
        }
        return $_result;
    }

    protected function updateCounters($sizeAdd)
    {
        $this->_currentUrlCount ++;
        $this->_currentFileSize += $sizeAdd;
        return $this;
    }
    /**
     * Generate XML file
     *
     * @return Mage_Sitemap_Model_Sitemap
     */
    public function generateXml()
    {
        $this->createFile();

        $storeId = $this->getStoreId();
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        $useImages = Mage::getStoreConfig(self::USE_IMAGES, $storeId);

        /**
         * Generate categories sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/category/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        if (Mage::getEdition() == 'Enterprise' && version_compare(Mage::getVersion(), '1.13', '>=')) {
            $categories = new Varien_Object();
            $categories->setItems($collection);
            Mage::dispatchEvent('sitemap_categories_generating_before', array(
                'collection' => $categories
            ));
            $items = $categories->getItems();
        } else {
            $items = $collection;
        }

        foreach ($items as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $_im = Mage::getModel('catalog/category')->load($item->getId())->getImageUrl();
            if ($_im && $useImages) {
                $xml = sprintf(
                    '<url><loc>%s</loc><lastmod>%s</lastmod><image:image><image:loc>%s</image:loc></image:image><changefreq>%s</changefreq><priority>%.1f</priority></url>'. "\n",
                    htmlspecialchars($baseUrl . $item->getUrl()),
                    $date,
                    $_im,
                    $changefreq,
                    $priority
                );
            }
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);

        /**
         * Generate products sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/product/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);

        if ($useImages) {
            $_info = Mage::helper('ascurl')->startEmulation($storeId);
        }
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );

            $_im = false;
            if ($useImages) {
                try {
                    $_im = Mage::getModel('catalog/product')->load($item->getId())->getImageUrl();
                } catch (Exception $e) {
                    throw new Mage_Core_Exception(
                        Mage::helper('ascurl')->__(
                            'Please specify System->Configuration->Catalog->Product Image Placeholders or be sure'
                                . ' that is a default holder image for product attribute \'image\' exists by path'
                                . ' {Skin Base Dir}/images/catalog/product/placeholder/image.jpg'
                        )
                    );
                }
            }

            if ($useImages && $_im) {
                $xml = sprintf(
                    '<url><loc>%s</loc><lastmod>%s</lastmod><image:image><image:loc>%s</image:loc></image:image><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($baseUrl . $item->getUrl()),
                    $date,
                    $_im,
                    $changefreq,
                    $priority
                );
            }
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);

        if ($useImages) {
            Mage::helper('ascurl')->stopEmulation($_info);
        }
        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/page/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);

        Mage::dispatchEvent('sitemap_add_xml_block_to_the_end', array('sitemap_object' => $this));

        $this
            ->closeFile()
            ->createIndexSitemapFile()
            ->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'))
            ->save()
        ;
        return $this;
    }

    public function sitemapFileAddLine($xml)
    {
        $xmlLength = strlen($xml);
        $this->updateCounters($xmlLength);
        if ($this->checkUrlCount() || $this->checkFileSize($xmlLength)) {
            $this
                ->closeFile()
                ->createFile()
                ->updateCounters($xmlLength)
            ;
        }
        $this->_io->streamWrite($xml);
        return $this;
    }
}