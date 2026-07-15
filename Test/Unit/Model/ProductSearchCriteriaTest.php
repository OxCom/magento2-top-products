<?php

namespace OxCom\MagentoTopProducts\Test\Unit\Model;

use Magento\Framework\Exception\InputException;
use OxCom\MagentoTopProducts\Api\ProductSearchCriteriaInterface;
use OxCom\MagentoTopProducts\Model\ProductSearchCriteria;
use PHPUnit\Framework\TestCase;

class ProductSearchCriteriaTest extends TestCase
{
    /**
     * @var \OxCom\MagentoTopProducts\Model\ProductSearchCriteria
     */
    protected $criteria;

    protected function setUp(): void
    {
        $this->criteria = new ProductSearchCriteria();
    }

    public function testSetPeriodAcceptsAllowedValues(): void
    {
        $allowed = [
            ProductSearchCriteriaInterface::PERIOD_DAILY,
            ProductSearchCriteriaInterface::PERIOD_MONTHLY,
            ProductSearchCriteriaInterface::PERIOD_YEARLY,
        ];

        foreach ($allowed as $period) {
            $result = $this->criteria->setPeriod($period);

            $this->assertSame($this->criteria, $result);
            $this->assertSame($period, $this->criteria->getPeriod());
        }
    }

    public function testSetPeriodFallsBackToDailyWhenEmpty(): void
    {
        $this->criteria->setPeriod(null);

        $this->assertSame(ProductSearchCriteriaInterface::PERIOD_DAILY, $this->criteria->getPeriod());
    }

    public function testSetPeriodRejectsUnknownValue(): void
    {
        $this->expectException(InputException::class);

        $this->criteria->setPeriod('weekly');
    }

    public function testRatingCodeAccessors(): void
    {
        $this->assertNull($this->criteria->getRatingCode());

        $result = $this->criteria->setRatingCode('Quality');

        $this->assertSame($this->criteria, $result);
        $this->assertSame('Quality', $this->criteria->getRatingCode());
    }
}
