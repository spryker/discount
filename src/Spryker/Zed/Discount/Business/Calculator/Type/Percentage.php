<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Calculator\Type;

use Generated\Shared\Transfer\DiscountableItemTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Spryker\Zed\Discount\Business\Exception\CalculatorException;

class Percentage implements CalculatorInterface
{

    /**
     * @deprecated use calculateDiscount instead
     *
     * @param array $discountableItems
     * @param int $percentage
     *
     * @return int
     */
    public function calculate(array $discountableItems, $percentage)
    {
        $discountTransfer = new DiscountTransfer();
        $discountTransfer->setAmount($percentage);

        return $this->calculateDiscount($discountableItems, $discountTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\DiscountableItemTransfer[] $discountableItems
     * @param \Generated\Shared\Transfer\DiscountTransfer $discountTransfer
     *
     * @return int
     */
    public function calculateDiscount(array $discountableItems, DiscountTransfer $discountTransfer)
    {
        $value = $discountTransfer->requireAmount()->getAmount();

        $this->ensureIsValidNumber($value);

        $discountAmount = 0;

        $value = $value / 100;

        if ($value > 100) {
            $value = 100;
        }

        if ($value <= 0) {
            return 0;
        }

        foreach ($discountableItems as $discountableItemTransfer) {
            $itemTotalAmount = $discountableItemTransfer->getUnitGrossPrice() * $this->getDiscountableObjectQuantity($discountableItemTransfer);
            $discountAmount += $this->calculateDiscountAmount($itemTotalAmount, $value);
        }

        if ($discountAmount <= 0) {
            return 0;
        }

        return (int)round($discountAmount);
    }

    /**
     * @param int $grossPrice
     * @param int $number
     *
     * @return float
     */
    protected function calculateDiscountAmount($grossPrice, $number)
    {
        return round(($grossPrice * $number / 100), 2);
    }

    /**
     * @param float $number
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\CalculatorException
     *
     * @return void
     */
    protected function ensureIsValidNumber($number)
    {
        if (!is_float($number) && !is_int($number)) {
            throw new CalculatorException('Wrong value number, only float or integer is allowed.');
        }
    }

    /**
     * @param \Generated\Shared\Transfer\DiscountableItemTransfer $discountableItemTransfer
     *
     * @return int
     */
    protected function getDiscountableObjectQuantity(DiscountableItemTransfer $discountableItemTransfer)
    {
        $quantity = $discountableItemTransfer->getQuantity();

        if (empty($quantity)) {
            return 1;
        }

        return $quantity;
    }

}
