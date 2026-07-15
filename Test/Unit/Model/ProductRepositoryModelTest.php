<?php

namespace OxCom\MagentoTopProducts\Test\Unit\Model;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection as Bestsellers;
use Magento\Store\Model\StoreManager;
use Magento\Review\Model\ResourceModel\Rating\Collection as Rating;
use OxCom\MagentoTopProducts\Model\ProductRepositoryModel;
use OxCom\MagentoTopProducts\Model\ResourceModel\Rating\Option\Aggregated\Collection as RatingAggregated;
use PHPUnit\Framework\TestCase;

class ProductRepositoryModelTest extends TestCase
{
    /**
     * @var \OxCom\MagentoTopProducts\Model\ProductRepositoryModel
     */
    protected $repository;

    protected function setUp(): void
    {
        $this->repository = new ProductRepositoryModel(
            $this->createStub(Bestsellers::class),
            $this->createStub(ProductRepository::class),
            $this->createStub(ProductCollection::class),
            $this->createStub(StoreManager::class),
            $this->createStub(Rating::class),
            $this->createStub(RatingAggregated::class),
            $this->createStub(ProductAttributeRepositoryInterface::class),
            $this->createStub(SearchCriteriaBuilder::class)
        );
    }

    public function testGetListRejectsUnknownType(): void
    {
        $this->expectException(InputException::class);

        $this->repository->getList('unknown');
    }

    public function testGetListRejectsEmptyType(): void
    {
        $this->expectException(InputException::class);

        $this->repository->getList('');
    }
}
