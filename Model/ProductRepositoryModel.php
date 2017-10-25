<?php

namespace OxCom\MagentoTopProducts\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Data\Collection;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection as Bestsellers;
use Magento\Store\Model\StoreManager;
use Magento\Review\Model\ResourceModel\Rating\Collection as Rating;
use OxCom\MagentoTopProducts\Model\ResourceModel\Rating\Option\Aggregated\Collection as RatingAggregated;
use OxCom\MagentoTopProducts\Api\ProductRepositoryInterface;
use OxCom\MagentoTopProducts\Api\ProductSearchCriteriaInterface;

/**
 * Class Repository
 *
 * @package OxCom\MagentoTopProducts\Model
 */
class ProductRepositoryModel implements ProductRepositoryInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection
     */
    protected $bestsellers;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $products;

    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Collection
     */
    protected $rating;

    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Collection
     */
    protected $ratingAggregated;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * Repository constructor.
     *
     * @param \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection                  $bestsellers
     * @param \Magento\Catalog\Model\ProductRepository                                          $products
     * @param \Magento\Store\Model\StoreManager                                                 $storeManager
     * @param \Magento\Review\Model\ResourceModel\Rating\Collection                             $rating
     * @param \OxCom\MagentoTopProducts\Model\ResourceModel\Rating\Option\Aggregated\Collection $ratingAggregated
     */
    public function __construct(
        Bestsellers $bestsellers,
        ProductRepository $products,
        StoreManager $storeManager,
        Rating $rating,
        RatingAggregated $ratingAggregated
    ) {
        $this->bestsellers      = $bestsellers;
        $this->products         = $products;
        $this->storeManager     = $storeManager;
        $this->rating           = $rating;
        $this->ratingAggregated = $ratingAggregated;
    }

    /**
     * Get items from report
     *
     * @api
     *
     * @param string                                                       $type Type of source
     * @param \OxCom\MagentoTopProducts\Api\ProductSearchCriteriaInterface $searchCriteria
     *
     * @return \OxCom\MagentoTopProducts\Api\Data\ProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList($type, ProductSearchCriteriaInterface $searchCriteria = null)
    {
        $allowed = [static::FILTER_TYPE_TOP_SELLING, static::FILTER_TYPE_TOP_FREE, static::FILTER_TYPE_TOP_RATED];
        $type    = mb_strtolower($type);

        if (empty($searchCriteria)) {
            $searchCriteria = new ProductSearchCriteria();
        }

        switch ($type) {
            case static::FILTER_TYPE_TOP_SELLING:
                $result = $this->getBestsellers('gt', $searchCriteria);
                break;

            case static::FILTER_TYPE_TOP_FREE:
                $result = $this->getBestsellers('eq', $searchCriteria);
                break;

            case static::FILTER_TYPE_TOP_RATED:
                $result = $this->getRatedProducts($searchCriteria);
                break;

            default:
                $allowed = implode(', ', $allowed);
                $phrase  = __('Requested type "%s" doesn\'t exist. Allowed: %s', $type, $allowed);
                throw new \Magento\Framework\Exception\InputException($phrase);
        }

        return $result;
    }

    /**
     * @param string                                                       $condition - price filter
     * @param \OxCom\MagentoTopProducts\Api\ProductSearchCriteriaInterface $searchCriteria
     *
     * @return \OxCom\MagentoTopProducts\Model\ProductSearchResults
     */
    protected function getBestsellers($condition, ProductSearchCriteriaInterface $searchCriteria)
    {
        $pageSize = (int)$searchCriteria->getPageSize();
        $page     = (int)$searchCriteria->getCurrentPage();

        $this->bestsellers
            ->clear()
            ->distinct(true)
            ->setPeriod($searchCriteria->getPeriod())
            ->setPageSize($pageSize)
            ->setCurPage($page)
            ->addStoreRestrictions($this->storeManager->getStore()->getId());

        $this->bestsellers->addFieldToFilter('product_price', [$condition => 0]);

        /** @var \Magento\Reports\Model\Item[] $items */
        $items = $this->bestsellers->walk(function ($item) {
            /** @var \Magento\Reports\Model\Item $item */
            $productId = $item->getData('product_id');
            $product   = $this->products->getById($productId);

            return $product;
        });

        $result = $this->process($items, $searchCriteria, $this->bestsellers->getSize());

        return $result;
    }

    /**
     * @param \OxCom\MagentoTopProducts\Api\ProductSearchCriteriaInterface $searchCriteria
     *
     * @return \OxCom\MagentoTopProducts\Model\ProductSearchResults
     */
    public function getRatedProducts(ProductSearchCriteriaInterface $searchCriteria)
    {
        $pageSize = (int)$searchCriteria->getPageSize();
        $page     = (int)$searchCriteria->getCurrentPage();
        $storeId  = (int)$this->storeManager->getStore()->getId();

        $this->ratingAggregated
            ->clear()
            ->setPageSize($pageSize)
            ->setCurPage($page)
            ->addFieldToFilter('store_id', ['eq' => $storeId]);

        $code = $searchCriteria->getRatingCode();
        if (!empty($code)) {
            $rating = $this->rating->getItemByColumnValue('rating_code', $code);

            if (!empty($rating)) {
                // there is something like we are searching
                $id = $rating->getData('rating_id');
                $this->ratingAggregated->addFieldToFilter('rating_id', ['eq' => $id]);
            }
        }

        $this->ratingAggregated
            ->addOrder('percent', Collection::SORT_ORDER_DESC)
            ->addOrder('vote_value_sum', Collection::SORT_ORDER_DESC);

        /** @var \Magento\Reports\Model\Item[] $items */
        $items = $this->ratingAggregated->walk(function ($item) {
            /** @var \Magento\Reports\Model\Item $item */
            $productId = $item->getData('entity_pk_value');
            $product   = $this->products->getById($productId);

            return $product;
        });

        $result = $this->process($items, $searchCriteria, $this->ratingAggregated->getSize());

        return $result;
    }

    /**
     * Prepare response
     *
     * @param                                                              $items
     * @param \OxCom\MagentoTopProducts\Api\ProductSearchCriteriaInterface $searchCriteria
     * @param                                                              $size
     *
     * @return \OxCom\MagentoTopProducts\Model\ProductSearchResults
     */
    protected function process($items, ProductSearchCriteriaInterface $searchCriteria, $size)
    {
        $result = new ProductSearchResults();
        $result->setItems($items)
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($size);

        return $result;
    }
}
