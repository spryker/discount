<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Discount\Business\Model;

use Generated\Shared\Transfer\DiscountTransfer;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainer;
use Orm\Zed\Discount\Persistence\SpyDiscount;

class VoucherPoolCategory
{

    /**
     * @var DiscountQueryContainer
     */
    protected $discountQueryContainer;

    /**
     * @param DiscountQueryContainer $discountQueryContainer
     */
    public function __construct(DiscountQueryContainer $discountQueryContainer)
    {
        $this->discountQueryContainer = $discountQueryContainer;
    }

    public function getAvailableVoucherPoolCategories()
    {
        $categories = $this->discountQueryContainer
            ->queryDiscountVoucherPoolCategory()
            ->orderByName()
            ->find();

        $availableVoucherPoolCategories = [];

        foreach ($categories as $category) {
            $availableVoucherPoolCategories[$category->getIdDiscountVoucherPoolCategory()] = $category->getName();
        }

        return $availableVoucherPoolCategories;
    }

    /**
     * @return SpyDiscount[]
     */
    public function retrieveActiveAndRunningDiscounts()
    {
        return $this->queryContainer->queryActiveAndRunningDiscounts()->find();
    }

    /**
     * @return array
     */
    protected function retrieveDiscountsToBeCalculated()
    {
        $discounts = $this->retrieveActiveAndRunningDiscounts();
        $discountsToBeCalculated = [];
        $errors = [];

        foreach ($discounts as $discount) {
            $discountTransfer = new DiscountTransfer();
            $discountTransfer->fromArray($discount->toArray(), true);
            $result = $this->decisionRule->evaluate(
                $discountTransfer,
                $this->discountContainer,
                $this->getDecisionRulePlugins($discount->getPrimaryKey())
            );

            if ($result->isSuccess()) {
                $discountsToBeCalculated[] = $discountTransfer;
            } else {
                $errors = array_merge($errors, $result->getErrors());
            }
        }

        return [
            self::KEY_DISCOUNTS => $discountsToBeCalculated,
            self::KEY_ERRORS => $errors,
        ];
    }

}
