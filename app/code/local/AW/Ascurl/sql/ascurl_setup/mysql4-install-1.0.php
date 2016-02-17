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

$installer = $this;
$installer->startSetup();

$setup = $this;

$pageIdDescription = $setup->getConnection()->query("DESCRIBE {$this->getTable('cms/page')} page_id")->fetch();
if (isset($pageIdDescription['Type'])) {
    $pageIdType = $pageIdDescription['Type'];
} else {
    Mage::throwException('page_id field doesn\'t found at cms_page table');
}

$setup->addAttribute('catalog_product', AW_Ascurl_Block_Adminhtml_Catalog_Product_Edit_Tab_Ascurl::OWNER_TREE_ID, array(
            'input_renderer'    => 'ascurl/adminhtml_catalog_product_edit_tab_ascurl_categoryascHidden',
            'label'             => 'Canonical URL',
            'type'              => 'int',
            'input'             => 'hidden',
            'default'           => '',
            'class'             => '',
            'source'            => '',
            'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'visible'           => true,
            'required'          => false,
            'user_defined'      => false,
            'searchable'        => false,
            'filterable'        => false,
            'comparable'        => false,
            'visible_on_front'  => false,
            'unique'            => false,
       ));
 
$setup->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('ascurl/cms')} (
      `id` int(10) unsigned NOT NULL auto_increment,
      `page_id` $pageIdType NOT NULL,
      `sitemap_include` int(11) NOT NULL,
      `meta_robots` varchar(5) NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB;

    ALTER TABLE {$this->getTable('ascurl/cms')}
      ADD CONSTRAINT `fk_awasc` FOREIGN KEY (`page_id`) REFERENCES {$this->getTable('cms/page')} (`page_id`) ON DELETE CASCADE;
");

foreach (Mage::getModel('cms/page')->getCollection() as $item) {
    try {
        Mage::getModel('ascurl/cms')
            ->setPageId($item->getPageId())
            ->setSitemapInclude(1)
            ->save()
        ;
    } catch (Exception $ex) {
        continue;
    }
}
$installer->endSetup();