<?php

namespace OxCom\MagentoTopProducts\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Data\Collection;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection as Bestsellers;
use Magento\Store\Model\StoreManager;
use Magento\Review\Model\ResourceModel\Rating\Collection as Rating;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
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
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

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
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $metadataService;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Repository constructor.
     *
     * @param \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection                  $bestsellers
     * @param \Magento\Catalog\Model\ProductRepository                                          $products
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection                           $productCollection
     * @param \Magento\Store\Model\StoreManager                                                 $storeManager
     * @param \Magento\Review\Model\ResourceModel\Rating\Collection                             $rating
     * @param \OxCom\MagentoTopProducts\Model\ResourceModel\Rating\Option\Aggregated\Collection $ratingAggregated
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface                          $metadataServiceInterface
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                                      $searchCriteriaBuilder
     */
    public function __construct(
        Bestsellers $bestsellers,
        ProductRepository $products,
        ProductCollection $productCollection,
        StoreManager $storeManager,
        Rating $rating,
        RatingAggregated $ratingAggregated,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataServiceInterface,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->bestsellers           = $bestsellers;
        $this->products              = $products;
        $this->productCollection     = $productCollection;
        $this->storeManager          = $storeManager;
        $this->rating                = $rating;
        $this->ratingAggregated      = $ratingAggregated;
        $this->metadataService       = $metadataServiceInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        $allowed = [
            ProductSearchCriteriaInterface::PERIOD_DAILY,
            ProductSearchCriteriaInterface::PERIOD_MONTHLY,
            ProductSearchCriteriaInterface::PERIOD_YEARLY
        ];

        $period = $searchCriteria->getPeriod();
        if (!\in_array($period, $allowed, true)) {
            $period = ProductSearchCriteriaInterface::PERIOD_YEARLY;
        }

        $from = new \DateTime();
        $to   = new \DateTime();

        $from->setTime(0, 0, 0, 0);
        $to->setTime(0, 0, 0, 0);

        switch ($period) {
            case ProductSearchCriteriaInterface::PERIOD_YEARLY:
                // from beggining of current year till the end of the current year
                $from->setDate($from->format('Y'), 1, 1);

                $to->modify('+1 year');
                $to->setDate($to->format('Y'), 1, 1);
                $to->modify('-1 day');

                $range = [
                    'from' => $from->format('Y-m-d 00:00:00'),
                    'to'   => $to->format('Y-m-d 23:59:59'),
                ];
                break;

            case ProductSearchCriteriaInterface::PERIOD_MONTHLY:
                // from beggining of current month till the end of the current moth
                $from->setDate($from->format('Y'), $from->format('m'), 1);

                $to->setDate($to->format('Y'), $to->format('m'), 1);
                $to->modify('+1 month');
                $to->modify('-1 day');

                $range = [
                    'from' => $from->format('Y-m-d 00:00:00'),
                    'to'   => $to->format('Y-m-d 23:59:59'),
                ];
                break;

            case ProductSearchCriteriaInterface::PERIOD_DAILY:
            default:
                // from beggining of current day till the end of the current day
                $range = [
                    'from' => $from->format('Y-m-d 00:00:00'),
                    'to'   => $to->format('Y-m-d 23:59:59'),
                ];
                break;
        }

        $storeId = (int)$this->storeManager->getStore()->getId();
        $this->prepareProductCollection($searchCriteria);

        $joinCond = [
            'store_id'      => ['eq' => $storeId],
            'product_price' => [$condition => 0],
        ];

        $table = $this->bestsellers->getTableByAggregationPeriod($period);
        $this->productCollection->joinTable(
            ['b' => $table],
            'product_id = entity_id',
            [
                'product_price' => 'product_price',
                'period'        => 'period',
                'rating_pos'    => 'rating_pos',
            ],
            $joinCond
        );

        $this->productCollection
            ->addFieldToFilter('period', ['gteq' => $range['from']])
            ->addFieldToFilter('period', ['lteq' => $range['to']])
            ->addOrder('rating_pos', Collection::SORT_ORDER_DESC);

        $result = $this->processProductCollection($searchCriteria);

        return $result;
    }

    /**
     * @param \OxCom\MagentoTopProducts\Api\ProductSearchCriteriaInterface $searchCriteria
     *
     * @return \OxCom\MagentoTopProducts\Model\ProductSearchResults
     */
    public function getRatedProducts(ProductSearchCriteriaInterface $searchCriteria)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $this->prepareProductCollection($searchCriteria);

        $joinCond = [
            'store_id' => ['eq' => $storeId],
        ];

        $code   = $searchCriteria->getRatingCode();
        $rating = $this->rating->getItemByColumnValue('rating_code', $code);

        if (empty($rating) || $rating->isEmpty()) {
            throw new \Magento\Framework\Exception\InputException(__('Rating code "%s" not found.', $code));
        }

        // there is something like we are searching
        $id = $rating->getData('rating_id');
        $joinCond['rating_id'] = ['eq' => $id];

        // $ra - alias for rating table
        $ra = 'r';
        /**
         * To create more reliable order based on number of votes and quality of votes we have to introduce product
         * weight, because product with 1 vote and 5 stars cannot be at first position. Please see link to get more
         * information about this.
         *
         * At current realisation
         * q        - 'vote_count' column
         * P = 0.5
         * Q = 5    - becase we have 5 star system
         * p        - 'vote_value_sum' / 'vote_count'
         *
         * @link https://math.stackexchange.com/questions/942738/algorithm-to-calculate-rating-based-on-multiple-reviews-using-both-review-score
         */
        $q = "`{$ra}`.vote_count";
        $P = 0.5;
        $Q = 5;
        $p = "(`{$ra}`.vote_value_sum / {$q})";
        $weight = "ROUND(({$P} * {$p} + 10 * (1 - {$P}) * (1 -  EXP( -({$q}) / {$Q} ))), 2)";
        $this->productCollection
            ->getSelect()
            ->columns([
                'score' => new \Zend_Db_Expr($weight)
            ]);

        $this->productCollection->joinTable(
            [$ra => $this->ratingAggregated->getMainTable()],
            'entity_pk_value = entity_id',
            [
                'rating_id'      => 'rating_id',
                'vote_count'     => 'vote_count',
                'vote_value_sum' => 'vote_value_sum',
            ],
            $joinCond
        );

        $this->productCollection
            ->getSelect()
            ->order('score ' . Collection::SORT_ORDER_DESC);

        $result = $this->processProductCollection($searchCriteria);

        return $result;
    }

    /**
     * @param ProductSearchCriteriaInterface $searchCriteria
     */
    protected function prepareProductCollection(ProductSearchCriteriaInterface $searchCriteria)
    {
        $pageSize = (int)$searchCriteria->getPageSize();
        $page     = (int)$searchCriteria->getCurrentPage();

        $this->productCollection
            ->clear()
            ->setPageSize($pageSize)
            ->setCurPage($page);

        // add attributes to filtering
        $criteria    = $this->searchCriteriaBuilder->create();
        $attributes  = $this->metadataService->getList($criteria)->getItems();
        $allowedAttr = [];

        foreach ($attributes as $attribute) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $code               = $attribute->getAttributeCode();
            $allowedAttr[$code] = $attribute;
        }

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if (\in_array($filter->getField(), \array_keys($allowedAttr), true)) {
                    // add this attribute to join list and filter
                    $attribute = $allowedAttr[$filter->getField()];
                    $this->productCollection->joinAttribute($filter->getField(), $attribute, 'entity_id', null, 'inner');

                    $this->productCollection->addAttributeToFilter(
                        $filter->getField(),
                        [$filter->getConditionType() => $filter->getValue()]
                    );
                }
            }
        }
    }

    /**
     * Prepare response
     *
     * @param \OxCom\MagentoTopProducts\Api\ProductSearchCriteriaInterface $searchCriteria
     *
     * @return \OxCom\MagentoTopProducts\Model\ProductSearchResults
     */
    protected function processProductCollection(ProductSearchCriteriaInterface $searchCriteria)
    {
        $items = $this->productCollection->walk(function ($item) {
            /** @var \Magento\Catalog\Model\Product $item */
            $productId = $item->getId();
            $product   = $this->products->getById($productId);

            return $product;
        });

        $size = $this->productCollection->getSize();

        $result = new ProductSearchResults();
        $result->setItems($items)
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($size);

        return $result;
    }
}
