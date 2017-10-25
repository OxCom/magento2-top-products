<?php

namespace OxCom\MagentoTopProducts\Model\ResourceModel\Rating\Option;

/**
 * Class Aggregated
 *
 * @package OxCom\MagentoTopProducts\Model\ResourceModel\Rating\Option
 */
class Aggregated extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Rating entity resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rating_option_vote_aggregated', 'primary_id');
    }
}
