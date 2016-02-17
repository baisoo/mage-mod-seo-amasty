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

require_once 'Mage/Adminhtml/controllers/Catalog/ProductController.php';

class AW_Ascurl_Adminhtml_IndexController extends Mage_Adminhtml_Catalog_ProductController
{
    /**
     * Get categories tree node AJAX
     */
    public function categoriesJsonAction()
    {
        $this->_initProduct();
        $_response = $this->getLayout()
            ->createBlock('ascurl/adminhtml_catalog_product_edit_tab_ascurl')
            ->setOwnerTreeId(AW_Ascurl_Block_Adminhtml_Catalog_Product_Edit_Tab_Ascurl::OWNER_TREE_ID)
            ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        ;
        $this
            ->getResponse()
            ->setBody($_response)
        ;
    }
}