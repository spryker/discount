<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Discount\Business\Calculator\Type;

use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use PHPUnit_Framework_TestCase;
use Spryker\Zed\Discount\Business\Calculator\Type\Fixed;

/**
 * Class FixedTest
 *
 * @group Unit
 * @group Spryker
 * @group Zed
 * @group Discount
 * @group Business
 * @group Calculator
 * @group Type
 * @group FixedTest
 */
class FixedTest extends PHPUnit_Framework_TestCase
{

    const ITEM_GROSS_PRICE_1000 = 1000;
    const DISCOUNT_AMOUNT_FIXED_100 = 100;
    const DISCOUNT_AMOUNT_FIXED_MINUS_100 = -100;

    /**
     * @return void
     */
    public function testCalculateFixedShouldReturnTheGivenAmount()
    {
        $items = $this->getItems(
            [
                self::ITEM_GROSS_PRICE_1000,
                self::ITEM_GROSS_PRICE_1000,
                self::ITEM_GROSS_PRICE_1000,
            ]
        );

        $calculator = new Fixed();
        $discountTransfer = (new DiscountTransfer())->setAmount(self::DISCOUNT_AMOUNT_FIXED_100);
        $discountAmount = $calculator->calculateDiscount($items, $discountTransfer);

        $this->assertSame(self::DISCOUNT_AMOUNT_FIXED_100, $discountAmount);
    }

    /**
     * @return void
     */
    public function testCalculateFixedShouldReturnNullForGivenNegativeAmounts()
    {
        $items = $this->getItems(
            [
                self::ITEM_GROSS_PRICE_1000,
                self::ITEM_GROSS_PRICE_1000,
                self::ITEM_GROSS_PRICE_1000,
            ]
        );

        $calculator = new Fixed();
        $discountTransfer = (new DiscountTransfer())->setAmount(-1 * self::DISCOUNT_AMOUNT_FIXED_100);
        $discountAmount = $calculator->calculateDiscount($items, $discountTransfer);

        $this->assertSame(0, $discountAmount);
    }

    /**
     * @param array $grossPrices
     *
     * @return \Generated\Shared\Transfer\ItemTransfer[]
     */
    protected function getItems(array $grossPrices)
    {
        $items = [];

        foreach ($grossPrices as $grossPrice) {
            $item = new ItemTransfer();
            $item->setUnitGrossPrice($grossPrice);
            $items[] = $item;
        }

        return $items;
    }

}
