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

class AW_Ascurl_Model_Source_Metarobots
{
    public static $metarobots = array(
        'IF'   => 'INDEX,FOLLOW',
        'NIF'  => 'NOINDEX,FOLLOW',
        'INF'  => 'INDEX,NOFOLLOW',
        'NINF' => 'NOINDEX,NOFOLLOW'
    );

    public static function toOptionArray()
    {
        $res = array();
        foreach (self::$metarobots as $value => $label) {
            $res[] = array(
                'value' => $value,
                'label' => Mage::helper('ascurl')->__($label)
            );
        }
        return $res;
    }
}