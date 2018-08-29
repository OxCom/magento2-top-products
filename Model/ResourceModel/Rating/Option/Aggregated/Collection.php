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
     * @var string
     */
    protected $_idFieldName = 'primary_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'oxcom_rating_aggregated_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'rating_aggregated_collection';

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
