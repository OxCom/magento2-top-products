<?php

namespace OxCom\MagentoTopProducts\Model\ResourceModel\Rating\Option\Aggregated;

/**
 * Aggregated rating votes collection
 *
 * @package OxCom\MagentoTopProducts\Model\ResourceModel\Rating\Option\Aggregated
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \OxCom\MagentoTopProducts\Model\Rating\Option\Aggregated::class,
            \OxCom\MagentoTopProducts\Model\ResourceModel\Rating\Option\Aggregated::class
        );
    }
}
