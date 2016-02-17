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

class AW_Ascurl_Block_Adminhtml_Catalog_Product_Edit_Tab_Ascurl
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    const OWNER_TREE_ID = 'category_id_asc';

    protected $_selectedNodesPositions;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_ascurl/catalog/product/edit/ascurl.phtml');
    }

    /*
     * Returns tab label
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Canonical URL');
    }

    public function getSelectedNodesPositions()
    {
        return $this->_selectedNodesPositions;
    }

    /*
     * Returns tab title
     * @return String
     */
    public function getTabTitle()
    {
        return $this->__('Canonical URL');
    }

    /*
     * Check if tab can be displayed
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /*
     * Check if tab is hidden
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Return url for left tree branches load (canonocal URL tab)
     * @param bool $expanded
     * @return string
     */
    public function getLoadTreeUrl($expanded = null)
    {
        return $this->getUrl('*/*/categoriesJson', array('_current' => true));
    }

    /**
     * return Category ids for tree to expand tree until them
     * @return array
     */
    protected function getCategoryIds()
    {
        $_categoryIds = $this->getProduct()->getCategoryIds();
        if ('category_id_asc' == $this->getOwnerTreeId()) {
            $_categoryIds = array($this->getProduct()->getCategoryIdAsc());
        }
        return $_categoryIds;
    }

    /**
     * Return url for right tree branches load (canonocal URL tab)
     * @param bool $expanded
     * @return string
     */
    public function getAscurlLoadTreeUrl($expanded = null)
    {
        return $this->getUrl('ascurl_admin/adminhtml_index/categoriesJson', array('_current' => true));
    }

    /**
     * Save current selected nodes into a string
     */
    public function saveSelectedIds()
    {
        $selectedIds = array();
        foreach ($this->_selectedNodes as $selectedNode) {
            if ($selectedNode) {
                $selectedIds[] = $selectedNode->getEntityId();
            }
        }
        $this->_selectedNodesPositions = implode(',', $selectedIds);
    }

    // compatibility with 1.5.0.1/1.10.0.1
    public function getSelectedCategoriesPathIds($rootId = false)
    {
        $ids = array();
        $collection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('entity_id', array('in' => $this->getCategoryIds()))
        ;
        foreach ($collection as $item) {
            if ($rootId && !in_array($rootId, $item->getPathIds())) {
                continue;
            }
            foreach ($item->getPathIds() as $id) {
                if (!in_array($id, $ids)) {
                    $ids[] = $id;
                }
            }
        }
        return $ids;
    }

    protected function _isParentSelectedCategory($node)
    {
        foreach ($this->_getSelectedNodes() as $selected) {
            if ($selected) {
                $pathIds = explode('/', $selected->getPathId());
                if (in_array($node->getId(), $pathIds)) {
                    return true;
                }
            }
        }
        return false;
    }
}