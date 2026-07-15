<?php

namespace OxCom\MagentoTopProducts\Test\Unit\Model;

use OxCom\MagentoTopProducts\Model\ProductSearchCriteria;
use OxCom\MagentoTopProducts\Model\ProductSearchResults;
use PHPUnit\Framework\TestCase;

class ProductSearchResultsTest extends TestCase
{
    /**
     * @var \OxCom\MagentoTopProducts\Model\ProductSearchResults
     */
    protected $results;

    protected function setUp(): void
    {
        $this->results = new ProductSearchResults();
    }

    public function testGetItemsReturnsEmptyArrayByDefault(): void
    {
        $this->assertSame([], $this->results->getItems());
    }

    public function testItemsAccessors(): void
    {
        $items  = ['first', 'second'];
        $result = $this->results->setItems($items);

        $this->assertSame($this->results, $result);
        $this->assertSame($items, $this->results->getItems());
    }

    public function testSearchCriteriaAccessors(): void
    {
        $criteria = new ProductSearchCriteria();
        $result   = $this->results->setSearchCriteria($criteria);

        $this->assertSame($this->results, $result);
        $this->assertSame($criteria, $this->results->getSearchCriteria());
    }

    public function testTotalCountAccessors(): void
    {
        $result = $this->results->setTotalCount(42);

        $this->assertSame($this->results, $result);
        $this->assertSame(42, $this->results->getTotalCount());
    }
}
