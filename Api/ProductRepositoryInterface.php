<?php

namespace OxCom\MagentoTopProducts\Api;

/**
 * Interface ProductRepositoryInterface
 *
 * @api
 * @package OxCom\MagentoTopProducts\Api
 */
interface ProductRepositoryInterface
{
    const FILTER_TYPE_TOP_SELLING = 'selling';
    const FILTER_TYPE_TOP_FREE    = 'free';
    const FILTER_TYPE_TOP_RATED   = 'rated';

    /**
     * @param string                                                       $type Type of source
     * @param \OxCom\MagentoTopProducts\Api\ProductSearchCriteriaInterface $searchCriteria
     *
     * @return \OxCom\MagentoTopProducts\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList($type, ProductSearchCriteriaInterface $searchCriteria = null);
}
