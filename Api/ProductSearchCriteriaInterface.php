<?php

namespace OxCom\MagentoTopProducts\Api;

/**
 * Search criteria interface.
 *
 * @package OxCom\MagentoTopProducts\Api
 */
interface ProductSearchCriteriaInterface
{
    const PERIOD_YEARLY  = 'yearly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_DAILY   = 'daily';

    /**
     * Get a list of filter groups.
     *
     * @return \Magento\Framework\Api\Search\FilterGroup[]
     */
    public function getFilterGroups();

    /**
     * Set a list of filter groups.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup[] $filterGroups
     * @return $this
     */
    public function setFilterGroups(array $filterGroups = null);

    /**
     * Get page size.
     *
     * @return int|null
     */
    public function getPageSize();

    /**
     * Set page size.
     *
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize($pageSize);

    /**
     * Get current page.
     *
     * @return int|null
     */
    public function getCurrentPage();

    /**
     * Set current page.
     *
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage($currentPage);

    /**
     * @param string $period Type of period
     *
     * @return $this
     */
    public function setPeriod($period = null);

    /**
     * @return string
     */
    public function getPeriod();

    /**
     * @param string $code Raging code filter
     *
     * @return $this
     */
    public function setRatingCode($code = null);

    /**
     * @return string
     */
    public function getRatingCode();
}
