<?php

namespace OxCom\MagentoTopProducts\Model;

use Magento\Framework\Api\SearchCriteria;
use OxCom\MagentoTopProducts\Api\ProductSearchCriteriaInterface;

/**
 * Class ProductSearchCriteria
 *
 * @package OxCom\MagentoTopProducts\Model
 */
class ProductSearchCriteria extends SearchCriteria implements ProductSearchCriteriaInterface
{
    /**
     * Type of period
     *
     * @var string
     */
    protected $period;

    /**
     * Raging code filter
     *
     * @var string
     */
    protected $ratingCode;

    /**
     * @param string $period Type of period
     *
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    public function setPeriod($period = null)
    {
        $allowed = [static::PERIOD_DAILY, static::PERIOD_MONTHLY, static::PERIOD_YEARLY];

        if (empty($period)) {
            $period = static::PERIOD_DAILY;
        } else {
            if (!in_array($period, $allowed, true)) {
                $allowed = implode(', ', $allowed);
                $msg     = 'Requested period "%s" doesn\'t exist. Allowed: %s. Default: %s';
                $phrase  = __($msg, $period, $allowed, static::PERIOD_DAILY);

                throw new \Magento\Framework\Exception\InputException($phrase);
            }
        }

        $this->period = $period;

        return $this;
    }

    /**
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param string $code Raging code filter
     *
     * @return $this
     */
    public function setRatingCode($code = null)
    {
        $this->ratingCode = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getRatingCode()
    {
        return $this->ratingCode;
    }
}
