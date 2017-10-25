<?php

namespace OxCom\MagentoTopProducts\Api\Data;

/**
 * Search results interface.
 *
 * @api
 * @package OxCom\MagentoTopProducts\Api\Data
 */
interface ProductSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \OxCom\MagentoTopProducts\Api\Data\ProductInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set collection items.
     *
     * @param \OxCom\MagentoTopProducts\Api\Data\ProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
